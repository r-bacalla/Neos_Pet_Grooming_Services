<?php

use App\Models\Customer;
use App\Models\AuditLog;
use App\Models\GroomingAppointment;
use App\Models\Service;
use App\Models\User;

it('saves additional pet profiles to the grooming customer without changing the visit pet', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Jamie Santos',
        'phone' => '09123456789',
        'email' => 'jamie@example.com',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $visitPet = $customer->pets()->create([
        'name' => 'Bella',
        'species' => 'Dog',
        'breed' => 'Golden Retriever',
        'sex' => 'Female',
    ]);
    $savedPet = $customer->pets()->create([
        'name' => 'Milo',
        'species' => 'Cat',
        'breed' => 'Domestic Shorthair',
        'sex' => 'Male',
        'weight_category' => 'Small',
    ]);
    $service = Service::create([
        'name' => 'Full Groom',
        'species' => 'Dog',
        'size' => 'Large',
        'rate' => 550,
        'active' => true,
    ]);
    $smallDogService = Service::create([
        'name' => 'Full Groom',
        'species' => 'Dog',
        'size' => 'Small',
        'rate' => 350,
        'active' => true,
    ]);
    $catService = Service::create([
        'name' => 'Cat Full Groom',
        'species' => 'Cat',
        'size' => 'Any',
        'rate' => 550,
        'active' => true,
    ]);

    $response = $this->actingAs($frontDesk)->post(route('grooming.store'), [
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_phone' => $customer->phone,
        'customer_email' => $customer->email,
        'customer_address' => $customer->address,
        'pet_id' => $visitPet->id,
        'pet_name' => $visitPet->name,
        'species' => $visitPet->species,
        'weight_category' => 'Large',
        'breed' => $visitPet->breed,
        'sex' => $visitPet->sex,
        'additional_pets' => [[
            'name' => 'Luna',
            'species' => 'Dog',
            'weight_category' => 'Small',
            'service_id' => $smallDogService->id,
            'breed' => 'Poodle',
            'sex' => 'Female',
        ], [
            'pet_id' => $savedPet->id,
            'name' => 'Milo',
            'species' => 'Cat',
            'weight_category' => 'Medium',
            'service_id' => $catService->id,
            'breed' => 'Domestic Shorthair',
            'sex' => 'Male',
        ]],
        'waiver_confirmed' => '1',
        'groomer_name' => 'Taylor Freeform',
        'service_id' => $service->id,
    ]);

    $appointment = GroomingAppointment::firstOrFail();
    $response->assertRedirect(route('grooming.payment', $appointment));

    $this->actingAs($frontDesk)
        ->get(route('grooming.payment', $appointment))
        ->assertOk()
        ->assertSee('Grooming Payment')
        ->assertSee('Bella')
        ->assertSee('Luna')
        ->assertSee('Milo')
        ->assertSee('₱1,450.00')
        ->assertDontSee('VAT (12%)');

    expect($appointment->customer_id)->toBe($customer->id)
        ->and($appointment->pet_id)->toBe($visitPet->id)
        ->and($appointment->status)->toBe('pending')
        ->and($visitPet->fresh()->weight_category)->toBeNull()
        ->and($appointment->pet_size)->toBe('Large')
        ->and($appointment->groomer_name)->toBe('Taylor Freeform')
        ->and($appointment->waiver_signature)->toBeNull()
        ->and($customer->pets()->count())->toBe(3)
        ->and($customer->pets()->where('name', 'Milo')->value('species'))->toBe('Cat')
        ->and($customer->pets()->where('name', 'Milo')->value('weight_category'))->toBe('Small')
        ->and($customer->pets()->where('name', 'Luna')->value('weight_category'))->toBe('Small')
        ->and(GroomingAppointment::where('visit_group_id', $appointment->visit_group_id)->count())->toBe(3)
        ->and(GroomingAppointment::where('pet_id', $savedPet->id)->value('pet_size'))->toBeNull()
        ->and(GroomingAppointment::where('visit_group_id', $appointment->visit_group_id)->orderBy('id')->pluck('grooming_price')->map(fn ($price) => (float) $price)->all())->toBe([550.0, 350.0, 550.0])
        ->and(GroomingAppointment::where('visit_group_id', $appointment->visit_group_id)->pluck('groomer_name')->unique()->all())->toBe(['Taylor Freeform'])
        ->and(AuditLog::where('action', 'grooming_requested')->where('module', 'Grooming')->where('record_id', $appointment->id)->exists())->toBeTrue()
        ->and(GroomingAppointment::where('visit_group_id', $appointment->visit_group_id)->whereNotNull('waiver_signature')->exists())->toBeFalse();
});

