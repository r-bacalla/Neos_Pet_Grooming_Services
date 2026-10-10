<?php

use App\Models\Customer;
use App\Models\GroomingAppointment;
use App\Models\Service;
use App\Models\User;

it('shows customer accounts only their own saved grooming details and no queue', function () {
    $account = User::factory()->create([
        'role' => 'customer',
        'name' => 'Account Owner',
        'email' => 'account-owner@example.test',
    ]);
    $ownCustomer = Customer::create([
        'name' => 'Account Owner',
        'email' => $account->email,
        'phone' => '09111111111',
        'address' => 'Davao City',
    ]);
    $ownPet = $ownCustomer->pets()->create([
        'name' => 'Account Pet',
        'species' => 'Dog',
        'weight_category' => 'Medium',
    ]);
    $otherCustomer = Customer::create([
        'name' => 'Another Customer',
        'email' => 'another-customer@example.test',
        'phone' => '09222222222',
        'address' => 'Cebu City',
    ]);
    $otherPet = $otherCustomer->pets()->create([
        'name' => 'Another Pet',
        'species' => 'Cat',
        'weight_category' => 'Small',
    ]);
    $service = Service::create(['name' => 'Basic Groom', 'rate' => 250, 'active' => true]);
    $otherAppointment = GroomingAppointment::create([
        'customer_id' => $otherCustomer->id,
        'pet_id' => $otherPet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'status' => 'pending',
        'grooming_price' => 250,
    ]);

    $this->actingAs($account)
        ->get(route('grooming.index'))
        ->assertRedirect(route('grooming.create'));

    $this->actingAs($account)
        ->get(route('grooming.create'))
        ->assertOk()
        ->assertSee('New Grooming Transaction')
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('Back to POS')
        ->assertSee('x-text="`STEP ${step} OF 4`"', false)
        ->assertSee($ownPet->name)
        ->assertDontSee($otherCustomer->name)
        ->assertDontSee($otherPet->name)
        ->assertDontSee("Today's Grooming Queue");

    $this->actingAs($account)
        ->post(route('grooming.approve', $otherAppointment))
        ->assertForbidden();

    $this->actingAs($account)
        ->post(route('grooming.ready', $otherAppointment))
        ->assertForbidden();

    $this->actingAs($account)
        ->get(route('grooming.payment', $otherAppointment))
        ->assertForbidden();

    $this->actingAs($account)
        ->get(route('grooming.waiver-photo', $otherAppointment))
        ->assertForbidden();
});

it('binds a customer grooming request to the signed-in customer account', function () {
    $account = User::factory()->create([
        'role' => 'customer',
        'name' => 'Account Owner',
        'email' => 'account-owner@example.test',
    ]);
    $ownCustomer = Customer::create([
        'name' => 'Old Customer Name',
        'email' => $account->email,
        'phone' => '09111111111',
        'address' => 'Davao City',
    ]);
    $otherCustomer = Customer::create([
        'name' => 'Unrelated Customer',
        'email' => 'unrelated@example.test',
        'phone' => '09222222222',
        'address' => 'Cebu City',
    ]);
    $service = Service::create(['name' => 'Basic Groom', 'rate' => 250, 'active' => true]);

    $this->actingAs($account)
        ->post(route('grooming.store'), [
            'customer_id' => $otherCustomer->id,
            'customer_name' => $otherCustomer->name,
            'customer_phone' => '09333333333',
            'customer_email' => $otherCustomer->email,
            'customer_address' => 'New address',
            'pet_name' => 'New Account Pet',
            'species' => 'Dog',
            'weight_category' => 'Medium',
            'waiver_confirmed' => '1',
            'service_id' => $service->id,
            'payment_action' => 'pay_later',
        ])
        ->assertRedirect(route('grooming.index'));

    $appointment = GroomingAppointment::query()
        ->where('service_id', $service->id)
        ->latest('id')
        ->firstOrFail();

    expect($appointment->customer_id)->toBe($ownCustomer->id)
        ->and($appointment->pet->customer_id)->toBe($ownCustomer->id)
        ->and($ownCustomer->fresh()->name)->toBe($account->name)
        ->and($ownCustomer->fresh()->email)->toBe($account->email)
        ->and($otherCustomer->fresh()->name)->toBe('Unrelated Customer');
});