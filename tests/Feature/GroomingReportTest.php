<?php

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

it('paginates grooming rows by 10 while keeping chart aggregates complete', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $saleDate = now();
    $sale = Sale::create([
        'invoice_number' => 'GROOMING-PAGINATION-001',
        'user_id' => $manager->id,
        'subtotal' => 31,
        'discount' => 0,
        'vat_rate' => 0,
        'vat_amount' => 0,
        'total' => 31,
        'amount_paid' => 31,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => $saleDate,
        'updated_at' => $saleDate,
    ]);

    foreach (range(1, 31) as $number) {
        SaleItem::create([
            'sale_id' => $sale->id,
            'item_type' => 'service',
            'item_name' => 'Grooming Service ' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'unit_price' => 1,
            'quantity' => 1,
            'discount' => 0,
            'line_total' => 1,
        ]);
    }

    $dates = [
        'from' => $saleDate->toDateString(),
        'to' => $saleDate->toDateString(),
    ];
    $firstPage = $this->actingAs($manager)
        ->get(route('reports.grooming', $dates))
        ->assertOk()
        ->assertSee('page=2', false);

    expect($firstPage->viewData('paginatedItems')->total())->toBe(31)
        ->and($firstPage->viewData('paginatedItems')->count())->toBe(10)
        ->and($firstPage->viewData('items')->count())->toBe(31);

    $secondPage = $this->get(route('reports.grooming', $dates + ['page' => 2]))->assertOk();
    expect($secondPage->viewData('paginatedItems')->currentPage())->toBe(2)
        ->and($secondPage->viewData('paginatedItems')->count())->toBe(10)
        ->and($secondPage->viewData('items')->count())->toBe(31);
});