<?php

namespace App\Http\Controllers;

use App\Models\GroomingPrice;
use Illuminate\Http\Request;

class GroomingPriceController extends Controller
{
    public function index()
    {
        $prices = GroomingPrice::orderBy('min_weight')->get();

        return view('grooming-prices.index', compact('prices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'in:Small,Medium,Large'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        [$minWeight, $maxWeight] = $this->rangeForCategory($validated['category']);

        GroomingPrice::create([
            'min_weight' => $minWeight,
            'max_weight' => $maxWeight,
            'price' => $validated['price'],
            'active' => true,
        ]);

        return redirect()
            ->route('grooming-prices.index')
            ->with('success', 'Grooming price added successfully.');
    }

    public function update(Request $request, GroomingPrice $groomingPrice)
    {
        $validated = $request->validate([
            'category' => ['required', 'in:Small,Medium,Large'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        [$minWeight, $maxWeight] = $this->rangeForCategory($validated['category']);

        $groomingPrice->update([
            'min_weight' => $minWeight,
            'max_weight' => $maxWeight,
            'price' => $validated['price'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('grooming-prices.index')
            ->with('success', 'Grooming price updated successfully.');
    }

    public function destroy(GroomingPrice $groomingPrice)
    {
        $groomingPrice->delete();

        return redirect()
            ->route('grooming-prices.index')
            ->with('success', 'Grooming price moved to Archive.');
    }

    private function rangeForCategory(string $category): array
    {
        return match ($category) {
            'Small' => [0, 5],
            'Medium' => [5.01, 15],
            'Large' => [15.01, null],
        };
    }
}