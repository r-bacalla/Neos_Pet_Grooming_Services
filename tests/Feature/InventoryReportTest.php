<?php

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;

it('shows a View reason button and keeps the reason in a hidden dialog until opened', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Reason Test Shampoo',
        'sku' => 'REASON-TEST-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 1,
    ]);
    $reason = 'Supplier delivery adjustment requested by manager.';
    StockMovement::create([
        'product_id' => $product->id,
        'user_id' => $manager->id,
        'type' => 'adjustment',
        'quantity' => 2,
        'stock_before' => 0,
        'stock_after' => 2,
        'available_before' => 0,
        'available_after' => 0,
        'reason' => $reason,
    ]);

    $this->actingAs($manager)
        ->get(route('reports.inventory'))
        ->assertOk()
        ->assertSee('View')
        ->assertSee('data-reason="' . $reason . '"', false)
        ->assertSee('x-show="reasonDialogOpen"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('x-text="selectedReason"', false);
});

it('shows 10 stock movements per page with numbered next-page navigation', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Pagination Test Shampoo',
        'sku' => 'PAGINATION-TEST-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 1,
    ]);

    foreach (range(1, 16) as $number) {
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $manager->id,
            'type' => 'adjustment',
            'quantity' => 1,
            'stock_before' => 0,
            'stock_after' => 1,
            'available_before' => 0,
            'available_after' => 0,
            'reason' => 'Pagination movement ' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'created_at' => now()->addMinutes($number),
            'updated_at' => now()->addMinutes($number),
        ]);
    }

    $this->actingAs($manager)
        ->get(route('reports.inventory'))
        ->assertOk()
        ->assertSee('Pagination movement 16')
        ->assertDontSee('Pagination movement 01')
        ->assertSee('page=2', false)
        ->assertSee('Showing 10 movements');

    $this->get(route('reports.inventory', ['page' => 2]))
        ->assertOk()
        ->assertSee('Pagination movement 06')
        ->assertSee('Pagination movement 01')
        ->assertDontSee('Pagination movement 07')
        ->assertSee('Showing 6 movements');
});

it('labels movement archiving as Delete while retaining the history record', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Archived Movement Product',
        'sku' => 'ARCHIVED-MOVEMENT-PRODUCT',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
    ]);
    $movement = StockMovement::create([
        'product_id' => $product->id,
        'user_id' => $manager->id,
        'type' => 'adjustment',
        'quantity' => 1,
        'stock_before' => 0,
        'stock_after' => 1,
        'available_before' => 0,
        'available_after' => 0,
    ]);

    $this->actingAs($manager)
        ->get(route('inventory.history', $product))
        ->assertOk()
        ->assertSee('>Delete</button>', false)
        ->assertSee('archived, not permanently deleted', false);

    $this->post(route('inventory.movements.archive', $movement))
        ->assertRedirect();

    expect($movement->fresh()->archived_at)->not->toBeNull();
});
