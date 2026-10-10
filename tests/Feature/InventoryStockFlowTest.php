<?php

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;

it('creates catalog products with zero stock until a receipt is recorded', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($manager)->post(route('products.store'), [
        'name' => 'Organic Dog Shampoo',
        'sku' => 'DOG-SHAM-001',
        'category' => 'Grooming Supplies',
        'cost' => 100,
        'price' => 180,
        'low_stock_level' => 5,
    ]);

    $response->assertRedirect(route('inventory.index'));
    expect(Product::where('sku', 'DOG-SHAM-001')->value('stock'))->toBe(0);
});

it('records stock received as an identified batch', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Cat Food',
        'sku' => 'CAT-FOOD-001',
        'cost' => 50,
        'price' => 80,
        'stock' => 3,
        'low_stock_level' => 5,
    ]);
    $arrivalAt = now()->subHours(2)->startOfMinute();

    $response = $this->actingAs($manager)->post(route('inventory.stock-in', $product), [
        'quantity' => 12,
        'arrival_at' => $arrivalAt->format('Y-m-d\\TH:i'),
        'supplier' => 'Paws Wholesale',
        'expiry_date' => today()->addDays(120)->toDateString(),
        'reason' => 'Morning delivery at rear entrance',
    ]);

    $response->assertRedirect();
    expect($product->fresh()->stock)->toBe(15);
    $batch = $product->stockBatches()->where('supplier', 'Paws Wholesale')->firstOrFail();
    expect($batch->inventory_quantity)->toBe(12)
        ->and($batch->supplier)->toBe('Paws Wholesale')
        ->and($batch->received_at->toDateTimeString())->toBe($arrivalAt->toDateTimeString());
    expect(StockMovement::where('product_id', $product->id)->first())
        ->batch_reference->toStartWith('RECEIVED-')
        ->product_stock_batch_id->toBe($batch->id)
        ->quantity->toBe(12)
        ->stock_before->toBe(3)
        ->stock_after->toBe(15)
        ->reason->toBe('Morning delivery at rear entrance');

    $this->actingAs($manager)
        ->get(route('inventory.history', $product))
        ->assertOk()
        ->assertSee('Supplier')
        ->assertSee('Paws Wholesale')
        ->assertSee('Movement Reason')
        ->assertSee('data-reason="Morning delivery at rear entrance"', false)
        ->assertSee('aria-label="View reason for Cat Food stock movement"', false);
});

it('updates the product expiry schedule when a new batch replaces expired stock', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Expired Cat Food',
        'sku' => 'EXPIRED-CAT-FOOD-001',
        'cost' => 50,
        'price' => 80,
        'stock' => 3,
        'available_stock' => 0,
        'low_stock_level' => 1,
        'expiry_date' => today()->subDay(),
    ]);

    $response = $this->actingAs($manager)->post(route('inventory.stock-in', $product), [
        'quantity' => 12,
        'arrival_at' => now()->format('Y-m-d\\TH:i'),
        'expiry_date' => today()->addDays(120)->toDateString(),
    ]);

    $response->assertRedirect();
    expect($product->fresh()->expiry_date->toDateString())->toBe(today()->addDays(120)->toDateString())
        ->and($product->hasExpiredStockBatches())->toBeTrue();
});

