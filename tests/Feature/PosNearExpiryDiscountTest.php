<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\User;

it('does not add VAT to POS payment details or the cart summary', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);

    $response = $this->actingAs($cashier)->get(route('pos.index'));

    $response->assertOk()
        ->assertDontSee('VAT (12%)')
        ->assertSee('Amount Due')
        ->assertDontSee('vat.toFixed(2)', false);

    expect(substr_count($response->getContent(), 'VAT'))->toBe(0)
        ->and(substr_count($response->getContent(), 'x-text="total.toFixed(2)"'))->toBe(3);
});

it('rejects insufficient payment without creating a sale or reducing stock', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    $product = Product::create([
        'name' => 'Insufficient Payment Shampoo',
        'sku' => 'INSUFFICIENT-PAYMENT-001',
        'cost' => 40,
        'price' => 100,
        'stock' => 0,
        'available_stock' => 2,
        'low_stock_level' => 1,
        'active' => true,
    ]);

    $this->actingAs($cashier)
        ->from(route('pos.index'))
        ->post(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 1,
            'items' => [[
                'item_type' => 'product',
                'product_id' => $product->id,
                'quantity' => 1,
            ]],
        ])
        ->assertRedirect(route('pos.index'))
        ->assertSessionHasErrors(['amount_paid']);

    expect(Sale::query()->count())->toBe(0)
        ->and($product->fresh()->available_stock)->toBe(2);
});

it('rejects unsupported payment methods before creating a sale', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    $product = Product::create([
        'name' => 'Invalid Method Shampoo',
        'sku' => 'INVALID-METHOD-001',
        'cost' => 40,
        'price' => 100,
        'stock' => 0,
        'available_stock' => 2,
        'low_stock_level' => 1,
        'active' => true,
    ]);

    $this->actingAs($cashier)
        ->from(route('pos.index'))
        ->post(route('pos.checkout'), [
            'payment_method' => 'cryptocurrency',
            'amount_paid' => 100,
            'items' => [[
                'item_type' => 'product',
                'product_id' => $product->id,
                'quantity' => 1,
            ]],
        ])
        ->assertRedirect(route('pos.index'))
        ->assertSessionHasErrors(['payment_method']);

    expect(Sale::query()->count())->toBe(0)
        ->and($product->fresh()->available_stock)->toBe(2);
});

it('shows the configured near-expiry markdown on POS product cards', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    Setting::setValue('expiry_warning_days', 30);
    Setting::setValue('expiry_discount_percent', 15);
    Setting::setValue('expiry_discount_enabled', 1);

    Product::create([
        'name' => 'Soon-to-Expire Shampoo',
        'sku' => 'SOON-EXPIRE-001',
        'cost' => 50,
        'price' => 100,
        'stock' => 0,
        'available_stock' => 8,
        'low_stock_level' => 2,
        'expiry_date' => today()->addDays(5),
        'active' => true,
    ]);

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertSee('15% discount')
        ->assertDontSee('Near-expiry')
        ->assertSee('₱85.00')
        ->assertSee('₱100.00');
});

it('sells unexpired batches first and applies near-expiry savings only to matching units', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    Setting::setValue('expiry_warning_days', 30);
    Setting::setValue('expiry_discount_percent', 10);
    Setting::setValue('expiry_discount_enabled', 1);

    $product = Product::create([
        'name' => 'Mixed Batch Shampoo',
        'sku' => 'MIXED-BATCH-SHAMPOO',
        'cost' => 50,
        'price' => 100,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 2,
        'active' => true,
    ]);
    $expiredBatch = $product->stockBatches()->create([
        'batch_reference' => 'EXPIRED-LOT',
        'expiry_date' => today()->subDay(),
        'inventory_quantity' => 0,
        'available_quantity' => 3,
    ]);
    $nearExpiryBatch = $product->stockBatches()->create([
        'batch_reference' => 'NEAR-LOT',
        'expiry_date' => today()->addDays(5),
        'inventory_quantity' => 0,
        'available_quantity' => 2,
    ]);
    $regularBatch = $product->stockBatches()->create([
        'batch_reference' => 'REGULAR-LOT',
        'expiry_date' => today()->addDays(90),
        'inventory_quantity' => 0,
        'available_quantity' => 4,
    ]);
    $product->syncStockTotals();

    $checkoutResponse = $this->actingAs($cashier)->post(route('pos.checkout'), [
        'payment_method' => 'cash',
        'amount_paid' => 280,
        'items' => [[
            'item_type' => 'product',
            'product_id' => $product->id,
            'quantity' => 3,
        ]],
    ]);

    $sale = Sale::latest('id')->firstOrFail();
    $saleItem = SaleItem::where('sale_id', $sale->id)->firstOrFail();
    $checkoutResponse->assertRedirect(route('receipts.done', $sale));

    expect($saleItem->line_total)->toBe('280.00')
        ->and($saleItem->discount)->toBe('20.00')
        ->and($expiredBatch->fresh()->available_quantity)->toBe(3)
        ->and($nearExpiryBatch->fresh()->available_quantity)->toBe(0)
        ->and($regularBatch->fresh()->available_quantity)->toBe(3)
        ->and($product->fresh()->available_stock)->toBe(6);

    $this->actingAs($cashier)
        ->get(route('pos.receipt', $sale))
        ->assertOk()
        ->assertSee('href="' . route('pos.index') . '"', false)
        ->assertSee('Back to POS')
        ->assertSee(route('receipts.print', $sale), false);

    $this->actingAs($cashier)
        ->get(route('receipts.done', $sale))
        ->assertOk()
        ->assertSee('View Receipt')
        ->assertDontSee('Print Receipt');
});
