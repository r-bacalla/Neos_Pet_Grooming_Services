<?php

use App\Models\Customer;
use App\Models\GroomingAddon;
use App\Models\GroomingAppointment;
use App\Models\GroomingVisitGroup;
use App\Models\LoyaltyVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\User;

it('checks out all pet appointments in one grooming visit group', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $manager = User::factory()->create(['role' => 'manager']);
    $customer = Customer::create([
        'name' => 'Jamie Santos',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $service = Service::create([
        'name' => 'Full Groom',
        'rate' => 500,
        'active' => true,
    ]);
    $visitGroup = GroomingVisitGroup::create(['customer_id' => $customer->id]);
    $appointments = collect(['Bella', 'Milo'])->map(function ($petName) use ($customer, $service, $visitGroup) {
        $pet = $customer->pets()->create([
            'name' => $petName,
            'species' => 'Dog',
        ]);

        return GroomingAppointment::create([
            'customer_id' => $customer->id,
            'visit_group_id' => $visitGroup->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'scheduled_start' => now(),
            'status' => 'pending',
            'grooming_price' => 500,
        ]);
    });

    $this->actingAs($frontDesk)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'grooming_appointment_id' => $appointments->first()->id,
        'payment_method' => 'cash',
        'amount_paid' => 1000,
    ])->assertRedirect();

    $sale = Sale::latest('id')->firstOrFail();

    expect((float) $sale->subtotal)->toBe(1000.0)
        ->and((float) $sale->total)->toBe(1000.0)
        ->and((float) $sale->vat_rate)->toBe(0.0)
        ->and((float) $sale->vat_amount)->toBe(0.0)
        ->and(SaleItem::where('sale_id', $sale->id)->pluck('item_name')->all())
        ->toBe(['Grooming - Bella', 'Grooming - Milo']);

    foreach ($appointments as $appointment) {
        expect($appointment->fresh()->sale_id)->toBe($sale->id);
    }

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Accept')
        ->assertDontSee('Receipt')
        ->assertDontSee('>Pay</a>', false);

    $this->actingAs($manager)
        ->post(route('grooming.approve', $appointments->first()))
        ->assertRedirect();

    expect($appointments->first()->fresh()->status)->toBe('in_progress');

    $this->actingAs($manager)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Ready')
        ->assertDontSee('Receipt');
});

it('automatically applies an active loyalty voucher to grooming checkout', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Voucher Customer',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 5,
    ]);
    $service = Service::create(['name' => 'Voucher Groom', 'rate' => 500, 'active' => true]);
    $pet = $customer->pets()->create(['name' => 'Voucher Pet', 'species' => 'Dog']);
    $appointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'pet_id' => $pet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'status' => 'ready',
        'grooming_price' => 500,
    ]);
    $voucher = LoyaltyVoucher::create([
        'customer_id' => $customer->id,
        'code' => 'AUTO-GROOM-20',
        'discount_percent' => 20,
        'required_visits' => 5,
        'issued_date' => today(),
        'expires_at' => today()->addMonth(),
        'status' => 'active',
    ]);

    $this->actingAs($frontDesk)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'grooming_appointment_id' => $appointment->id,
        'payment_method' => 'cash',
        'amount_paid' => 400,
    ])->assertRedirect();

    $sale = Sale::latest('id')->firstOrFail();
    expect((float) $sale->discount)->toBe(100.0)
        ->and((float) $sale->total)->toBe(400.0)
        ->and($voucher->fresh()->status)->toBe('used')
        ->and($voucher->fresh()->used_sale_id)->toBe($sale->id);
});

it('starts a new loyalty cycle after the 10-visit voucher is redeemed', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Loyalty Cycle Customer',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 10,
    ]);
    $service = Service::create(['name' => 'Cycle Groom', 'rate' => 500, 'active' => true]);
    $pet = $customer->pets()->create(['name' => 'Cycle Pet', 'species' => 'Dog']);
    $appointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'pet_id' => $pet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'status' => 'ready',
        'grooming_price' => 500,
    ]);
    $voucher = LoyaltyVoucher::create([
        'customer_id' => $customer->id,
        'code' => 'AUTO-CYCLE-100',
        'discount_percent' => 100,
        'required_visits' => 10,
        'issued_date' => today(),
        'expires_at' => today()->addMonth(),
        'status' => 'active',
    ]);

    $this->actingAs($frontDesk)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'grooming_appointment_id' => $appointment->id,
        'payment_method' => 'cash',
        'amount_paid' => 0,
    ])->assertRedirect();

    expect($voucher->fresh()->status)->toBe('used')
        ->and($customer->fresh()->loyalty_visits)->toBe(1);
});

