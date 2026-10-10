<?php

use App\Models\Customer;
use App\Models\GeneratedReport;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('shows all archive modules to owners and keeps front desk archive limited', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $owner = User::factory()->create(['role' => 'owner']);
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->actingAs($manager)
        ->get(route('archive.index'))
        ->assertOk()
        ->assertSee('Archive')
        ->assertSee('Products')
        ->assertSee('Categories')
        ->assertSee('Expired batches')
        ->assertSee('Customers')
        ->assertSee('Pets')
        ->assertSee('Services')
        ->assertSee('Grooming prices')
        ->assertSee('Accounts')
        ->assertSee('Saved reports');

    foreach (['products', 'categories', 'expired-batches', 'customers', 'pets', 'services', 'grooming-prices', 'users', 'reports'] as $type) {
        $this->get(route('archive.index', ['type' => $type]))->assertOk();
    }

    $this->actingAs($owner)
        ->get(route('archive.index'))
        ->assertOk()
        ->assertSee('Products')
        ->assertSee('Categories')
        ->assertSee('Expired batches')
        ->assertSee('Customers')
        ->assertSee('Pets')
        ->assertSee('Services')
        ->assertSee('Grooming prices')
        ->assertSee('Accounts')
        ->assertSee('Saved reports')
        ->assertSee(route('archive.index', ['type' => 'users']), false);

    $this->get(route('archive.index', ['type' => 'users']))
        ->assertOk();

    $this->actingAs($frontDesk)
        ->get(route('archive.index'))
        ->assertOk()
        ->assertSee('Products')
        ->assertDontSee('href="' . route('archive.index', ['type' => 'customers']) . '"', false)
        ->assertDontSee('href="' . route('archive.index', ['type' => 'reports']) . '"', false);

    $this->get(route('archive.index', ['type' => 'customers']))
        ->assertNotFound();
});

it('archives a customer with its pets and restores both together', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $customer = Customer::create([
        'name' => 'Archive Test Customer',
        'address' => '12 Pet Lane',
    ]);
    $pet = Pet::create([
        'customer_id' => $customer->id,
        'name' => 'Milo',
    ]);

    $this->actingAs($manager)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    expect(Customer::onlyTrashed()->find($customer->id))->not->toBeNull()
        ->and(Pet::onlyTrashed()->find($pet->id))->not->toBeNull();

    $this->post(route('archive.restore', ['type' => 'customers', 'id' => $customer->id]))
        ->assertRedirect(route('archive.index', ['type' => 'customers']));

    expect(Customer::find($customer->id))->not->toBeNull()
        ->and(Pet::find($pet->id))->not->toBeNull();
});

it('restores archived products and permits a separate permanent deletion', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $owner = User::factory()->create(['role' => 'owner']);
    $product = Product::create([
        'name' => 'Archiveable Brush',
        'sku' => 'ARCHIVEABLE-BRUSH',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
    ]);

    $this->actingAs($manager)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $this->actingAs($owner)
        ->get(route('archive.index', ['type' => 'products']))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('Permanently Delete');

    $this->post(route('archive.restore', ['type' => 'products', 'id' => $product->id]))
        ->assertRedirect(route('archive.index', ['type' => 'products']));

    expect(Product::find($product->id))->not->toBeNull();

    $this->actingAs($manager)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $this->actingAs($owner)
        ->delete(route('archive.destroy', ['type' => 'products', 'id' => $product->id]))
        ->assertRedirect(route('archive.index', ['type' => 'products']));

    expect(Product::withTrashed()->find($product->id))->toBeNull();
});

it('keeps report files while archived and deletes them only on permanent purge', function () {
    Storage::fake('local');
    $manager = User::factory()->create(['role' => 'manager']);
    $filePath = 'reports/archive-test.csv';
    Storage::disk('local')->put($filePath, "title\nArchive Test\n");
    $report = GeneratedReport::create([
        'user_id' => $manager->id,
        'type' => 'inventory',
        'title' => 'Archive Test Report',
        'from_date' => today(),
        'to_date' => today(),
        'file_path' => $filePath,
    ]);

    $this->actingAs($manager)
        ->delete(route('reports.destroy', $report))
        ->assertRedirect(route('reports.index'));

    expect(Storage::disk('local')->exists($filePath))->toBeTrue();

    $this->get(route('archive.index', ['type' => 'reports']))
        ->assertOk()
        ->assertSee('Archive Test Report');

    $this->post(route('archive.restore', ['type' => 'reports', 'id' => $report->id]))
        ->assertRedirect(route('archive.index', ['type' => 'reports']));

    expect(GeneratedReport::find($report->id))->not->toBeNull()
        ->and(Storage::disk('local')->exists($filePath))->toBeTrue();

    $this->delete(route('reports.destroy', $report))
        ->assertRedirect(route('reports.index'));
    $this->delete(route('archive.destroy', ['type' => 'reports', 'id' => $report->id]))
        ->assertRedirect(route('archive.index', ['type' => 'reports']));

    expect(GeneratedReport::withTrashed()->find($report->id))->toBeNull()
        ->and(Storage::disk('local')->exists($filePath))->toBeFalse();
});

it('permanently deletes eligible records in the selected archive module and keeps protected records', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $eligible = Product::create([
        'name' => 'Disposable Archived Item',
        'sku' => 'DISPOSABLE-ARCHIVE-ITEM',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
    ]);
    $protected = Product::create([
        'name' => 'Stocked Archived Item',
        'sku' => 'STOCKED-ARCHIVE-ITEM',
        'cost' => 10,
        'price' => 20,
        'stock' => 3,
        'available_stock' => 0,
    ]);

    $this->actingAs($manager)
        ->delete(route('products.destroy', $eligible))
        ->assertRedirect(route('products.index'));
    $this->delete(route('products.destroy', $protected))
        ->assertRedirect(route('products.index'));

    $this->get(route('archive.index', ['type' => 'products']))
        ->assertOk()
        ->assertSee('Permanently Delete All');

    $this->delete(route('archive.destroy-all', ['type' => 'products']))
        ->assertRedirect(route('archive.index', ['type' => 'products']))
        ->assertSessionHas('success', 'Permanently deleted 1 archived record(s); 1 protected record(s) remain archived.');

    expect(Product::withTrashed()->find($eligible->id))->toBeNull()
        ->and(Product::onlyTrashed()->find($protected->id))->not->toBeNull();
});