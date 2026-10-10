<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pet;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function create(Customer $customer)
    {
        return view('pets.create', compact('customer'));
    }

    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'species' => ['required', 'string', 'in:Dog,Cat,Other'],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'weight_category' => ['nullable', 'required_if:species,Dog,Other', 'in:Small,Medium,Large,XLarge'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['weight_category'] = $validated['species'] !== 'Cat'
            ? $validated['weight_category']
            : null;

        $customer->pets()->create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Pet added successfully.');
    }

    public function edit(Pet $pet)
    {
        $customer = $pet->customer;

        return view('pets.edit', compact('pet', 'customer'));
    }

    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'species' => ['required', 'string', 'in:Dog,Cat,Other'],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'weight_category' => ['nullable', 'required_if:species,Dog,Other', 'in:Small,Medium,Large,XLarge'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['weight_category'] = $validated['species'] !== 'Cat'
            ? $validated['weight_category']
            : null;

        $pet->update($validated);

        return redirect()
            ->route('customers.show', $pet->customer_id)
            ->with('success', 'Pet updated successfully.');
    }

    public function destroy(Pet $pet)
    {
        $customerId = $pet->customer_id;

        $pet->delete();

        return redirect()
            ->route('customers.show', $customerId)
            ->with('success', 'Pet moved to Archive.');
    }
}