it('updates a saved pet size only after explicit confirmation', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Jamie Santos',
        'phone' => '09123456789',
        'email' => 'jamie@example.com',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $pet = $customer->pets()->create([
        'name' => 'Bella',
        'species' => 'Dog',
        'weight_category' => 'Small',
    ]);
    $service = Service::create([
        'name' => 'Full Groom',
        'species' => 'Dog',
        'size' => 'Large',
        'rate' => 550,
        'active' => true,
    ]);

    $this->actingAs($frontDesk)->post(route('grooming.store'), [
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_phone' => $customer->phone,
        'customer_email' => $customer->email,
        'customer_address' => $customer->address,
        'pet_id' => $pet->id,
        'pet_name' => $pet->name,
        'species' => $pet->species,
        'weight_category' => 'Large',
        'update_saved_profile_size' => '1',
        'waiver_confirmed' => '1',
        'service_id' => $service->id,
        'payment_action' => 'pay_later',
    ])->assertRedirect(route('grooming.index'));

    $appointment = GroomingAppointment::firstOrFail();

    expect($pet->fresh()->weight_category)->toBe('Large')
        ->and($appointment->pet_size)->toBe('Large');
});

it('requires and saves Other pet profile sizes while using the Any grooming price', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $service = Service::create([
        'name' => 'Other Pet Groom',
        'species' => 'Other',
        'size' => 'Any',
        'rate' => 400,
        'active' => true,
        'available_add_on_ids' => [],
    ]);
    $payload = [
        'customer_name' => 'Jamie Santos',
        'customer_phone' => '09123456789',
        'customer_address' => 'Davao City',
        'pet_name' => 'Nori',
        'species' => 'Other',
        'waiver_confirmed' => '1',
        'service_id' => $service->id,
        'additional_pets' => [[
            'name' => 'Pip',
            'species' => 'Other',
            'service_id' => $service->id,
        ]],
        'payment_action' => 'pay_later',
    ];

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $payload)
        ->assertSessionHasErrors(['profile_size', 'additional_pets.0.profile_size']);

    $payload['profile_size'] = 'Medium';
    $payload['additional_pets'][0]['profile_size'] = 'Large';

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), $payload)
        ->assertRedirect(route('grooming.index'));

    $appointments = GroomingAppointment::orderBy('id')->get();

    expect($appointments)->toHaveCount(2)
        ->and($appointments[0]->pet->weight_category)->toBe('Medium')
        ->and($appointments[1]->pet->weight_category)->toBe('Large')
        ->and($appointments[0]->pet_size)->toBeNull()
        ->and($appointments[1]->pet_size)->toBeNull()
        ->and($appointments->pluck('service_id')->unique()->all())->toBe([$service->id])
        ->and($appointments->pluck('grooming_price')->map(fn ($price) => (float) $price)->all())->toBe([400.0, 400.0]);
});

it('accepts a cat grooming visit without a pet size', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $service = Service::create([
        'name' => 'Cat Full Groom',
        'species' => 'Cat',
        'size' => 'Any',
        'rate' => 550,
        'active' => true,
        'available_add_on_ids' => [],
    ]);

    $this->actingAs($frontDesk)->post(route('grooming.store'), [
        'customer_name' => 'Jamie Santos',
        'customer_phone' => '09123456789',
        'customer_address' => 'Davao City',
        'pet_name' => 'Milo',
        'species' => 'Cat',
        'waiver_confirmed' => '1',
        'service_id' => $service->id,
        'additional_pets' => [[
            'name' => 'Luna',
            'species' => 'Cat',
            'service_id' => $service->id,
        ]],
        'payment_action' => 'pay_later',
    ])->assertRedirect(route('grooming.index'));

    $appointment = GroomingAppointment::firstOrFail();
    $additionalAppointment = GroomingAppointment::where('pet_id', '!=', $appointment->pet_id)->firstOrFail();

    expect($appointment->pet_size)->toBeNull()
        ->and($appointment->pet->weight_category)->toBeNull()
        ->and((float) $appointment->grooming_price)->toBe(550.0)
        ->and($additionalAppointment->pet_size)->toBeNull()
        ->and($additionalAppointment->pet->weight_category)->toBeNull();
});

it('saves a pay-later visit to the grooming queue with an accept action while pending', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $service = Service::create([
        'name' => 'Bath and Brush',
        'rate' => 300,
        'active' => true,
    ]);

    $this->actingAs($frontDesk)
        ->post(route('grooming.store'), [
            'customer_name' => 'Jamie Santos',
            'customer_phone' => '09123456789',
            'customer_address' => 'Davao City',
            'pet_name' => 'Bella',
            'species' => 'Dog',
            'weight_category' => 'Medium',
            'waiver_confirmed' => '1',
            'service_id' => $service->id,
            'payment_action' => 'pay_later',
        ])
        ->assertRedirect(route('grooming.index'));

    $appointment = GroomingAppointment::firstOrFail();

    expect($appointment->status)->toBe('pending')
        ->and($appointment->sale_id)->toBeNull();

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Accept')
        ->assertDontSee('auto_payment=1')
        ->assertDontSee('Receipt');
});
