<?php

namespace App\Http\Controllers;

use App\Models\GroomingAddon;
use Illuminate\Http\Request;

class GroomingAddonController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validatedAddon($request);
        GroomingAddon::create($validated);

        return redirect()->route('services.index')->with('success', 'Grooming add-on created.');
    }

    public function edit(GroomingAddon $groomingAddon)
    {
        return view('services.addon-edit', compact('groomingAddon'));
    }

    public function update(Request $request, GroomingAddon $groomingAddon)
    {
        $validated = $this->validatedAddon($request, true);
        $groomingAddon->update($validated);

        return redirect()->route('services.index')->with('success', 'Grooming add-on updated.');
    }

    public function destroy(GroomingAddon $groomingAddon)
    {
        $groomingAddon->delete();

        return redirect()->route('services.index')->with('success', 'Grooming add-on moved to Archive.');
    }

    private function validatedAddon(Request $request, bool $updating = false): array
    {
        $request->merge(['category' => $request->input('category', 'extra_service')]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:detangling,extra_service'],
            'description' => ['nullable', 'string', 'max:1000'],
            'rate' => ['required', 'numeric', 'min:0'],
            'active' => [$updating ? 'nullable' : 'sometimes', 'boolean'],
        ]);
    }
}