it('lets front desk accept and ready a grooming request through payment and receipt', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Morgan Lee',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $pet = $customer->pets()->create([
        'name' => 'Coco',
        'species' => 'Dog',
        'weight' => 8,
        'weight_category' => 'Medium',
    ]);
    $service = Service::create([
        'name' => 'Bath and Brush',
        'rate' => 300,
        'active' => true,
    ]);
    $appointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'pet_id' => $pet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'status' => 'pending',
        'grooming_price' => 300,
    ]);

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSeeInOrder(['Request', 'Morgan Lee', 'Coco', 'Medium', '₱300.00', 'Pending'])
        ->assertSee('Accept');

    $this->actingAs($frontDesk)
        ->post(route('grooming.approve', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe('in_progress')
        ->and($appointment->fresh()->started_at)->not->toBeNull();

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('In Progress')
        ->assertSee('Ready');

    $this->actingAs($frontDesk)
        ->post(route('grooming.ready', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe('completed')
        ->and($appointment->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Completed')
        ->assertSee('Unpaid')
        ->assertSee('Unpaid, collect payment')
        ->assertSee(route('grooming.payment', $appointment), false)
        ->assertSee('bg-emerald-50 text-emerald-700 border-emerald-200');

    $checkoutResponse = $this->actingAs($frontDesk)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'grooming_appointment_id' => $appointment->id,
        'payment_method' => 'cash',
        'amount_paid' => 300,
    ]);

    $sale = Sale::latest('id')->firstOrFail();
    $checkoutResponse->assertRedirect(route('receipts.done', $sale));
    expect($appointment->fresh()->status)->toBe('completed')
        ->and($appointment->fresh()->sale_id)->toBe($sale->id);

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee(route('receipts.show', $sale->id), false)
        ->assertSee('Receipt');

    $this->actingAs($frontDesk)
        ->get(route('receipts.done', $sale))
        ->assertOk()
        ->assertSee('Payment Done')
        ->assertSee(route('receipts.show', $sale), false)
        ->assertDontSee(route('receipts.print', $sale), false)
        ->assertDontSee('Print Receipt');

    $this->actingAs($frontDesk)
        ->get(route('pos.receipt', $sale))
        ->assertOk()
        ->assertSee('href="' . route('grooming.index') . '"', false)
        ->assertSee('Back to Grooming')
        ->assertSee(route('receipts.print', $sale), false);

    $this->actingAs($frontDesk)
        ->get(route('receipts.print', $sale))
        ->assertOk()
        ->assertSee('window.print()', false);
});

it('allows legacy approved and received appointments to use the queue ready action', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $customer = Customer::create([
        'name' => 'Riley Cruz',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $service = Service::create([
        'name' => 'Basic Groom',
        'rate' => 250,
        'active' => true,
    ]);

    foreach (['approved', 'received'] as $legacyStatus) {
        $pet = $customer->pets()->create([
            'name' => ucfirst($legacyStatus) . ' Pet',
            'species' => 'Dog',
        ]);
        $appointment = GroomingAppointment::create([
            'customer_id' => $customer->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'scheduled_start' => now(),
            'status' => $legacyStatus,
            'grooming_price' => 250,
        ]);

        $this->actingAs($manager)
            ->post(route('grooming.ready', $appointment))
            ->assertRedirect();

        expect($appointment->fresh()->status)->toBe('completed');
    }
});

it('lets an admin accept a pending grooming request from the queue', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = Customer::create([
        'name' => 'Avery Santos',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $pet = $customer->pets()->create([
        'name' => 'Pepper',
        'species' => 'Cat',
    ]);
    $service = Service::create([
        'name' => 'Cat Groom',
        'rate' => 350,
        'active' => true,
    ]);
    $appointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'pet_id' => $pet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'status' => 'pending',
        'grooming_price' => 350,
    ]);

    $this->actingAs($admin)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Accept');

    $this->actingAs($admin)
        ->post(route('grooming.approve', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe('in_progress');
});

it('adds selected extras to the outstanding balance for an unpaid visit group', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Unpaid Add-on Customer',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $service = Service::create(['name' => 'Group Groom', 'rate' => 500, 'active' => true]);
    $addon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'rate' => 100,
        'active' => true,
    ]);
    $visitGroup = GroomingVisitGroup::create(['customer_id' => $customer->id]);
    $appointments = collect(['Milo', 'Luna'])->map(function ($name) use ($customer, $service, $visitGroup) {
        $pet = $customer->pets()->create(['name' => $name, 'species' => 'Dog']);

        return GroomingAppointment::create([
            'customer_id' => $customer->id,
            'visit_group_id' => $visitGroup->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'scheduled_start' => now(),
            'status' => 'ready',
            'grooming_price' => 500,
        ]);
    });

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Edit')
        ->assertSee('h-9 w-24 shrink-0');

    $this->put(route('grooming.addons.update', $appointments->first()), [
        '_form' => 'grooming-addons',
        'appointment_id' => $appointments->first()->id,
        'add_on_ids' => [$addon->id],
    ])->assertRedirect(route('grooming.index'));

    foreach ($appointments as $appointment) {
        expect((float) $appointment->fresh()->grooming_price)->toBe(600.0)
            ->and($appointment->fresh()->add_on_ids)->toBe([$addon->id]);
    }

    expect(Sale::count())->toBe(0);

    $this->get(route('grooming.payment', $appointments->first()))
        ->assertOk()
        ->assertSee('₱1,200.00');
});

