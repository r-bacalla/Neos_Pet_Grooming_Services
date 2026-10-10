<?php

use App\Models\GroomingAddon;
use App\Models\GroomingAppointment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\User;

it('creates standalone priced add-ons and charges them through Grooming without touching Products', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $service = Service::create([
        'name' => 'Full Groom',
        'description' => 'Main grooming service',
        'rate' => 250,
        'size' => 'Medium',
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('grooming-addons.store'), [
            'name' => 'Scented Soap',
            'description' => 'Fresh floral scent',
            'rate' => 75,
        ])
        ->assertRedirect(route('services.index'));

    $addon = GroomingAddon::where('name', 'Scented Soap')->firstOrFail();
    $service->update(['available_add_on_ids' => [$addon->id]]);
    expect((float) $addon->rate)->toBe(75.0);

    $this->actingAs($manager)
        ->get(route('services.index'))
        ->assertOk()
        ->assertSee('Add Add-on')
        ->assertSee('Add Grooming Add-on')
        ->assertSee('Pet type *')
        ->assertSee('Category *')
        ->assertDontSee('Included add-ons')
        ->assertDontSee('name="included_add_on_ids[]"', false)
        ->assertSee('Available add-ons')
        ->assertSee('aria-label="View description for Full Groom"', false)
        ->assertSee('min-w-[800px] table-fixed', false)
        ->assertSee('Scented Soap')
        ->assertSee('₱75.00')
        ->assertDontSee('Main services');

    $this->actingAs($manager)
        ->get(route('grooming-addons.edit', $addon))
        ->assertOk()
        ->assertSee('Additional price')
        ->assertDontSee('name="service_ids[]"', false);

    $request = [
        'customer_name' => 'Jamie Santos',
        'customer_phone' => '09123456789',
        'customer_address' => 'Davao City',
        'pet_name' => 'Bella',
        'species' => 'Dog',
        'weight_category' => 'Medium',
        'waiver_confirmed' => '1',
        'service_id' => $service->id,
    ];

    $this->actingAs($manager)
        ->get(route('grooming.create'))
        ->assertOk()
        ->assertSee('Scented Soap');

    $response = $this->actingAs($manager)
        ->post(route('grooming.store'), $request + ['add_on_ids' => [$addon->id]]);
    $appointment = GroomingAppointment::firstOrFail();

    $response->assertRedirect(route('grooming.payment', $appointment));
    expect((float) $appointment->grooming_price)->toBe(325.0)
        ->and($appointment->add_on_ids)->toBe([$addon->id]);

    $this->actingAs($manager)
        ->get(route('grooming.payment', $appointment))
        ->assertOk()
        ->assertSee('₱250.00')
        ->assertSee('Scented Soap × 1')
        ->assertSee('₱325.00');

    $checkoutResponse = $this->actingAs($manager)
        ->post(route('pos.checkout'), [
            'customer_id' => $appointment->customer_id,
            'grooming_appointment_id' => $appointment->id,
            'payment_method' => 'cash',
            'amount_paid' => 325,
        ]);

    $sale = Sale::latest('id')->firstOrFail();
    $checkoutResponse->assertRedirect(route('receipts.done', $sale));

    expect(Product::query()->count())->toBe(0)
        ->and(SaleItem::query()->where('item_type', 'product')->count())->toBe(0)
        ->and(SaleItem::query()->where('item_name', 'Grooming - Bella')->value('line_total'))->toBe('325.00');
});

it('opens grooming add-on editing in a modal and saves the updated add-on fields', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $addon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'description' => 'Basic nail care',
        'rate' => 50,
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->get(route('services.index'))
        ->assertOk()
        ->assertSee('aria-labelledby="edit-grooming-addon-title"', false)
        ->assertSee('openAddonEditor(', false)
        ->assertSee('edit_grooming_addon_name', false);

    $this->actingAs($manager)
        ->put(route('grooming-addons.update', $addon), [
            '_form' => 'grooming-addon-edit',
            'grooming_addon_id' => $addon->id,
            'name' => 'Nail and Paw Care',
            'category' => 'detangling',
            'description' => 'Gentle detangling treatment.',
            'rate' => 125,
            'active' => 1,
        ])
        ->assertRedirect(route('services.index'));

    expect($addon->fresh()->name)->toBe('Nail and Paw Care')
        ->and($addon->fresh()->category)->toBe('detangling')
        ->and($addon->fresh()->description)->toBe('Gentle detangling treatment.')
        ->and((float) $addon->fresh()->rate)->toBe(125.0)
        ->and($addon->fresh()->active)->toBeTrue();
});

