<?php

use App\Models\GeneratedReport;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('shows POS product totals in the sales report', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $soldProduct = Product::create([
        'name' => 'Pet Shampoo',
        'sku' => 'SALES-PRODUCT-SOLD-001',
        'price' => 20,
        'active' => true,
    ]);
    Product::create([
        'name' => 'Pet Brush',
        'sku' => 'SALES-PRODUCT-UNSOLD-001',
        'price' => 15,
        'active' => true,
    ]);
    $saleDate = now()->setTime(14, 5, 9);
    $sale = Sale::create([
        'invoice_number' => 'SALES-PRODUCT-001',
        'user_id' => $manager->id,
        'subtotal' => 40,
        'discount' => 0,
        'vat_rate' => 12,
        'vat_amount' => 4.8,
        'total' => 44.8,
        'amount_paid' => 44.8,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => $saleDate,
        'updated_at' => $saleDate,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $soldProduct->id,
        'item_type' => 'product',
        'item_name' => 'Pet Shampoo',
        'unit_price' => 20,
        'quantity' => 2,
        'discount' => 0,
        'line_total' => 40,
    ]);

    $response = $this->actingAs($manager)
        ->get(route('reports.sales', [
            'from' => $saleDate->toDateString(),
            'to' => $saleDate->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Best-selling products')
        ->assertSee('Best seller')
        ->assertSee('Product sales mix')
        ->assertSee('Pet Brush')
        ->assertSee('Pet Shampoo')
        ->assertSee('Product units sold');

    expect($response->viewData('sales')->perPage())->toBe(10);
});

it('saves and downloads sales reports as PDFs for the selected date range', function () {
    Storage::fake('local');
    $manager = User::factory()->create(['role' => 'manager']);
    $saleDate = now()->setTime(14, 5, 9);

    Sale::create([
        'invoice_number' => 'CSV-DATE-001',
        'user_id' => $manager->id,
        'subtotal' => 100,
        'discount' => 0,
        'vat_rate' => 12,
        'vat_amount' => 12,
        'total' => 112,
        'amount_paid' => 112,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_at' => $saleDate,
        'updated_at' => $saleDate,
    ]);

    $this->actingAs($manager)
        ->post(route('reports.generate', 'sales'), [
            'from' => $saleDate->toDateString(),
            'to' => $saleDate->toDateString(),
        ])
        ->assertRedirect(route('reports.index'));

    $saleDate = Sale::firstOrFail()->created_at;
    $report = GeneratedReport::firstOrFail();
    $pdf = Storage::disk('local')->get($report->file_path);

    expect($report->file_path)->toEndWith('.pdf')
        ->and(substr($pdf, 0, 4))->toBe('%PDF');

    $this->actingAs($manager)
        ->get(route('reports.download', $report))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('makes dates readable when an older generated sales report is downloaded again', function () {
    Storage::fake('local');
    $manager = User::factory()->create(['role' => 'manager']);
    $report = GeneratedReport::create([
        'user_id' => $manager->id,
        'type' => 'sales',
        'title' => 'Sales Report',
        'from_date' => today(),
        'to_date' => today(),
        'file_path' => 'reports/older-sales.csv',
    ]);
    Storage::disk('local')->put(
        $report->file_path,
        "Date,Customer,Payment Method,Subtotal,Discount,VAT,Total\n2026-09-28 14:05:09,Jamie,cash,100,0,12,112\n"
    );

    $response = $this->actingAs($manager)->get(route('reports.download', $report));

    $response->assertOk();
    expect($response->getContent())->toContain("'2026-09-28 14:05:09");
});

it('saves all report sections in one combined PDF', function () {
    Storage::fake('local');
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)
        ->post(route('reports.generate-complete'), [
            'from' => today()->subDay()->toDateString(),
            'to' => today()->toDateString(),
        ])
        ->assertRedirect(route('reports.index'));

    $report = GeneratedReport::firstOrFail();
    expect($report->type)->toBe('complete')
        ->and($report->file_path)->toEndWith('.pdf');

    $contents = Storage::disk('local')->get($report->file_path);
    expect(substr($contents, 0, 4))->toBe('%PDF');

    $this->actingAs($manager)
        ->get(route('reports.download', $report))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});