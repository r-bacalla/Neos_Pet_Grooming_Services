<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\GeneratedReport;
use App\Models\GroomingPrice;
use App\Models\Pet;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    private const MODULES = [
        'products' => ['label' => 'Products', 'description' => 'Archived product records'],
        'categories' => ['label' => 'Categories', 'description' => 'Archived product categories'],
        'expired-batches' => ['label' => 'Expired batches', 'description' => 'Archived expired stock batches'],
        'customers' => ['label' => 'Customers', 'description' => 'Archived customer profiles'],
        'pets' => ['label' => 'Pets', 'description' => 'Archived pet profiles'],
        'services' => ['label' => 'Services', 'description' => 'Archived grooming services'],
        'grooming-prices' => ['label' => 'Grooming prices', 'description' => 'Archived grooming prices'],
        'users' => ['label' => 'Accounts', 'description' => 'Archived user accounts'],
        'reports' => ['label' => 'Saved reports', 'description' => 'Archived reports and files'],
    ];

    public function index(Request $request)
    {
        $modules = $this->modulesForUser();
        $counts = [
            'products' => Product::onlyTrashed()->count(),
            'categories' => Category::onlyTrashed()->count(),
            'expired-batches' => ProductStockBatch::onlyTrashed()->count(),
            'customers' => Customer::onlyTrashed()->count(),
            'pets' => Pet::onlyTrashed()->count(),
            'services' => Service::onlyTrashed()->count(),
            'grooming-prices' => GroomingPrice::onlyTrashed()->count(),
            'users' => User::onlyTrashed()->count(),
            'reports' => GeneratedReport::onlyTrashed()->count(),
        ];

        $type = $request->query('type');
        abort_if($type !== null && !array_key_exists($type, $modules), 404);

        $records = null;
        if ($type !== null) {
            $query = match ($type) {
                'products' => Product::onlyTrashed()->orderByDesc('deleted_at'),
                'categories' => Category::onlyTrashed()->orderByDesc('deleted_at'),
                'expired-batches' => ProductStockBatch::onlyTrashed()
                    ->with(['product' => fn ($query) => $query->withTrashed()])
                    ->orderByDesc('deleted_at'),
                'customers' => Customer::onlyTrashed()->orderByDesc('deleted_at'),
                'pets' => Pet::onlyTrashed()
                    ->with(['customer' => fn ($query) => $query->withTrashed()])
                    ->orderByDesc('deleted_at'),
                'services' => Service::onlyTrashed()->orderByDesc('deleted_at'),
                'grooming-prices' => GroomingPrice::onlyTrashed()->orderByDesc('deleted_at'),
                'users' => User::onlyTrashed()->orderByDesc('deleted_at'),
                'reports' => GeneratedReport::onlyTrashed()->orderByDesc('deleted_at'),
            };

            $records = $query->paginate(15)->withQueryString();
        }

        return view('archive.index', [
            'modules' => $modules,
            'counts' => $counts,
            'type' => $type,
            'records' => $records,
        ]);
    }

    public function restore(string $type, int $id)
    {
        abort_unless($this->canManageType($type), 404);
        $record = $this->archivedRecord($type, $id);

        if ($type === 'pets' && $record->customer()->withTrashed()->first()?->trashed()) {
            return redirect()
                ->route('archive.index', ['type' => $type])
                ->with('error', 'Restore the customer before restoring this pet.');
        }

        if ($type === 'expired-batches' && $record->product()->withTrashed()->first()?->trashed()) {
            return redirect()
                ->route('archive.index', ['type' => $type])
                ->with('error', 'Restore the product before restoring this batch.');
        }

        DB::transaction(function () use ($record, $type) {
            if ($type === 'customers') {
                $deletedAt = $record->deleted_at;
                $record->restore();
                $record->pets()
                    ->onlyTrashed()
                    ->where('deleted_at', $deletedAt)
                    ->restore();

                return;
            }

            $product = $type === 'expired-batches'
                ? $record->product()->withTrashed()->first()
                : null;

            $record->restore();
            $product?->syncStockTotals();
        });

        return redirect()
            ->route('archive.index', ['type' => $type])
            ->with('success', self::MODULES[$type]['label'] . ' record restored.');
    }

    public function destroy(string $type, int $id)
    {
        abort_unless($this->canManageType($type), 404);
        $record = $this->archivedRecord($type, $id);

        if (($message = $this->purgeBlockReason($type, $record)) !== null) {
            return redirect()
                ->route('archive.index', ['type' => $type])
                ->with('error', $message);
        }

        $this->permanentlyDeleteRecord($type, $record);

        return redirect()
            ->route('archive.index', ['type' => $type])
            ->with('success', 'Record permanently deleted.');
    }

    public function destroyAll(string $type)
    {
        abort_unless($this->canManageType($type), 404);

        $deleted = 0;
        $retained = 0;
        $this->archivedQuery($type)->chunkById(100, function ($records) use ($type, &$deleted, &$retained) {
            foreach ($records as $record) {
                if ($this->purgeBlockReason($type, $record) !== null) {
                    $retained++;
                    continue;
                }

                $this->permanentlyDeleteRecord($type, $record);
                $deleted++;
            }
        });

        $message = "Permanently deleted {$deleted} archived record(s); {$retained} protected record(s) remain archived.";

        return redirect()
            ->route('archive.index', ['type' => $type])
            ->with($retained > 0 && $deleted === 0 ? 'error' : 'success', $message);
    }

    private function permanentlyDeleteRecord(string $type, object $record): void
    {
        DB::transaction(function () use ($record, $type) {
            if ($type === 'categories') {
                Product::withTrashed()
                    ->where('category', $record->name)
                    ->update(['category' => null]);
            }

            if ($type === 'reports') {
                Storage::disk('local')->delete($record->file_path);
            }

            $product = $type === 'expired-batches'
                ? $record->product()->withTrashed()->first()
                : null;

            $record->forceDelete();
            $product?->syncStockTotals();
        });
    }

    private function archivedRecord(string $type, int $id)
    {
        $model = match ($type) {
            'products' => Product::class,
            'categories' => Category::class,
            'expired-batches' => ProductStockBatch::class,
            'customers' => Customer::class,
            'pets' => Pet::class,
            'services' => Service::class,
            'grooming-prices' => GroomingPrice::class,
            'users' => User::class,
            'reports' => GeneratedReport::class,
        };

        return $model::onlyTrashed()->findOrFail($id);
    }

    private function archivedQuery(string $type)
    {
        $model = match ($type) {
            'products' => Product::class,
            'categories' => Category::class,
            'expired-batches' => ProductStockBatch::class,
            'customers' => Customer::class,
            'pets' => Pet::class,
            'services' => Service::class,
            'grooming-prices' => GroomingPrice::class,
            'users' => User::class,
            'reports' => GeneratedReport::class,
        };

        return $model::onlyTrashed();
    }

    private function canManageType(string $type): bool
    {
        return array_key_exists($type, $this->modulesForUser());
    }

    private function modulesForUser(): array
    {
        if (auth()->user()->isFrontDesk()) {
            return array_intersect_key(self::MODULES, [
                'products' => true,
                'categories' => true,
                'expired-batches' => true,
            ]);
        }

        return self::MODULES;
    }

    private function purgeBlockReason(string $type, object $record): ?string
    {
        if ($type === 'customers' && (
            $record->sales()->exists() ||
            $record->groomingAppointments()->exists() ||
            $record->groomingVisitGroups()->exists()
        )) {
            return 'This customer has sales or grooming history. Restore the customer instead of permanently deleting it.';
        }

        if ($type === 'pets' && $record->groomingAppointments()->exists()) {
            return 'This pet has grooming history. Restore the pet instead of permanently deleting it.';
        }

        if ($type === 'products' && (
            $record->stock > 0 ||
            $record->available_stock > 0 ||
            $record->saleItems()->exists() ||
            $record->stockMovements()->exists() ||
            $record->stockBatches()->withTrashed()->exists()
        )) {
            return 'This product has stock or inventory history. Restore it instead of permanently deleting it.';
        }

        if ($type === 'expired-batches' && (
            $record->inventory_quantity > 0 ||
            $record->available_quantity > 0
        )) {
            return 'This batch still has stock. Write it off before permanently deleting it.';
        }

        if ($type === 'categories' && Product::withTrashed()->where('category', $record->name)->exists()) {
            return 'Products still use this category. Reassign them before permanently deleting it.';
        }

        if ($type === 'services' && (
            $record->saleItems()->exists() ||
            $record->groomingAppointments()->exists()
        )) {
            return 'This service has sales or grooming history. Restore it instead of permanently deleting it.';
        }

        if ($type === 'users' && (
            $record->is(auth()->user()) ||
            $record->sales()->exists() ||
            $record->stockMovements()->exists()
        )) {
            return 'This account is signed in or has business history. Restore it instead of permanently deleting it.';
        }

        return null;
    }
}