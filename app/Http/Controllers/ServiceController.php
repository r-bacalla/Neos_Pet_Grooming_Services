<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\GroomingAddon;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $species = $request->query('species', '');
        $addonFilter = $request->query('addons', '');

        $services = Service::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            }))
            ->when(in_array($species, ['Dog', 'Cat', 'Other'], true), fn ($query) => $query->where('species', $species))
            ->when($addonFilter === 'with', fn ($query) => $query->whereJsonLength('available_add_on_ids', '>', 0))
            ->when($addonFilter === 'without', fn ($query) => $query->where(function ($query) {
                $query->whereNull('available_add_on_ids')
                    ->orWhereJsonLength('available_add_on_ids', 0);
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        $serviceNames = Service::query()->select('name')->distinct()->orderBy('name')->pluck('name');
        $groomingAddons = GroomingAddon::orderBy('name')->get();
        $editingService = old('_form') === 'service-edit'
            ? Service::find(old('service_id'))
            : null;

        return view(
            'services.index',
            compact('services', 'serviceNames', 'groomingAddons', 'editingService')
        );
    }

    public function create()
    {
        $groomingAddons = GroomingAddon::where('active', true)->orderBy('category')->orderBy('name')->get();

        return view('services.create', compact('groomingAddons'));
    }

    public function store(Request $request)
    {
        $request->merge(['species' => $request->input('species', 'Dog')]);
        if ($request->boolean('available_add_on_ids_present') && !$request->has('available_add_on_ids')) {
            $request->merge(['available_add_on_ids' => []]);
        }
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'size' => [
                'required',
                'in:Small,Medium,Large,XLarge,Any',
            ],

            'species' => ['required', 'in:Dog,Cat,Other'],
            'available_add_on_ids' => ['nullable', 'array'],
            'available_add_on_ids.*' => ['integer', 'distinct'],

        ]);

        if (!$this->validPackageVariant($validated)) {
            return back()->withInput()->withErrors([
                'size' => $validated['species'] === 'Dog'
                    ? 'Dog service variants must use Small, Medium, Large, or XLarge.'
                    : 'Cat and Other service variants must use Any size.',
            ]);
        }

        $validated['available_add_on_ids'] = $this->validAvailableAddons($validated['available_add_on_ids'] ?? []);
        if ($validated['available_add_on_ids'] === null) {
            return back()->withInput()->withErrors(['available_add_on_ids' => 'Choose active add-ons available for this service.']);
        }

        Service::create($validated);

        return redirect()
            ->route('services.index')
            ->with(
                'success',
                'Service created.'
            );
    }

    public function edit(Service $service)
    {
        $groomingAddons = GroomingAddon::where('active', true)->orderBy('category')->orderBy('name')->get();

        return view(
            'services.edit',
            compact('service', 'groomingAddons')
        );
    }

    public function update(
        Request $request,
        Service $service
    ) {
        $request->merge(['species' => $request->input('species', $service->species ?? 'Dog')]);
        if ($request->boolean('available_add_on_ids_present') && !$request->has('available_add_on_ids')) {
            $request->merge(['available_add_on_ids' => []]);
        }
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'size' => [
                'required',
                'in:Small,Medium,Large,XLarge,Any',
            ],

            'species' => ['required', 'in:Dog,Cat,Other'],
            'available_add_on_ids' => ['nullable', 'array'],
            'available_add_on_ids.*' => ['integer', 'distinct'],
            'catalog_search' => ['nullable', 'string', 'max:255'],
            'catalog_species' => ['nullable', 'in:Dog,Cat,Other'],
            'catalog_addons' => ['nullable', 'in:with,without'],

            'active' => [
                'nullable',
                'boolean',
            ],

        ]);

        if (!$this->validPackageVariant($validated)) {
            return back()->withInput()->withErrors([
                'size' => $validated['species'] === 'Dog'
                    ? 'Dog service variants must use Small, Medium, Large, or XLarge.'
                    : 'Cat and Other service variants must use Any size.',
            ]);
        }

        $validated['available_add_on_ids'] = $this->validAvailableAddons($validated['available_add_on_ids'] ?? []);
        if ($validated['available_add_on_ids'] === null) {
            return back()->withInput()->withErrors(['available_add_on_ids' => 'Choose active add-ons available for this service.']);
        }

        $catalogFilters = array_filter([
            'search' => $validated['catalog_search'] ?? null,
            'species' => $validated['catalog_species'] ?? null,
            'addons' => $validated['catalog_addons'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
        unset($validated['catalog_search'], $validated['catalog_species'], $validated['catalog_addons']);

        $service->update($validated);

        return redirect()
            ->route('services.index', $catalogFilters)
            ->with(
                'success',
                'Service updated.'
            );
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Service moved to Archive.');
    }

    private function validPackageVariant(array $validated): bool
    {
        return $validated['species'] === 'Dog'
            ? in_array($validated['size'], ['Small', 'Medium', 'Large', 'XLarge'], true)
            : $validated['size'] === 'Any';
    }

    private function validAvailableAddons(array $availableAddOnIds): ?array
    {
        $ids = array_values(array_unique(array_map('intval', $availableAddOnIds)));
        $activeCount = GroomingAddon::where('active', true)->whereIn('id', $ids)->count();

        return $activeCount === count($ids) ? $ids : null;
    }
}