it('opens service editing from the catalog and saves the updated service fields', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $addon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'rate' => 50,
        'active' => true,
    ]);
    $availableAddon = GroomingAddon::create([
        'name' => 'Ear Cleaning',
        'category' => 'extra_service',
        'rate' => 75,
        'active' => true,
    ]);
    $service = Service::create([
        'name' => 'Bath Package',
        'description' => 'Basic bath',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 300,
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->get(route('services.index'))
        ->assertOk()
        ->assertSee('aria-labelledby="edit-service-title"', false)
        ->assertSee('edit_service_name', false)
        ->assertSee('edit_service_species', false)
        ->assertSee('edit_service_rate', false)
        ->assertSee('edit_service_size', false)
        ->assertSee('name="catalog_addons"', false)
        ->assertSee('Nail Trim')
        ->assertSee('aria-label="Edit Bath Package"', false)
        ->assertDontSee('href="' . route('services.edit', $service) . '"', false);

    $this->put(route('services.update', $service), [
        '_form' => 'service-edit',
        'service_id' => $service->id,
        'name' => 'Bath and Brush',
        'description' => 'Bath with nail trim',
        'species' => 'Dog',
        'size' => 'Medium',
        'rate' => 450,
        'available_add_on_ids' => [$availableAddon->id],
        'catalog_search' => 'Bath',
        'catalog_species' => 'Dog',
        'catalog_addons' => 'without',
        'active' => 1,
    ])->assertRedirect(route('services.index', [
        'search' => 'Bath',
        'species' => 'Dog',
        'addons' => 'without',
    ]));

    expect($service->fresh()->name)->toBe('Bath and Brush')
        ->and($service->fresh()->size)->toBe('Medium')
        ->and((float) $service->fresh()->rate)->toBe(450.0)
        ->and($service->fresh()->available_add_on_ids)->toBe([$availableAddon->id]);
});

    it('enforces available add-ons and prices a selected detangling tier server-side', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $manager = User::factory()->create(['role' => 'manager']);
    $unavailableAddon = GroomingAddon::create([
        'name' => 'Nail Cut',
        'category' => 'extra_service',
        'rate' => 100,
        'active' => true,
    ]);
    $extraService = GroomingAddon::create([
        'name' => 'Toothbrush',
        'category' => 'extra_service',
        'rate' => 100,
        'active' => true,
    ]);
    $detangling = GroomingAddon::create([
        'name' => 'Extra Mild Detangling',
        'category' => 'detangling',
        'rate' => 150,
        'active' => true,
    ]);
    $service = Service::create([
        'name' => 'Full Groom',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 350,
        'available_add_on_ids' => [$extraService->id, $detangling->id],
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('services.store'), [
            'name' => 'Cat Full Groom',
            'species' => 'Cat',
            'size' => 'Any',
            'rate' => 550,
            'available_add_on_ids' => [$unavailableAddon->id],
        ])
        ->assertRedirect(route('services.index'));

    $catPackage = Service::where('name', 'Cat Full Groom')->firstOrFail();
    expect($catPackage->species)->toBe('Cat')
        ->and($catPackage->size)->toBe('Any')
        ->and($catPackage->available_add_on_ids)->toBe([$unavailableAddon->id]);

    $request = [
        'customer_name' => 'Jordan Owner',
        'customer_phone' => '09112223344',
        'customer_address' => 'Davao City',
        'pet_name' => 'Coco',
        'species' => 'Dog',
        'weight_category' => 'Small',
        'waiver_confirmed' => '1',
        'service_id' => $service->id,
    ];

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $request + ['add_on_ids' => [$unavailableAddon->id]])
        ->assertSessionHasErrors('add_on_ids');

    expect(GroomingAppointment::count())->toBe(0);

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $request + ['add_on_ids' => [$detangling->id]])
        ->assertSessionHasErrors('add_on_ids');

    expect(GroomingAppointment::count())->toBe(0);

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $request + [
            'add_on_ids' => [$extraService->id],
            'detangling_addon_id' => $detangling->id,
        ])
        ->assertRedirect();

    $appointment = GroomingAppointment::firstOrFail();
    expect((float) $appointment->grooming_price)->toBe(600.0)
        ->and($appointment->add_on_ids)->toBe([$extraService->id, $detangling->id]);
});

