<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;

it('allows managers to access all business and administration areas', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager);

    foreach ([
        'dashboard',
        'pos.index',
        'products.index',
        'inventory.index',
        'customers.index',
        'services.index',
        'settings.edit',
        'users.index',
        'grooming.index',
        'grooming-prices.index',
        'reports.index',
        'reports.sales',
        'reports.products',
        'reports.grooming',
        'reports.inventory',
        'activity-logs.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }

    $this->get(route('dashboard'))
        ->assertDontSee('Owner dashboard view', false);
});

it('limits front desk to POS, products, and grooming', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->actingAs($frontDesk);

    foreach ([
        'dashboard',
        'pos.index',
        'products.index',
        'grooming.index',
        'customers.index',
        'reports.sales',
        'reports.grooming',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }

    $this->get(route('dashboard'))
        ->assertSee(route('pos.index'), false)
        ->assertSee(route('products.index'), false)
        ->assertSee(route('grooming.index'), false)
        ->assertSee(route('customers.index'), false)
        ->assertSee(route('reports.sales'), false)
        ->assertSee(route('reports.grooming'), false)
        ->assertDontSee(route('activity-logs.index'), false)
        ->assertDontSee(route('categories.index'), false)
        ->assertDontSee(route('archive.index'), false);

    foreach ([
        'inventory.index',
        'categories.index',
        'archive.index',
        'services.index',
        'settings.edit',
        'users.index',
        'grooming-prices.index',
        'reports.index',
        'reports.products',
        'reports.inventory',
        'activity-logs.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }

    $customer = Customer::create(['name' => 'Front Desk Customer', 'address' => 'Davao City']);
    $this->get(route('customers.index'))
        ->assertOk()
        ->assertSee($customer->name)
        ->assertSee('Add Customer')
        ->assertDontSee('Delete Customer')
        ->assertDontSee('>Delete</button>', false);
    $this->get(route('customers.show', $customer))->assertOk();
    $this->get(route('customers.edit', $customer))->assertOk();
    $this->post(route('customers.store'), [
        'name' => 'New Front Desk Customer',
        'address' => 'Davao City',
    ])->assertRedirect(route('customers.index'));
    $this->delete(route('customers.destroy', $customer))->assertForbidden();
});

it('allows owners to access their dashboard, reports, activity logs, settings, and archive', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $this->actingAs($owner);

    foreach ([
        'dashboard',
        'reports.index',
        'reports.sales',
        'reports.products',
        'reports.grooming',
        'reports.inventory',
        'activity-logs.index',
        'settings.edit',
        'archive.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }

    $this->get(route('dashboard'))
        ->assertSee('Sales Report')
        ->assertSee('Product Report')
        ->assertSee('Grooming Report')
        ->assertSee('Inventory Report')
        ->assertSee('report=sales', false)
        ->assertSee('report=products', false)
        ->assertSee('report=grooming', false)
        ->assertSee('report=inventory', false);

    $this->get(route('dashboard', ['view' => 'overview']))
        ->assertOk()
        ->assertSee('Owner dashboard view', false)
        ->assertSee('Overview')
        ->assertSee('Reports')
        ->assertDontSee('id="owner-reports-heading"', false);

    foreach ([
        'sales' => 'Sales',
        'products' => 'Product',
        'grooming' => 'Grooming',
        'inventory' => 'Inventory',
    ] as $report => $title) {
        $this->get(route('dashboard', ['report' => $report]))
            ->assertOk()
            ->assertSee('id="owner-report-results"', false)
            ->assertSee($title . ' Report');
    }

    foreach (['pos.index', 'products.index', 'inventory.index', 'grooming.index'] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }
});

it('limits customer accounts to POS and grooming', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer);

    $this->get(route('pos.index'))->assertOk();
    $this->get(route('grooming.index'))
        ->assertRedirect(route('grooming.create'));
    $this->get(route('grooming.create'))->assertOk();

    foreach ([
        'dashboard',
        'products.index',
        'inventory.index',
        'customers.index',
        'services.index',
        'settings.edit',
        'users.index',
        'grooming-prices.index',
        'reports.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }
});

it('sends front desk and owners to their first allowed page after login', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->post(route('login'), [
        'email' => $frontDesk->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    auth()->logout();

    $owner = User::factory()->create(['role' => 'owner']);

    $this->post(route('login'), [
        'email' => $owner->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    auth()->logout();

    $customer = User::factory()->create(['role' => 'customer']);

    $this->post(route('login'), [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect(route('pos.index', absolute: false));
});

it('does not redirect restricted roles back to forbidden intended pages', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->withSession(['url.intended' => route('inventory.index', absolute: false)])
        ->post(route('login'), [
            'email' => $frontDesk->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('dashboard', absolute: false));
});

it('allows front desk to transfer stock without opening the inventory page', function () {
    $product = Product::create([
        'name' => 'Role Access Product',
        'sku' => 'ROLE-ACCESS-001',
        'cost' => 10,
        'price' => 20,
        'stock' => 10,
        'available_stock' => 2,
        'low_stock_level' => 1,
        'active' => true,
    ]);
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($frontDesk)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('role="combobox"', false)
        ->assertSee($product->name);

    $this->actingAs($frontDesk)
        ->post(route('inventory.allocate', $product), ['quantity' => 1])
        ->assertRedirect(route('products.index'));

    expect($product->fresh()->stock)->toBe(9)
        ->and($product->fresh()->available_stock)->toBe(3);

    $this->actingAs($frontDesk)
        ->get(route('inventory.index'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->get(route('products.index'))
        ->assertSee('role="combobox"', false)
        ->assertSee($product->name);
});