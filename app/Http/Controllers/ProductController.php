<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\Category;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(
            $request->get('search', '')
        );
        $category = $request->filled('category')
            ? (string) $request->query('category')
            : '';

        $products = Product::query()
            ->with('stockBatches')
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'sku',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'category',
                        'like',
                        "%{$search}%"
                    );
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $categories = Category::query()->orderBy('name')->pluck('name');
        $inventoryProducts = Product::query()
            ->with('stockBatches')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                $product->allocatable_inventory = (int) $product->stockBatches
                    ->filter(fn ($batch) =>
                        $batch->inventory_quantity > 0 &&
                        (!$batch->expiry_date || !$batch->expiry_date->lt(today()))
                    )
                    ->sum('inventory_quantity');

                return $product;
            })
            ->filter(fn (Product $product) => $product->allocatable_inventory > 0)
            ->values();
        $inventoryProductOptions = $inventoryProducts->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'stock' => $product->allocatable_inventory,
        ]);

        return view(
            'products.index',
            compact('products', 'categories', 'inventoryProducts', 'inventoryProductOptions')
        );
    }

    public function expired()
    {
        $expiredQuery = ProductStockBatch::query()
            ->with('product')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', today());

        $totals = (clone $expiredQuery)
            ->selectRaw('COUNT(DISTINCT product_id) as product_count, COALESCE(SUM(inventory_quantity), 0) as inventory_units, COALESCE(SUM(available_quantity), 0) as product_units')
            ->first();

        $batches = $expiredQuery
            ->orderBy('expiry_date')
            ->orderBy('product_id')
            ->paginate(15);

        return view('products.expired', compact('batches', 'totals'));
    }

    public function writeOffExpired()
    {
        Artisan::call('inventory:write-off-expired');
        $result = trim(Artisan::output());

        return redirect()
            ->route('products.expired')
            ->with('success', $result ?: 'Expired stock check completed.');
    }

    public function removeExpiredBatch(ProductStockBatch $batch)
    {
        abort_unless($batch->expiry_date && $batch->expiry_date->lt(today()), 404);

        if ($batch->inventory_quantity > 0 || $batch->available_quantity > 0) {
            return redirect()
                ->route('products.expired')
                ->with('error', 'Write off the remaining stock before removing this batch.');
        }

        $batch->delete();

        return redirect()
            ->route('products.expired')
            ->with('success', 'Expired batch moved to Archive.');
    }

    public function create()
    {
        $categories = Category::query()->orderBy('name')->pluck('name');

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'low_stock_level' => [
                'required',
                'integer',
                'min:0',
            ],

            'expiry_date' => [
                'nullable',
                'date',
            ],

            'expiry_discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);
        $validated['category'] = filled($validated['category'] ?? null)
            ? trim($validated['category'])
            : null;

        $product = DB::transaction(function () use ($validated) {
            if (filled($validated['category'] ?? null)) {
                Category::firstOrCreate(['name' => trim($validated['category'])]);
            }

            return Product::create([
                ...$validated,
                'stock' => 0,
            ]);
        });

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product_created',
            'module' => 'Products',
            'record_type' => 'Product',
            'record_id' => $product->id,
            'description' => 'Product created: ' . $product->name,
            'new_values' => $product->only(['name', 'sku', 'category', 'cost', 'price', 'low_stock_level', 'expiry_date', 'expiry_discount']),
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Product created successfully.'
            );
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->orderBy('name')->pluck('name');

        return view(
            'products.edit',
            compact('product', 'categories')
        );
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku,' .
                    $product->id,
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'low_stock_level' => [
                'required',
                'integer',
                'min:0',
            ],

            'expiry_date' => [
                'nullable',
                'date',
            ],

            'expiry_discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],
        ]);
        $validated['category'] = filled($validated['category'] ?? null)
            ? trim($validated['category'])
            : null;

        $oldValues = $product->only(['name', 'sku', 'category', 'cost', 'price', 'low_stock_level', 'expiry_date', 'expiry_discount', 'active']);

        DB::transaction(function () use ($validated, $product) {
            if (filled($validated['category'] ?? null)) {
                Category::firstOrCreate(['name' => trim($validated['category'])]);
            }

            $product->update($validated);
        });

        $product->refresh();
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product_updated',
            'module' => 'Products',
            'record_type' => 'Product',
            'record_id' => $product->id,
            'description' => 'Product updated: ' . $product->name,
            'old_values' => $oldValues,
            'new_values' => $product->only(array_keys($oldValues)),
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Product updated successfully.'
            );
    }

    public function destroy(Request $request, Product $product)
    {
        $oldValues = $product->only(['name', 'sku', 'category', 'cost', 'price', 'stock', 'available_stock', 'active']);
        $product->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'product_deleted',
            'module' => 'Products',
            'record_type' => 'Product',
            'record_id' => $product->id,
            'description' => 'Product moved to Archive: ' . $product->name,
            'old_values' => $oldValues,
            'new_values' => ['deleted' => true],
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product moved to Archive.');
    }
}