it('limits selectable add-ons to each pet service and prices them independently', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $mainAddon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'rate' => 50,
        'active' => true,
    ]);
    $additionalAddon = GroomingAddon::create([
        'name' => 'Ear Cleaning',
        'category' => 'extra_service',
        'rate' => 75,
        'active' => true,
    ]);
    $mainService = Service::create([
        'name' => 'Basic Wash',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 300,
        'available_add_on_ids' => [$mainAddon->id],
        'active' => true,
    ]);
    $additionalService = Service::create([
        'name' => 'Full Groom',
        'species' => 'Dog',
        'size' => 'Medium',
        'rate' => 400,
        'available_add_on_ids' => [$additionalAddon->id],
        'active' => true,
    ]);
    $request = [
        'customer_name' => 'Jamie Santos',
        'customer_phone' => '09123456789',
        'customer_address' => 'Davao City',
        'pet_name' => 'Bella',
        'species' => 'Dog',
        'weight_category' => 'Small',
        'waiver_confirmed' => '1',
        'service_id' => $mainService->id,
        'additional_pets' => [[
            'name' => 'Milo',
            'species' => 'Dog',
            'weight_category' => 'Medium',
            'service_id' => $additionalService->id,
            'add_on_ids' => [$additionalAddon->id],
        ]],
        'payment_action' => 'pay_later',
    ];

    $this->actingAs($frontDesk)
        ->from(route('grooming.create'))
        ->post(route('grooming.store'), $request + ['add_on_ids' => [$additionalAddon->id]])
        ->assertSessionHasErrors('add_on_ids');

    expect(GroomingAppointment::count())->toBe(0);

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $request + ['add_on_ids' => [$mainAddon->id]])
        ->assertRedirect(route('grooming.index'));

    $appointments = GroomingAppointment::orderBy('id')->get();

    expect($appointments)->toHaveCount(2)
        ->and($appointments->pluck('add_on_ids')->all())->toBe([[$mainAddon->id], [$additionalAddon->id]])
        ->and($appointments->pluck('grooming_price')->map(fn ($price) => (float) $price)->all())->toBe([350.0, 475.0]);
});

it('filters services by search text, pet type, and available add-on status', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $addon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'rate' => 50,
        'active' => true,
    ]);
    Service::create([
        'name' => 'Basic Wash',
        'description' => 'A simple bath service',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 300,
        'available_add_on_ids' => [],
        'active' => true,
    ]);
    Service::create([
        'name' => 'Deluxe Wash',
        'description' => 'Bath and brush service',
        'species' => 'Dog',
        'size' => 'Medium',
        'rate' => 450,
        'available_add_on_ids' => [$addon->id],
        'active' => true,
    ]);
    Service::create([
        'name' => 'Quick Wash',
        'description' => 'Quick dog wash',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 250,
        'available_add_on_ids' => [],
        'active' => true,
    ]);
    Service::create([
        'name' => 'Cat Full Groom',
        'description' => 'Full grooming for cats',
        'species' => 'Cat',
        'size' => 'Any',
        'rate' => 550,
        'available_add_on_ids' => [],
        'active' => true,
    ]);

    $this->actingAs($manager)
        ->get(route('services.index', ['search' => 'Basic Wash', 'species' => 'Dog', 'addons' => 'without']))
        ->assertOk()
        ->assertSee('Basic Wash')
        ->assertSee('list="service_name_suggestions"', false)
        ->assertSee('<option value="Basic Wash"></option>', false)
        ->assertSee('value="Basic Wash"', false)
        ->assertSee('id="service_species_filter" name="species" onchange="this.form.requestSubmit()"', false)
        ->assertSee('id="service_addons_filter" name="addons" onchange="this.form.requestSubmit()"', false)
        ->assertDontSee('>Clear</a>', false)
        ->assertSee('1–1 of 1')
        ->assertSee('value="without" selected', false);

    $this->actingAs($manager)
        ->get(route('services.index', ['species' => 'Cat', 'addons' => 'without']))
        ->assertOk()
        ->assertSee('Cat Full Groom')
        ->assertSee('1–1 of 1');

    $this->actingAs($manager)
        ->get(route('services.index', ['search' => 'Quick Wash', 'species' => 'Dog', 'addons' => 'without']))
        ->assertOk()
        ->assertSee('Quick Wash')
        ->assertSee('<option value="Quick Wash"></option>', false)
        ->assertSee('1–1 of 1');

    $this->actingAs($manager)
        ->get(route('services.index', ['search' => 'Del']))
        ->assertOk()
        ->assertSee('Deluxe Wash')
        ->assertSee('1–1 of 1');
});