it('shows stock-in and import dialogs from the product inventory header', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    Category::create(['name' => 'Grooming Supplies']);
    $product = Product::create([
        'name' => 'Modal Test Shampoo',
        'sku' => 'MODAL-SHAMPOO-001',
        'cost' => 20,
        'price' => 40,
        'stock' => 5,
        'low_stock_level' => 1,
    ]);

    $this->actingAs($manager)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('Add Item')
        ->assertSee('id="add-item-title"', false)
        ->assertSee('action="' . route('products.store') . '"', false)
        ->assertSee('name="sku"', false)
        ->assertSee('name="category"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('role="listbox"', false)
        ->assertDontSee('@focus="categoryDropdownOpen = true"', false)
        ->assertSee('@input="categoryDropdownOpen = true"', false)
        ->assertSee('aria-label="Toggle saved categories"', false)
        ->assertSee('@click="categoryDropdownOpen = !categoryDropdownOpen"', false)
        ->assertSee('Type a category')
        ->assertSee('Grooming Supplies')
        ->assertDontSee('Uncategorized')
        ->assertSee('name="cost"', false)
        ->assertSee('name="price"', false)
        ->assertSee('Stock In')
        ->assertSee('x-show="stockInOpen"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('name="product_id"', false)
        ->assertSee('name="quantity"', false)
        ->assertSee('name="arrival_at"', false)
        ->assertSee('name="expiry_date"', false)
        ->assertSee('name="reason"', false)
        ->assertSee('>View</a>', false)
        ->assertSee('Edit Item')
        ->assertSee('openEditItem(JSON.parse($event.currentTarget.dataset.editItem))', false)
        ->assertSee('class="grid w-24 grid-cols-2 gap-x-1"', false)
        ->assertSee('record-action-secondary inline-flex min-h-8 w-full items-center justify-center whitespace-nowrap', false)
        ->assertSee('w-[8rem] min-w-[8rem]', false)
        ->assertSee('class="col-span-2 mt-1.5 w-full border-t border-slate-200 pt-1.5"', false)
        ->assertSee('class="mt-4 grid w-full grid-cols-2 gap-2"', false)
        ->assertSee('class="col-span-2 border-t border-slate-200 pt-2"', false)
        ->assertSee('record-action-secondary inline-flex min-h-10 w-full items-center justify-center', false)
        ->assertSee('>Delete</button>', false)
        ->assertSee('Import Products')
        ->assertDontSee('Add to Products')
        ->assertSee('x-show="importOpen"', false)
        ->assertSee('data-inventory-import', false)
        ->assertSee('name="spreadsheet"', false)
        ->assertSee('Download column template')
        ->assertSee('data-import-status', false);
});

it('transfers only the selected quantity from inventory to products', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Bulk Pet Food',
        'sku' => 'BULK-FOOD-001',
        'cost' => 100,
        'price' => 150,
        'stock' => 1000,
        'available_stock' => 0,
        'low_stock_level' => 10,
    ]);

    $response = $this->actingAs($manager)->post(route('inventory.allocate', $product), [
        'quantity' => 250,
    ]);

    $response->assertRedirect(route('products.index'));
    expect($product->fresh()->stock)->toBe(750)
        ->and($product->fresh()->available_stock)->toBe(250);
    expect(StockMovement::where('product_id', $product->id)->first())
        ->type->toBe('allocation')
        ->stock_before->toBe(1000)
        ->stock_after->toBe(750)
        ->available_before->toBe(0)
        ->available_after->toBe(250);
});

it('rejects transfers larger than the warehouse balance', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Limited Warehouse Item',
        'sku' => 'LIMITED-WH-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 5,
        'available_stock' => 0,
        'low_stock_level' => 2,
    ]);

    $this->actingAs($manager)->from(route('products.index'))
        ->post(route('inventory.allocate', $product), ['quantity' => 6])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('error', 'The quantity entered is over the stock in Inventory. Only 5 units are available.');

    expect($product->fresh()->stock)->toBe(5)
        ->and($product->fresh()->available_stock)->toBe(0)
        ->and(StockMovement::where('product_id', $product->id)->exists())->toBeFalse();
});

it('accepts a zero transfer as a no-op without changing stock', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Zero Transfer Item',
        'sku' => 'ZERO-TRANSFER-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 10,
        'available_stock' => 3,
        'low_stock_level' => 2,
    ]);

    $this->actingAs($manager)->post(route('inventory.allocate', $product), ['quantity' => 0])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', 'No stock was transferred.');

    expect($product->fresh()->stock)->toBe(10)
        ->and($product->fresh()->available_stock)->toBe(3);
});

