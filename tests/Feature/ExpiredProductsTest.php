<?php

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('writes off expired stock once and records a system movement', function () {
    $expiredProduct = Product::create([
        'name' => 'Expired Shampoo',
        'sku' => 'EXPIRED-SHAMPOO',
        'cost' => 40,
        'price' => 80,
        'stock' => 12,
        'available_stock' => 5,
        'low_stock_level' => 2,
        'expiry_date' => today()->subDay(),
        'active' => true,
    ]);
    $expiresToday = Product::create([
        'name' => 'Today Shampoo',
        'sku' => 'TODAY-SHAMPOO',
        'cost' => 30,
        'price' => 60,
        'stock' => 4,
        'available_stock' => 2,
        'low_stock_level' => 2,
        'expiry_date' => today(),
        'active' => true,
    ]);

    Artisan::call('inventory:write-off-expired');

    expect($expiredProduct->fresh()->stock)->toBe(0)
        ->and($expiredProduct->fresh()->available_stock)->toBe(0)
        ->and($expiresToday->fresh()->stock)->toBe(4)
        ->and(StockMovement::where('product_id', $expiredProduct->id)->count())->toBe(1)
        ->and(StockMovement::where('product_id', $expiredProduct->id)->value('product_stock_batch_id'))->toBe($expiredProduct->stockBatches()->value('id'))
        ->and(StockMovement::where('product_id', $expiredProduct->id)->first()->user_id)->toBeNull()
        ->and(AuditLog::where('record_id', $expiredProduct->id)
            ->where('action', 'inventory_expired_stock_write_off')
            ->exists())->toBeTrue();

    Artisan::call('inventory:write-off-expired');

    expect(StockMovement::where('product_id', $expiredProduct->id)->count())->toBe(1);
});

it('lists expired products and lets an inventory manager run the write-off', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Expired Cat Treats',
        'sku' => 'EXPIRED-CAT-TREATS',
        'cost' => 10,
        'price' => 20,
        'stock' => 7,
        'available_stock' => 3,
        'low_stock_level' => 1,
        'expiry_date' => today()->subDay(),
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->get(route('products.expired'))
        ->assertOk()
        ->assertSee('Expired Products')
        ->assertSee('Expired Cat Treats')
        ->assertSee('Write Off Expired Stock')
        ->assertSee('keeps the batch history', false)
        ->assertSee('Pending write-off');

    $this->actingAs($manager)
        ->post(route('inventory.allocate', $product), ['quantity' => 1])
        ->assertRedirect(route('products.index'));

    $this->actingAs($manager)
        ->post(route('inventory.stock-in', $product), [
            'quantity' => 2,
            'batch_reference' => 'EXPIRED-DELIVERY',
        ])
        ->assertRedirect();

    expect($product->fresh()->stock)->toBe(7)
        ->and($product->fresh()->available_stock)->toBe(3);

    $batch = $product->stockBatches()->firstOrFail();

    $this->actingAs($manager)
        ->delete(route('products.expired.remove', $batch))
        ->assertRedirect(route('products.expired'))
        ->assertSessionHas('error');

    expect($batch->fresh())->not->toBeNull();

    $this->actingAs($manager)
        ->post(route('products.expired.write-off'))
        ->assertRedirect(route('products.expired'));

    expect($product->fresh()->stock)->toBe(0)
        ->and($product->fresh()->available_stock)->toBe(0);

    $this->actingAs($manager)
        ->get(route('products.expired'))
        ->assertOk()
        ->assertSee('Expired Cat Treats')
        ->assertSee('Stock removed')
        ->assertSee('Delete');

    $this->actingAs($manager)
        ->delete(route('products.expired.remove', $batch))
        ->assertRedirect(route('products.expired'))
        ->assertSessionHas('success');

    expect($batch->fresh()->trashed())->toBeTrue()
        ->and(ProductStockBatch::onlyTrashed()->find($batch->id))->not->toBeNull()
        ->and($product->stockMovements()->first()->fresh()->product_stock_batch_id)->toBe($batch->id);

    $this->actingAs($manager)
        ->get(route('products.expired'))
        ->assertDontSee('Expired Cat Treats');
});

it('writes off an expired batch without removing newer stock of the same product', function () {
    $product = Product::create([
        'name' => 'Mixed Expiry Food',
        'sku' => 'MIXED-EXPIRY-001',
        'cost' => 20,
        'price' => 35,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 2,
        'active' => true,
    ]);
    $expiredBatch = $product->stockBatches()->create([
        'batch_reference' => 'OLD-LOT',
        'expiry_date' => today()->subDay(),
        'inventory_quantity' => 2,
        'available_quantity' => 3,
    ]);
    $validBatch = $product->stockBatches()->create([
        'batch_reference' => 'NEW-LOT',
        'expiry_date' => today()->addDays(60),
        'inventory_quantity' => 4,
        'available_quantity' => 5,
    ]);
    $product->syncStockTotals();

    expect($product->hasExpiredStockBatches())->toBeTrue()
        ->and($product->isExpired())->toBeFalse();

    Artisan::call('inventory:write-off-expired');

    expect($expiredBatch->fresh()->inventory_quantity)->toBe(0)
        ->and($expiredBatch->fresh()->available_quantity)->toBe(0)
        ->and($validBatch->fresh()->inventory_quantity)->toBe(4)
        ->and($validBatch->fresh()->available_quantity)->toBe(5)
        ->and($product->fresh()->stock)->toBe(4)
        ->and($product->fresh()->available_stock)->toBe(5)
        ->and($product->fresh()->expiry_date->toDateString())->toBe(today()->addDays(60)->toDateString());
});
