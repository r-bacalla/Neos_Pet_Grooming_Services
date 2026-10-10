<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\StockMovement;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:write-off-expired', function () {
    $writtenOff = 0;

    ProductStockBatch::query()
        ->whereNotNull('expiry_date')
        ->whereDate('expiry_date', '<', today())
        ->where(function ($query) {
            $query->where('inventory_quantity', '>', 0)
                ->orWhere('available_quantity', '>', 0);
        })
        ->orderBy('id')
        ->chunkById(100, function ($batches) use (&$writtenOff) {
            foreach ($batches as $candidate) {
                DB::transaction(function () use ($candidate, &$writtenOff) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find($candidate->product_id);

                    if (!$product) {
                        return;
                    }

                    $batch = ProductStockBatch::query()
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->find($candidate->id);

                    if (!$batch || !$batch->expiry_date || !$batch->expiry_date->lt(today())) {
                        return;
                    }

                    $inventoryWrittenOff = (int) $batch->inventory_quantity;
                    $availableWrittenOff = (int) $batch->available_quantity;

                    if ($inventoryWrittenOff === 0 && $availableWrittenOff === 0) {
                        return;
                    }

                    $stockBefore = (int) $product->stock;
                    $availableBefore = (int) $product->available_stock;

                    $batch->update([
                        'inventory_quantity' => 0,
                        'available_quantity' => 0,
                    ]);

                    $product->syncStockTotals();
                    $product->refresh();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'product_stock_batch_id' => $batch->id,
                        'user_id' => null,
                        'type' => 'expiry_write_off',
                        'quantity' => -($inventoryWrittenOff + $availableWrittenOff),
                        'batch_reference' => $batch->batch_reference,
                        'stock_before' => $stockBefore,
                        'stock_after' => (int) $product->stock,
                        'available_before' => $availableBefore,
                        'available_after' => (int) $product->available_stock,
                        'reason' => 'Automatic expired batch write-off; expiry date ' . $batch->expiry_date->toDateString(),
                    ]);

                    AuditLog::create([
                        'user_id' => null,
                        'action' => 'inventory_expired_stock_write_off',
                        'module' => 'Inventory',
                        'record_type' => 'Product',
                        'record_id' => $product->id,
                        'description' => 'Automatically wrote off expired stock for ' . $product->name,
                        'old_values' => [
                            'stock' => $stockBefore,
                            'available_stock' => $availableBefore,
                        ],
                        'new_values' => [
                            'stock' => (int) $product->stock,
                            'available_stock' => (int) $product->available_stock,
                            'expiry_date' => $batch->expiry_date->toDateString(),
                            'batch_reference' => $batch->batch_reference,
                        ],
                    ]);

                    $writtenOff++;
                });
            }
        });

    $this->info("Expired batches written off: {$writtenOff}");
})->purpose('Automatically remove expired stock batches from Inventory and Products');

Schedule::command('inventory:write-off-expired')
    ->dailyAt('00:10')
    ->withoutOverlapping();

Schedule::command('backup:run')
    ->dailyAt('01:30')
    ->withoutOverlapping();