it('sells from allocated Products stock without decrementing warehouse inventory', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    $product = Product::create([
        'name' => 'Allocated Treats',
        'sku' => 'TREATS-001',
        'cost' => 20,
        'price' => 50,
        'stock' => 1000,
        'available_stock' => 2,
        'low_stock_level' => 1,
    ]);

    $this->actingAs($cashier)->post(route('pos.checkout'), [
        'payment_method' => 'cash',
        'amount_paid' => 50,
        'items' => [[
            'item_type' => 'product',
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ])->assertRedirect();

    expect($product->fresh()->stock)->toBe(1000)
        ->and($product->fresh()->available_stock)->toBe(1);
    expect(StockMovement::where('sale_id', Sale::latest('id')->value('id'))->first())
        ->stock_before->toBe(1000)
        ->stock_after->toBe(1000)
        ->available_before->toBe(2)
        ->available_after->toBe(1);
});

it('shows only allocated product quantities in POS', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    Product::create([
        'name' => 'Warehouse Only Food',
        'sku' => 'WAREHOUSE-ONLY',
        'cost' => 10,
        'price' => 20,
        'stock' => 1000,
        'available_stock' => 0,
        'low_stock_level' => 2,
    ]);
    Product::create([
        'name' => 'Allocated Food',
        'sku' => 'ALLOCATED-FOOD',
        'cost' => 10,
        'price' => 20,
        'stock' => 980,
        'available_stock' => 20,
        'low_stock_level' => 2,
    ]);

    $response = $this->actingAs($cashier)->get(route('pos.index'));

    $response->assertOk()
        ->assertSee('Allocated Food')
        ->assertDontSee('Warehouse Only Food');
});

it('rejects checkout quantities above allocated stock even when inventory has more', function () {
    $cashier = User::factory()->create(['role' => 'frontdesk']);
    $product = Product::create([
        'name' => 'Limited Product',
        'sku' => 'LIMITED-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 1000,
        'available_stock' => 2,
        'low_stock_level' => 1,
    ]);

    $this->actingAs($cashier)->postJson(route('pos.checkout'), [
        'payment_method' => 'cash',
        'amount_paid' => 67.2,
        'items' => [[
            'item_type' => 'product',
            'product_id' => $product->id,
            'quantity' => 3,
        ]],
    ])->assertUnprocessable()->assertJsonValidationErrors('items');

    expect($product->fresh()->stock)->toBe(1000)
        ->and($product->fresh()->available_stock)->toBe(2);
});

it('imports inventory rows into the shared product catalog and records stock changes', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Existing Shampoo',
        'sku' => 'SHAM-001',
        'cost' => 40,
        'price' => 75,
        'stock' => 4,
        'low_stock_level' => 2,
    ]);

    $response = $this->actingAs($manager)->postJson(route('inventory.import'), [
        'rows' => [
            [
                'name' => 'Updated Shampoo',
                'sku' => 'SHAM-001',
                'category' => 'Care',
                'cost' => 42,
                'price' => 80,
                'stock' => 9,
                'low_stock_level' => 3,
                'expiry_date' => null,
            ],
            [
                'name' => 'New Pet Brush',
                'sku' => 'BRUSH-001',
                'category' => 'Accessories',
                'cost' => 20,
                'price' => 45,
                'stock' => 6,
                'low_stock_level' => 2,
                'expiry_date' => null,
            ],
        ],
    ]);

    $response->assertOk()->assertJson(['created' => 1, 'updated' => 1]);
    expect(AuditLog::where('action', 'product_created')->where('record_id', Product::where('sku', 'BRUSH-001')->value('id'))->exists())->toBeTrue()
        ->and(AuditLog::where('action', 'product_updated')->where('record_id', $product->id)->exists())->toBeTrue();
    expect(AuditLog::where('action', 'inventory_stock_in')->where('record_id', $product->id)->exists())->toBeTrue();
    expect($product->fresh()->name)->toBe('Updated Shampoo')
        ->and($product->fresh()->stock)->toBe(9)
        ->and(Product::where('sku', 'BRUSH-001')->value('stock'))->toBe(6)
        ->and(StockMovement::where('product_id', $product->id)->first()->batch_reference)->toStartWith('IMPORT-');
});

