<?php

use App\Models\Customer;
use App\Models\GroomingAddon;
use App\Models\GeneratedReport;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;

it('shows 10 products, inventory items, and customers per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 31) as $number) {
        $label = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

        Product::create([
            'name' => 'Pagination Product ' . $label,
            'sku' => 'PAGINATION-PRODUCT-' . $label,
            'price' => 20,
            'stock' => 0,
        ]);

        Customer::create([
            'name' => 'Pagination Customer ' . $label,
        ]);
    }

    foreach ([
        ['products.index', 'products'],
        ['inventory.index', 'products'],
        ['customers.index', 'customers'],
    ] as [$route, $paginatorName]) {
        $firstPage = $this->actingAs($admin)
            ->get(route($route))
            ->assertOk()
            ->assertSee('page=2', false);
        $firstPageItems = $firstPage->viewData($paginatorName);

        expect($firstPageItems->perPage())->toBe(10)
            ->and($firstPageItems->total())->toBe(31)
            ->and($firstPageItems->count())->toBe(10);

        $secondPage = $this->get(route($route, ['page' => 2]))->assertOk();
        $secondPageItems = $secondPage->viewData($paginatorName);

        expect($secondPageItems->currentPage())->toBe(2)
            ->and($secondPageItems->count())->toBe(10);
    }
});

it('searches inventory by product name, SKU, and category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Product::create([
        'name' => 'Gentle Oatmeal Wash',
        'sku' => 'GROOM-WASH-001',
        'category' => 'Shampoo',
        'price' => 20,
        'stock' => 0,
    ]);
    Product::create([
        'name' => 'Soft Bristle Brush',
        'sku' => 'BRUSH-001',
        'category' => 'Tools',
        'price' => 15,
        'stock' => 0,
    ]);

    foreach (['Oatmeal', 'GROOM-WASH', 'Shampoo'] as $search) {
        $response = $this->actingAs($admin)
            ->get(route('inventory.index', ['search' => $search]))
            ->assertOk();

        expect($response->viewData('products')->total())->toBe(1)
            ->and($response->viewData('products')->first()->name)->toBe('Gentle Oatmeal Wash');
    }

    $categoryResponse = $this->get(route('inventory.index', ['category' => 'Tools']))
        ->assertOk();

    expect($categoryResponse->viewData('products')->pluck('name')->all())
        ->toBe(['Soft Bristle Brush']);
});

it('paginates saved reports in the Reports Overview by 10', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 31) as $number) {
        $label = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        $createdAt = now()->addMinutes($number);

        $report = GeneratedReport::create([
            'user_id' => $admin->id,
            'type' => 'sales',
            'title' => 'Pagination Report ' . $label,
            'from_date' => today(),
            'to_date' => today(),
            'file_path' => 'reports/pagination-' . $label . '.pdf',
        ]);
        $report->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
    }

    $firstPage = $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('page=2', false);
    $firstPageReports = $firstPage->viewData('generatedReports');

    expect($firstPageReports->perPage())->toBe(10)
        ->and($firstPageReports->total())->toBe(31)
        ->and($firstPageReports->count())->toBe(10);

    $secondPage = $this->get(route('reports.index', ['page' => 2]))->assertOk();
    $secondPageReports = $secondPage->viewData('generatedReports');

    expect($secondPageReports->currentPage())->toBe(2)
        ->and($secondPageReports->count())->toBe(10);
});

it('paginates services and archived customers by 10 and pages grooming add-ons', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    foreach (range(1, 16) as $number) {
        $label = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

        Service::create([
            'name' => 'Pagination Service ' . $label,
            'species' => 'Cat',
            'size' => 'Any',
            'rate' => 100,
            'active' => true,
        ]);

        GroomingAddon::create([
            'name' => 'Pagination Add-on ' . $label,
            'category' => 'extra_service',
            'rate' => 25,
            'active' => true,
        ]);

        Customer::create([
            'name' => 'Archived Pagination Customer ' . $label,
        ])->delete();
    }

    $servicesResponse = $this->actingAs($manager)
        ->get(route('services.index'))
        ->assertOk()
        ->assertSee('addonPageSize: 10', false)
        ->assertSee('Page ${addonPage} of ${addonPageCount}', false)
        ->assertSee('page=2', false);

    expect($servicesResponse->viewData('services')->perPage())->toBe(10)
        ->and($servicesResponse->viewData('services')->total())->toBe(16)
        ->and($servicesResponse->viewData('services')->count())->toBe(10)
        ->and($servicesResponse->viewData('groomingAddons')->count())->toBe(16);

    $archivedCustomersResponse = $this->get(route('archive.index', ['type' => 'customers']))
        ->assertOk()
        ->assertSee('page=2', false);

    expect($archivedCustomersResponse->viewData('records')->perPage())->toBe(10)
        ->and($archivedCustomersResponse->viewData('records')->total())->toBe(16)
        ->and($archivedCustomersResponse->viewData('records')->count())->toBe(10);
});