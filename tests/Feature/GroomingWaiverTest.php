<?php

use App\Models\User;

it('renders the supplied grooming waiver with consent verification and no typed signature', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->actingAs($frontDesk)
        ->get(route('grooming.create'))
        ->assertOk()
        ->assertDontSee('Manage Accounts')
        ->assertDontSee('max-h-[52vh]', false)
        ->assertDontSee('max-h-[46vh]', false)
        ->assertSee('space-y-5 rounded-xl border-2 border-slate-200 bg-slate-50 p-5 text-sm leading-7', false)
        ->assertSee('Consent to Groom &amp; General Liability Waiver', false)
        ->assertSee('1. Matted Pets')
        ->assertSee('7. Customer Satisfaction')
        ->assertSee('daycare fee of ₱200 will apply.')
        ->assertSee('By checking the agreement box below')
        ->assertSee('name="waiver_confirmed"', false)
        ->assertSeeText('Services & Add-ons')
        ->assertSeeText('Customer & Pet')
        ->assertSee('serviceVariantsFor(species, serviceName)', false)
        ->assertSee('window.confirm', false)
        ->assertDontSee('name="waiver_signature"', false)
        ->assertSee('x-show="step === 2" @click="next()" :disabled="!waiverConfirmed"', false)
        ->assertSee('name="payment_action" value="pay_later"', false)
        ->assertSee('Pay Later and Continue')
        ->assertSee('name="payment_action" value="pay_now"', false)
        ->assertSee('placeholder="Enter groomer name"', false)
        ->assertSee('name="weight_category"', false)
        ->assertSee('Pet size *')
        ->assertSee('Pet 1', false)
        ->assertDontSee('New Customer')
        ->assertDontSee('Customer & Pet Records')
        ->assertDontSee('groomer_options')
        ->assertDontSee('Choose an available groomer from the suggestions')
        ->assertDontSee('Leave blank for queue');
});

    it('requires agreement checkbox consent but not a typed signature when submitting a grooming request', function () {
    $frontDesk = User::factory()->create(['role' => 'frontdesk']);

    $this->actingAs($frontDesk)
        ->from(route('grooming.create'))
        ->post(route('grooming.store'), [])
        ->assertRedirect(route('grooming.create'))
        ->assertSessionHasErrors(['waiver_confirmed'])
        ->assertSessionDoesntHaveErrors(['waiver_signature']);
});