it('collects a separate receipt when add-ons are added after the visit was paid', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Paid Add-on Customer',
        'phone' => '09123456789',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $service = Service::create(['name' => 'Group Groom', 'rate' => 500, 'active' => true]);
    $previousAddon = GroomingAddon::create([
        'name' => 'Nail Trim',
        'category' => 'extra_service',
        'rate' => 100,
        'active' => true,
    ]);
    $newAddon = GroomingAddon::create([
        'name' => 'Toothbrush',
        'category' => 'extra_service',
        'rate' => 50,
        'active' => true,
    ]);
    $visitGroup = GroomingVisitGroup::create(['customer_id' => $customer->id]);
    $appointments = collect(['Milo', 'Luna'])->map(function ($name) use ($customer, $service, $visitGroup, $previousAddon) {
        $pet = $customer->pets()->create(['name' => $name, 'species' => 'Dog']);

        return GroomingAppointment::create([
            'customer_id' => $customer->id,
            'visit_group_id' => $visitGroup->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'scheduled_start' => now(),
            'status' => 'ready',
            'add_on_ids' => [$previousAddon->id],
            'grooming_price' => 600,
        ]);
    });

    $this->actingAs($frontDesk)->post(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'grooming_appointment_id' => $appointments->first()->id,
        'payment_method' => 'cash',
        'amount_paid' => 1200,
    ])->assertRedirect();

    $originalSale = Sale::latest('id')->firstOrFail();
    $this->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('>Receipt</a>', false)
        ->assertDontSee('aria-label="Edit add-ons for Milo"', false);

    $this->put(route('grooming.addons.update', $appointments->first()), [
        '_form' => 'grooming-addons',
        'appointment_id' => $appointments->first()->id,
        'add_on_ids' => [$newAddon->id],
        'payment_method' => 'gcash',
        'amount_paid' => 200,
    ])->assertSessionHasErrors('add_on_ids');

    expect(Sale::count())->toBe(1)
        ->and($appointments->first()->fresh()->add_on_ids)->toBe([$previousAddon->id]);

    $this->put(route('grooming.addons.update', $appointments->first()), [
        '_form' => 'grooming-addons',
        'appointment_id' => $appointments->first()->id,
        'add_on_ids' => [$previousAddon->id, $newAddon->id],
        'payment_method' => 'gcash',
        'amount_paid' => 99,
    ])->assertSessionHasErrors('amount_paid');

    $response = $this->put(route('grooming.addons.update', $appointments->first()), [
        '_form' => 'grooming-addons',
        'appointment_id' => $appointments->first()->id,
        'add_on_ids' => [$previousAddon->id, $newAddon->id],
        'payment_method' => 'gcash',
        'amount_paid' => 110,
    ]);

    $addonSale = Sale::latest('id')->firstOrFail();
    $response->assertRedirect(route('receipts.done', $addonSale));

    expect($addonSale->id)->not->toBe($originalSale->id)
        ->and((float) $addonSale->total)->toBe(100.0)
        ->and((float) $addonSale->change_amount)->toBe(10.0)
        ->and(SaleItem::where('sale_id', $addonSale->id)->pluck('item_name')->all())
        ->toBe(['Grooming add-on - Toothbrush (Milo)', 'Grooming add-on - Toothbrush (Luna)']);

    foreach ($appointments as $appointment) {
        expect($appointment->fresh()->sale_id)->toBe($originalSale->id)
            ->and($appointment->fresh()->add_on_ids)->toBe([$previousAddon->id, $newAddon->id])
            ->and((float) $appointment->fresh()->grooming_price)->toBe(650.0);
    }

    $this->get(route('receipts.show', $addonSale))
        ->assertOk()
        ->assertSee('Grooming add-on - Toothbrush (Milo)')
        ->assertSee('Grooming add-on - Toothbrush (Luna)');
});