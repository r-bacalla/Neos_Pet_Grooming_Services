<?php

use App\Models\Customer;
use App\Models\User;

it('requires sizes for dogs and other pets but not cats', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = Customer::create([
        'name' => 'Jamie Santos',
        'phone' => '09123456789',
        'email' => 'jamie@example.com',
        'address' => 'Davao City',
    ]);

    $this->actingAs($admin)
        ->post(route('pets.store', $customer), [
            'name' => 'Bella',
            'species' => 'Dog',
        ])
        ->assertSessionHasErrors('weight_category');

    $this->actingAs($admin)
        ->post(route('pets.store', $customer), [
            'name' => 'Nori',
            'species' => 'Other',
        ])
        ->assertSessionHasErrors('weight_category');

    $this->actingAs($admin)
        ->post(route('pets.store', $customer), [
            'name' => 'Nori',
            'species' => 'Other',
            'weight_category' => 'Large',
        ])
        ->assertRedirect(route('customers.show', $customer));

    $pet = $customer->pets()->where('name', 'Nori')->firstOrFail();

    expect($pet->weight_category)->toBe('Large');

    $this->actingAs($admin)
        ->put(route('pets.update', $pet), [
            'name' => 'Nori',
            'species' => 'Other',
        ])
        ->assertSessionHasErrors('weight_category');

    $this->actingAs($admin)
        ->put(route('pets.update', $pet), [
            'name' => 'Nori',
            'species' => 'Cat',
        ])
        ->assertRedirect(route('customers.show', $customer));

    expect($pet->fresh()->weight_category)->toBeNull();
});