it('shows stock status using each page balance and the configured minimum', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    foreach ([
        ['A Above Minimum', 'STATUS-ABOVE', 6, 6],
        ['B At Minimum', 'STATUS-MINIMUM', 5, 5],
        ['C Below Minimum', 'STATUS-BELOW', 4, 4],
        ['D Empty', 'STATUS-EMPTY', 0, 0],
    ] as [$name, $sku, $inventoryStock, $availableStock]) {
        Product::create([
            'name' => $name,
            'sku' => $sku,
            'cost' => 10,
            'price' => 20,
            'stock' => $inventoryStock,
            'available_stock' => $availableStock,
            'low_stock_level' => 5,
        ]);
    }

    $this->actingAs($manager)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'A Above Minimum', 'In Stock',
            'B At Minimum', 'Low Stock',
            'C Below Minimum', 'Low Stock',
            'D Empty', 'Out of Stock',
        ]);

    $this->actingAs($manager)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'A Above Minimum', 'In Stock',
            'B At Minimum', 'Low Stock',
            'C Below Minimum', 'Out of Stock',
            'D Empty', 'Out of Stock',
        ]);
});

it('shows batch expiry status on inventory, products, and the dashboard within ten days', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    Setting::setValue('expiry_discount_enabled', 0);

    $nearExpiry = Product::create([
        'name' => 'Near Expiry Batch',
        'sku' => 'NEAR-EXPIRY-BATCH',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 1,
        'active' => true,
    ]);
    $nearExpiry->stockBatches()->create([
        'batch_reference' => 'NEAR-10-DAYS',
        'expiry_date' => today()->addDays(10),
        'inventory_quantity' => 2,
        'available_quantity' => 1,
    ]);
    $nearExpiry->syncStockTotals();

    $expired = Product::create([
        'name' => 'Expired Batch',
        'sku' => 'EXPIRED-BATCH-STATUS',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 1,
        'active' => true,
    ]);
    $expired->stockBatches()->create([
        'batch_reference' => 'EXPIRED-LOT-STATUS',
        'expiry_date' => today()->subDay(),
        'inventory_quantity' => 2,
        'available_quantity' => 0,
    ]);
    $expired->syncStockTotals();

    expect(Setting::expiryWarningDays())->toBe(10);

    foreach ([route('inventory.index'), route('products.index')] as $url) {
        $this->actingAs($manager)
            ->get($url)
            ->assertOk()
            ->assertSee('Near Expiry')
            ->assertSee('Expired')
            ->assertSee('bg-amber-50')
            ->assertSee('bg-red-50')
            ->when($url === route('products.index'), function ($response) {
                $response->assertSee('title="Near Expiry"', false)
                    ->assertSee('title="Expired"', false);
            });
    }

    $this->actingAs($manager)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('NEAR-10-DAYS')
        ->assertSee('Near Expiry Batch')
        ->assertDontSee('EXPIRED-LOT-STATUS');
});

it('marks a product near expiry when its remaining legacy batch has no batch date', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Legacy Expiry Shampoo',
        'sku' => 'LEGACY-EXPIRY-SHAMPOO',
        'cost' => 10,
        'price' => 20,
        'stock' => 4,
        'available_stock' => 0,
        'low_stock_level' => 1,
        'active' => true,
    ]);
    $product->update(['expiry_date' => today()->addDays(10)]);

    expect($product->fresh()->hasNearExpiryStockBatches())->toBeTrue();

    $this->actingAs($manager)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSeeInOrder(['Legacy Expiry Shampoo', 'Near Expiry']);

    $this->actingAs($manager)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('Legacy Expiry Shampoo')
        ->assertDontSee('Near Expiry');
});
