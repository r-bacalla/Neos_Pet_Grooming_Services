<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index()
    {
        $stockProducts = Product::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with([
                'stockBatches',
                'stockMovements' => function ($query) {
                    $query->whereNull('archived_at')
                        ->latest()
                        ->limit(1);
                },
            ])
            ->orderBy('name')
            ->paginate(15);
        $categories = Category::query()->orderBy('name')->pluck('name');

        return view(
            'inventory.index',
            compact('products', 'stockProducts', 'categories')
        );
    }

    public function stockIn(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'arrival_at' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($product->isExpired() && empty($validated['expiry_date'])) {
            return back()->with('error', 'Enter a future expiry date before restocking this expired product.');
        }

        $receivedAt = \Illuminate\Support\Carbon::parse($validated['arrival_at']);

        DB::transaction(function () use ($request, $product, $validated, $receivedAt) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $before = (int) $product->stock;
            $availableBefore = (int) $product->available_stock;
            $quantity = (int) $validated['quantity'];
            $batchReference = 'RECEIVED-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(6));

            $product->update([
                'last_stock_in_quantity' => $quantity,
                'last_stock_in_at' => $receivedAt,
            ]);

            $batch = $product->stockBatches()->create([
                'batch_reference' => $batchReference,
                'supplier' => $validated['supplier'] ?? null,
                'received_at' => $receivedAt,
                'expiry_date' => $validated['expiry_date'] ?? $product->expiry_date,
                'inventory_quantity' => $quantity,
                'available_quantity' => 0,
            ]);
            $product->syncStockTotals();
            $product->refresh();
            $after = (int) $product->stock;

            StockMovement::create([
                'product_id' => $product->id,
                'product_stock_batch_id' => $batch->id,
                'user_id' => $request->user()->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'batch_reference' => $batchReference,
                'stock_before' => $before,
                'stock_after' => $after,
                'available_before' => $availableBefore,
                'available_after' => $availableBefore,
                'reason' => $validated['reason'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'inventory_stock_in',
                'module' => 'Inventory',
                'record_type' => 'Product',
                'record_id' => $product->id,
                'description' => 'Stock-in recorded for ' . $product->name,
                'old_values' => ['stock' => $before],
                'new_values' => [
                    'stock' => $after,
                    'quantity' => $quantity,
                    'stock_in_reference' => $batchReference,
                    'arrival_at' => $receivedAt->toDateTimeString(),
                    'supplier' => $validated['supplier'] ?? null,
                    'delivery_notes' => $validated['reason'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Stock-in recorded successfully.');
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:1000'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.sku' => ['required', 'string', 'max:100', 'distinct:ignore_case'],
            'rows.*.category' => ['nullable', 'string', 'max:100'],
            'rows.*.cost' => ['required', 'numeric', 'min:0'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.stock' => ['required', 'integer', 'min:0'],
            'rows.*.low_stock_level' => ['nullable', 'integer', 'min:0'],
            'rows.*.expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $batchReference = 'IMPORT-' . now()->format('Ymd-His');
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($request, $validated, $batchReference, &$created, &$updated) {
            foreach ($validated['rows'] as $row) {
                $product = Product::query()
                    ->where('sku', trim($row['sku']))
                    ->lockForUpdate()
                    ->first();
                $isNew = $product === null;
                $before = (int) ($product?->stock ?? 0);
                $availableBefore = (int) ($product?->available_stock ?? 0);
                $oldValues = $product?->only(['name', 'sku', 'category', 'cost', 'price', 'stock', 'available_stock', 'low_stock_level', 'expiry_date']);
                $importedStock = (int) $row['stock'];

                $attributes = [
                    'name' => trim($row['name']),
                    'category' => $row['category'] ?? null,
                    'cost' => $row['cost'],
                    'price' => $row['price'],
                    'low_stock_level' => $row['low_stock_level'] ?? 5,
                    'active' => true,
                ];

                if (filled($attributes['category'])) {
                    Category::firstOrCreate(['name' => trim($attributes['category'])]);
                }

                if ($isNew) {
                    $product = Product::create([
                        ...$attributes,
                        'sku' => trim($row['sku']),
                        'stock' => 0,
                        'available_stock' => 0,
                        'last_stock_in_quantity' => null,
                        'last_stock_in_at' => null,
                    ]);
                    $created++;
                } else {
                    $product->update([
                        ...$attributes,
                        ...($importedStock > $before ? [
                            'last_stock_in_quantity' => $importedStock - $before,
                            'last_stock_in_at' => now(),
                        ] : []),
                    ]);
                    $updated++;
                }

                $product->stockBatches()
                    ->where('inventory_quantity', '>', 0)
                    ->update(['inventory_quantity' => 0]);

                if ($importedStock > 0) {
                    $importedBatch = $product->stockBatches()->create([
                        'batch_reference' => $batchReference,
                        'expiry_date' => $row['expiry_date'] ?? null,
                        'inventory_quantity' => $importedStock,
                        'available_quantity' => 0,
                    ]);

                    $product->update([
                        'last_stock_in_quantity' => $importedStock,
                        'last_stock_in_at' => now(),
                    ]);
                }

                $product->syncStockTotals();
                $product->refresh();

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => $isNew ? 'product_created' : 'product_updated',
                    'module' => 'Products',
                    'record_type' => 'Product',
                    'record_id' => $product->id,
                    'description' => ($isNew ? 'Product imported: ' : 'Product updated by import: ') . $product->name,
                    'old_values' => $oldValues,
                    'new_values' => $product->only(['name', 'sku', 'category', 'cost', 'price', 'stock', 'available_stock', 'low_stock_level', 'expiry_date']),
                    'ip_address' => $request->ip(),
                ]);

                $difference = $importedStock - $before;
                if ($difference !== 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'product_stock_batch_id' => $difference > 0 ? ($importedBatch->id ?? null) : null,
                        'user_id' => $request->user()->id,
                        'type' => 'excel_import',
                        'quantity' => $difference,
                        'batch_reference' => $batchReference,
                        'stock_before' => $before,
                        'stock_after' => $importedStock,
                        'available_before' => $availableBefore,
                        'available_after' => $availableBefore,
                        'reason' => 'Inventory quantity imported from spreadsheet',
                    ]);

                    AuditLog::create([
                        'user_id' => $request->user()->id,
                        'action' => $difference > 0 ? 'inventory_stock_in' : 'inventory_stock_out',
                        'module' => 'Inventory',
                        'record_type' => 'Product',
                        'record_id' => $product->id,
                        'description' => 'Inventory quantity ' . ($difference > 0 ? 'increased' : 'decreased') . ' by spreadsheet import for ' . $product->name,
                        'old_values' => ['stock' => $before, 'available_stock' => $availableBefore],
                        'new_values' => [
                            'stock' => $product->stock,
                            'available_stock' => $product->available_stock,
                            'quantity_change' => $difference,
                            'batch_reference' => $batchReference,
                        ],
                        'ip_address' => $request->ip(),
                    ]);
                }
            }
        });

        return response()->json([
            'message' => "Import complete: {$created} products added, {$updated} products updated.",
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    public function allocateToProducts(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $result = DB::transaction(function () use ($request, $product, $validated) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $quantity = (int) $validated['quantity'];
            $warehouseBefore = (int) $product->stock;
            $availableBefore = (int) $product->available_stock;
            $batches = $product->stockBatches()
                ->where('inventory_quantity', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                })
                ->orderByRaw('expiry_date IS NULL')
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->get();
            $allocatable = (int) $batches->sum('inventory_quantity');

            if ($quantity === 0) {
                return ['status' => 'zero', 'available' => $allocatable];
            }

            if ($allocatable < $quantity) {
                return ['status' => 'overstock', 'available' => $allocatable];
            }

            $remaining = $quantity;
            $movementStock = $warehouseBefore;
            $movementAvailable = $availableBefore;
            foreach ($batches as $batch) {
                if ($remaining === 0) {
                    break;
                }

                $moved = min((int) $batch->inventory_quantity, $remaining);
                $batch->update([
                    'inventory_quantity' => $batch->inventory_quantity - $moved,
                    'available_quantity' => $batch->available_quantity + $moved,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'product_stock_batch_id' => $batch->id,
                    'user_id' => $request->user()->id,
                    'type' => 'allocation',
                    'quantity' => -$moved,
                    'batch_reference' => $batch->batch_reference,
                    'stock_before' => $movementStock,
                    'stock_after' => $movementStock - $moved,
                    'available_before' => $movementAvailable,
                    'available_after' => $movementAvailable + $moved,
                    'reason' => "Transferred {$moved} units from Inventory to Products from batch {$batch->batch_reference}.",
                ]);

                $movementStock -= $moved;
                $movementAvailable += $moved;
                $remaining -= $moved;
            }

            $product->syncStockTotals();
            $product->refresh();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'inventory_stock_allocated',
                'module' => 'Inventory',
                'record_type' => 'Product',
                'record_id' => $product->id,
                'description' => "Allocated {$quantity} units of {$product->name} to Products.",
                'old_values' => [
                    'inventory_stock' => $warehouseBefore,
                    'available_stock' => $availableBefore,
                ],
                'new_values' => [
                    'inventory_stock' => $product->stock,
                    'available_stock' => $product->available_stock,
                ],
                'ip_address' => $request->ip(),
            ]);

            return ['status' => 'transferred', 'available' => $warehouseBefore - $quantity];
        });

        if ($result['status'] === 'overstock') {
            return redirect()->route('products.index')->with(
                'error',
                "The quantity entered is over the stock in Inventory. Only {$result['available']} units are available."
            );
        }

        if ($result['status'] === 'zero') {
            return redirect()->route('products.index')->with('success', 'No stock was transferred.');
        }

        return redirect()->route('products.index')->with('success', 'Stock transferred to Products.');
    }

    public function history(Product $product)
    {
        $movements = $product->stockMovements()
            ->with(['user', 'batch'])
            ->latest()
            ->paginate(15);

        return view('inventory.history', compact('product', 'movements'));
    }

    public function archiveMovement(StockMovement $movement)
    {
        $movement->update(['archived_at' => now()]);

        return back()->with('success', 'Inventory movement archived.');
    }
}