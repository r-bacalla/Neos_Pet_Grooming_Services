<?php

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\User;

it('records product changes and stock movements with the responsible user', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)
        ->post(route('products.store'), [
            'name' => 'Audit Shampoo',
            'sku' => 'AUDIT-SHAMPOO-001',
            'category' => 'Care',
            'cost' => 20,
            'price' => 50,
            'low_stock_level' => 2,
        ])
        ->assertRedirect(route('inventory.index'));

    $product = Product::where('sku', 'AUDIT-SHAMPOO-001')->firstOrFail();
    expect(AuditLog::where('action', 'product_created')->where('record_id', $product->id)->value('user_id'))
        ->toBe($manager->id);

    $this->actingAs($manager)
        ->put(route('products.update', $product), [
            'name' => 'Audit Shampoo Plus',
            'sku' => 'AUDIT-SHAMPOO-001',
            'category' => 'Care',
            'cost' => 22,
            'price' => 60,
            'low_stock_level' => 2,
        ])
        ->assertRedirect(route('inventory.index'));

    $updatedLog = AuditLog::where('action', 'product_updated')->latest('id')->firstOrFail();
    expect($updatedLog->user_id)->toBe($manager->id)
        ->and((float) $updatedLog->old_values['price'])->toBe(50.0)
        ->and((float) $updatedLog->new_values['price'])->toBe(60.0);

    $this->actingAs($manager)
        ->post(route('inventory.stock-in', $product), [
            'quantity' => 2,
            'batch_reference' => 'AUDIT-DELIVERY-001',
        ])
        ->assertRedirect();

    expect(AuditLog::where('action', 'inventory_stock_in')->where('record_id', $product->id)->exists())->toBeTrue();

    $this->actingAs($manager)
        ->post(route('inventory.allocate', $product), ['quantity' => 2])
        ->assertRedirect(route('products.index'));

    $this->actingAs($manager)->post(route('pos.checkout'), [
        'payment_method' => 'cash',
        'amount_paid' => 60,
        'items' => [[
            'item_type' => 'product',
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ])->assertRedirect();

    expect(AuditLog::where('action', 'product_stock_out')->where('record_id', $product->id)->exists())->toBeTrue();

    $this->actingAs($manager)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(AuditLog::where('action', 'product_deleted')->where('record_id', $product->id)->exists())->toBeTrue();
});

it('records login and logout events and restricts activity logs to management roles', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    AuditLog::create([
        'user_id' => $manager->id,
        'action' => 'product_updated',
        'module' => 'Products',
        'record_type' => 'Product',
        'record_id' => 1,
        'description' => 'Updated product details.',
        'old_values' => ['price' => 50, 'active' => false],
        'new_values' => ['price' => 60, 'active' => true],
    ]);

    $this->post(route('login'), [
        'email' => $manager->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->get(route('activity-logs.index'))
        ->assertOk()
        ->assertSee('Activity Logs')
        ->assertSee('user login')
        ->assertSee($manager->name)
        ->assertSee('Price')
        ->assertSee('50')
        ->assertSee('60')
        ->assertSee('No')
        ->assertSee('Yes')
        ->assertDontSee('<pre', false);

    $this->post(route('logout'))->assertRedirect('/');

    expect(AuditLog::where('user_id', $manager->id)->where('action', 'user_login')->exists())->toBeTrue()
        ->and(AuditLog::where('user_id', $manager->id)->where('action', 'user_logout')->exists())->toBeTrue();

    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $this->actingAs($frontDesk)
        ->get(route('activity-logs.index'))
        ->assertForbidden();
});
