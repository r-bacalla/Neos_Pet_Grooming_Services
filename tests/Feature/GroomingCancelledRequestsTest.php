<?php

use App\Models\Customer;
use App\Models\GroomingAppointment;
use App\Models\GroomingVisitGroup;
use App\Models\Sale;
use App\Models\Service;
use App\Models\User;

it('hides cancelled grooming requests without deleting their records', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $customer = Customer::create([
        'name' => 'Taylor Owner',
        'phone' => '09112223333',
        'address' => 'Davao City',
        'loyalty_visits' => 0,
    ]);
    $pet = $customer->pets()->create([
        'name' => 'Cancelled Pet',
        'species' => 'Dog',
        'weight_category' => 'Small',
    ]);
    $service = Service::create([
        'name' => 'Basic Groom',
        'rate' => 300,
        'active' => true,
    ]);
    $visitGroup = GroomingVisitGroup::create(['customer_id' => $customer->id]);
    $appointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'visit_group_id' => $visitGroup->id,
        'pet_id' => $pet->id,
        'service_id' => $service->id,
        'sale_id' => null,
        'scheduled_start' => now(),
        'scheduled_end' => null,
        'status' => 'pending',
    ]);
    $otherPet = $customer->pets()->create([
        'name' => 'Also Cancelled Pet',
        'species' => 'Cat',
    ]);
    $otherAppointment = GroomingAppointment::create([
        'customer_id' => $customer->id,
        'visit_group_id' => $visitGroup->id,
        'pet_id' => $otherPet->id,
        'service_id' => $service->id,
        'scheduled_start' => now(),
        'scheduled_end' => null,
        'status' => 'pending',
    ]);

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Accept')
        ->assertDontSee('text-red-500 transition hover:bg-red-50 hover:text-red-700', false)
        ->assertDontSee('aria-label="Remove grooming visit from queue"', false);

    $this->actingAs($frontDesk)
        ->post(route('grooming.cancel', $appointment))
        ->assertRedirect()
        ->assertSessionHas('error', 'Pending or in-progress grooming visits cannot be cancelled.');

    expect($appointment->fresh()->status)->toBe('pending')
        ->and($otherAppointment->fresh()->status)->toBe('pending');

    $appointment->update(['status' => 'in_progress']);
    $otherAppointment->update(['status' => 'in_progress']);

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Ready')
        ->assertDontSee('text-red-500 transition hover:bg-red-50 hover:text-red-700', false);

    $this->actingAs($frontDesk)
        ->post(route('grooming.cancel', $appointment))
        ->assertRedirect()
        ->assertSessionHas('error', 'Pending or in-progress grooming visits cannot be cancelled.');

    expect($appointment->fresh()->status)->toBe('in_progress')
        ->and($otherAppointment->fresh()->status)->toBe('in_progress');

    $appointment->update(['status' => 'approved']);
    $otherAppointment->update(['status' => 'approved']);

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertSee('Ready')
        ->assertSee('Cancel')
        ->assertSee('text-red-500 transition hover:bg-red-50 hover:text-red-700', false);

    $this->actingAs($frontDesk)
        ->post(route('grooming.cancel', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe('cancelled')
        ->and($otherAppointment->fresh()->status)->toBe('cancelled');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index'))
        ->assertOk()
        ->assertDontSee('Cancelled Pet')
        ->assertDontSee('Also Cancelled Pet');

    expect(GroomingAppointment::whereKey($appointment->id)->exists())->toBeTrue();
});

it('searches queue customer and pet names and filters appointment status and unpaid visits', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);
    $service = Service::create(['name' => 'Queue Groom', 'rate' => 300, 'active' => true]);
    $customer = Customer::create(['name' => 'Queue Search Customer', 'phone' => '09112223333', 'address' => 'Davao City']);
    $otherCustomer = Customer::create(['name' => 'Other Queue Customer', 'phone' => '09223334444', 'address' => 'Davao City']);
    $paidSale = Sale::create([
        'invoice_number' => 'QUEUE-PAID-001',
        'customer_id' => $customer->id,
        'user_id' => $frontDesk->id,
        'subtotal' => 300,
        'total' => 300,
        'amount_paid' => 300,
        'change_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    $createAppointment = function (Customer $owner, string $petName, string $status, ?int $saleId = null) use ($service) {
        $pet = $owner->pets()->create(['name' => $petName, 'species' => 'Dog', 'weight_category' => 'Small']);

        return GroomingAppointment::create([
            'customer_id' => $owner->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'sale_id' => $saleId,
            'scheduled_start' => now(),
            'scheduled_end' => null,
            'status' => $status,
        ]);
    };

    $pending = $createAppointment($customer, 'Pending Pet', 'pending');
    $inProgress = $createAppointment($customer, 'Running Pet', 'in_progress');
    $paidCompleted = $createAppointment($customer, 'Paid Pet', 'completed', $paidSale->id);
    $unpaidCompleted = $createAppointment($otherCustomer, 'Unpaid Pet', 'completed');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['search' => 'Queue Search Customer']))
        ->assertOk()
        ->assertSee('Pending Pet')
        ->assertSee('Running Pet')
        ->assertDontSee('Unpaid Pet');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['search' => 'Unpaid Pet']))
        ->assertOk()
        ->assertSee('Unpaid Pet')
        ->assertDontSee('Pending Pet');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['status' => 'pending']))
        ->assertOk()
        ->assertSee('Pending Pet')
        ->assertDontSee('Running Pet')
        ->assertDontSee('Paid Pet');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['status' => 'in_progress']))
        ->assertOk()
        ->assertSee('Running Pet')
        ->assertDontSee('Pending Pet');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['status' => 'completed']))
        ->assertOk()
        ->assertSee('Paid Pet')
        ->assertDontSee('Unpaid Pet');

    $this->actingAs($frontDesk)
        ->get(route('grooming.index', ['status' => 'unpaid']))
        ->assertOk()
        ->assertSee('Unpaid Pet')
        ->assertDontSee('Paid Pet');
});