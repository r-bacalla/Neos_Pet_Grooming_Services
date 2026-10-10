@extends('layouts.app')

@section('title', 'Services')

@section('content')

<div class="min-h-screen bg-slate-50" x-data="{
    addServiceOpen: @js(old('_form') === 'service' && $errors->any()),
    addAddonOpen: @js(old('_form') === 'grooming-addon'),
    editServiceOpen: @js(old('_form') === 'service-edit' && $editingService),
    editingServiceId: @js($editingService?->id),
    editName: @js(old('_form') === 'service-edit' ? old('name', $editingService?->name) : ''),
    editSpecies: @js(old('_form') === 'service-edit' ? old('species', $editingService?->species ?? 'Dog') : 'Dog'),
    editSize: @js(old('_form') === 'service-edit' ? old('size', $editingService?->size ?? '') : ''),
    editRate: @js(old('_form') === 'service-edit' ? old('rate', $editingService?->rate) : ''),
    editDescription: @js(old('_form') === 'service-edit' ? old('description', $editingService?->description) : ''),
    editActive: @js((bool) old('active', $editingService?->active ?? false)),
    editAvailableAddonIds: @js(array_map('strval', old('available_add_on_ids', $editingService?->available_add_on_ids ?? []))),
    editAddonOpen: @js(old('_form') === 'grooming-addon-edit' && $errors->any()),
    editingAddonId: @js(old('_form') === 'grooming-addon-edit' ? old('grooming_addon_id') : null),
    editAddonName: @js(old('_form') === 'grooming-addon-edit' ? old('name') : ''),
    editAddonCategory: @js(old('_form') === 'grooming-addon-edit' ? old('category', 'extra_service') : 'extra_service'),
    editAddonRate: @js(old('_form') === 'grooming-addon-edit' ? old('rate') : ''),
    editAddonDescription: @js(old('_form') === 'grooming-addon-edit' ? old('description') : ''),
    editAddonActive: @js((bool) old('active', false)),
    newServiceSpecies: @js(old('_form') === 'service' ? old('species', 'Dog') : 'Dog'),
    newServiceSize: @js(old('_form') === 'service' ? old('size', '') : ''),
    openAddonEditor(addon) {
        this.editingAddonId = addon.id;
        this.editAddonName = addon.name;
        this.editAddonCategory = addon.category;
        this.editAddonRate = addon.rate;
        this.editAddonDescription = addon.description || '';
        this.editAddonActive = addon.active;
        this.editAddonOpen = true;
        this.$nextTick(() => document.getElementById('edit_grooming_addon_name')?.focus());
    }
}" @keydown.escape.window="addServiceOpen = false; addAddonOpen = false; editServiceOpen = false; editAddonOpen = false">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">
                            Services
                        </h1>
                    </div>

                    <p class="mt-1 text-sm text-emerald-100/80">
                        Manage available pet grooming services and rates.
                    </p>
                </div>

            </div>

                <div class="flex flex-wrap gap-2">
                <button type="button" @click="addAddonOpen = true; $nextTick(() => $refs.addonName.focus())"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span aria-hidden="true">+</span>
                    Add Add-on
                </button>
                <button type="button" @click="newServiceSpecies = 'Dog'; newServiceSize = ''; addServiceOpen = true; $nextTick(() => $refs.serviceName.focus())"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M12 5v14m-7-7h14"/>
                </svg>

                Add Service
            </button>
                </div>

        </div>

        <div x-show="addServiceOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4" @click.self="addServiceOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="add-service-title" class="my-auto max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="add-service-title" class="text-lg font-bold text-slate-900">Add Service</h2>
                        <p class="mt-1 text-sm text-slate-500">Create a main grooming service.</p>
                    </div>
                    <button type="button" @click="addServiceOpen = false" aria-label="Close add service form" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('services.store') }}" class="space-y-4 p-5">
                    @csrf
                    <input type="hidden" name="_form" value="service">
                    <input type="hidden" name="available_add_on_ids_present" value="1">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="add_service_name" class="mb-1 block text-sm font-semibold text-slate-700">Service name *</label>
                            <input id="add_service_name" x-ref="serviceName" name="name" value="{{ old('_form') === 'service' ? old('name') : '' }}" maxlength="255" required class="w-full rounded-lg border-slate-300 text-sm">
                            <p class="mt-1 text-xs text-slate-500">Create one row per size/type variant; reuse the same package name for related variants.</p>
                        </div>
                        <div>
                            <label for="add_service_species" class="mb-1 block text-sm font-semibold text-slate-700">Pet type *</label>
                            <select id="add_service_species" name="species" x-model="newServiceSpecies" @change="newServiceSize = ''" required class="w-full rounded-lg border-slate-300 text-sm">
                                @foreach(['Dog', 'Cat', 'Other'] as $species)
                                    <option value="{{ $species }}">{{ $species }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="add_service_rate" class="mb-1 block text-sm font-semibold text-slate-700">Rate *</label>
                            <input id="add_service_rate" type="number" name="rate" min="0" step="0.01" value="{{ old('_form') === 'service' ? old('rate') : '' }}" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="add_service_size" class="mb-1 block text-sm font-semibold text-slate-700">Pet size *</label>
                            <select id="add_service_size" name="size" x-model="newServiceSize" required class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="">Select size</option>
                                <template x-for="size in newServiceSpecies === 'Dog' ? ['Small', 'Medium', 'Large', 'XLarge'] : ['Any']" :key="size">
                                    <option :value="size" x-text="size"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="add_service_description" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                            <textarea id="add_service_description" name="description" rows="2" maxlength="1000" class="w-full rounded-lg border-slate-300 text-sm">{{ old('description') }}</textarea>
                        </div>
                        @if($groomingAddons->where('active', true)->isNotEmpty())
                        <fieldset class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <legend class="px-1 text-sm font-semibold text-slate-800">Available add-ons</legend>
                            <p class="mb-3 text-xs text-slate-500">Customers can choose these extras with this service.</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($groomingAddons->where('active', true) as $addon)
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                        <input type="checkbox" name="available_add_on_ids[]" value="{{ $addon->id }}" @checked(old('_form') === 'service' && in_array($addon->id, old('available_add_on_ids', []))) class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                        <span class="min-w-0 flex-1">{{ $addon->name }}</span>
                                        <span class="text-xs text-slate-500">₱{{ number_format((float) $addon->rate, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="addServiceOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Save Service</button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="addAddonOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4" @click.self="addAddonOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="add-grooming-addon-title" class="my-auto max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="add-grooming-addon-title" class="text-lg font-bold text-slate-900">Add Grooming Add-on</h2>
                        <p class="mt-1 text-sm text-slate-500">Set an optional grooming extra and its additional price.</p>
                    </div>
                    <button type="button" @click="addAddonOpen = false" aria-label="Close add-on form" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('grooming-addons.store') }}" class="space-y-4 p-5">
                    @csrf
                    <input type="hidden" name="_form" value="grooming-addon">
                    <div>
                        <label for="grooming_addon_name" class="mb-1 block text-sm font-semibold text-slate-700">Add-on name *</label>
                        <input id="grooming_addon_name" x-ref="addonName" name="name" value="{{ old('_form') === 'grooming-addon' ? old('name') : '' }}" maxlength="255" required placeholder="Scented soap" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label for="grooming_addon_category" class="mb-1 block text-sm font-semibold text-slate-700">Category *</label>
                        <select id="grooming_addon_category" name="category" required class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="extra_service" @selected(old('_form') === 'grooming-addon' && old('category', 'extra_service') === 'extra_service')>Extra Services</option>
                            <option value="detangling" @selected(old('_form') === 'grooming-addon' && old('category') === 'detangling')>Detangling</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Detangling choices are mutually exclusive; Extra Services can be combined.</p>
                    </div>
                    <div>
                        <label for="grooming_addon_rate" class="mb-1 block text-sm font-semibold text-slate-700">Additional price *</label>
                        <input id="grooming_addon_rate" type="number" name="rate" min="0" step="0.01" value="{{ old('_form') === 'grooming-addon' ? old('rate') : '' }}" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label for="grooming_addon_description" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea id="grooming_addon_description" name="description" rows="2" maxlength="1000" class="w-full rounded-lg border-slate-300 text-sm">{{ old('_form') === 'grooming-addon' ? old('description') : '' }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="addAddonOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Save Add-on</button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="editServiceOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="editServiceOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="edit-service-title" class="my-auto flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Service catalog</p>
                        <h2 id="edit-service-title" class="mt-1 text-lg font-bold text-slate-900">Edit Service</h2>
                        <p class="mt-1 text-sm text-slate-500">Update this package variant and its availability.</p>
                    </div>
                    <button type="button" @click="editServiceOpen = false" aria-label="Close edit service form" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-700">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </header>

                @if($errors->any() && old('_form') === 'service-edit')
                    <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Please check the service details.</p>
                        <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" :action="'{{ url('/services') }}/' + editingServiceId" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="service-edit">
                    <input type="hidden" name="service_id" x-model="editingServiceId">
                    <input type="hidden" name="available_add_on_ids_present" value="1">
                    <input type="hidden" name="catalog_search" value="{{ request('search') }}">
                    <input type="hidden" name="catalog_species" value="{{ request('species') }}">
                    <input type="hidden" name="catalog_addons" value="{{ request('addons') }}">
                    <input type="hidden" name="active" value="0">

                    <div class="space-y-4 overflow-y-auto p-5">
                        <div>
                            <label for="edit_service_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Service name <span class="text-red-600">*</span></label>
                            <input id="edit_service_name" name="name" x-model="editName" maxlength="255" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100">
                            <p class="mt-1 text-xs text-slate-500">Keep size and pet-type variants under the same package name.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit_service_species" class="mb-1.5 block text-sm font-semibold text-slate-700">Pet type <span class="text-red-600">*</span></label>
                                <select id="edit_service_species" name="species" x-model="editSpecies" @change="editSize = editSpecies === 'Dog' ? (editSize === 'Any' ? 'Small' : editSize) : 'Any'" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100">
                                    @foreach(['Dog', 'Cat', 'Other'] as $species)
                                        <option value="{{ $species }}">{{ $species }}</option>
                                    @endforeach
            <script type="application/json" x-ref="availableAddonMap">@json($services->getCollection()->mapWithKeys(fn ($service) => [(string) $service->id => array_map('strval', $service->available_add_on_ids ?? [])])->all())</script>
            <span class="hidden" x-effect="if (!editServiceOpen) { $el.dataset.serviceId = '' } else if (String(editingServiceId) !== $el.dataset.serviceId) { editAvailableAddonIds = JSON.parse($refs.availableAddonMap.textContent)[String(editingServiceId)] || []; $el.dataset.serviceId = String(editingServiceId) }"></span>
                                </select>
                            </div>
                            <div>
                                <label for="edit_service_size" class="mb-1.5 block text-sm font-semibold text-slate-700">Pet size <span class="text-red-600">*</span></label>
                                <select id="edit_service_size" name="size" x-model="editSize" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100">
                                    <template x-if="editSpecies === 'Dog'"><optgroup label="Dog sizes"><option value="Small">Small</option><option value="Medium">Medium</option><option value="Large">Large</option><option value="XLarge">XLarge</option></optgroup></template>
                                    <template x-if="editSpecies !== 'Dog'"><option value="Any">Any size</option></template>
                                </select>
                            </div>
                            <div>
                                <label for="edit_service_rate" class="mb-1.5 block text-sm font-semibold text-slate-700">Rate <span class="text-red-600">*</span></label>
                                <div class="flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-emerald-700 focus-within:ring-2 focus-within:ring-emerald-100">
                                    <span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">₱</span>
                                    <input id="edit_service_rate" type="number" name="rate" min="0" step="0.01" x-model="editRate" required class="w-full border-0 text-sm focus:ring-0">
                                </div>
                            </div>
                            <div>
                                <label for="edit_service_description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
                                <textarea id="edit_service_description" name="description" x-model="editDescription" rows="2" maxlength="1000" class="w-full resize-y rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100"></textarea>
                            </div>
                        </div>

                        @if($groomingAddons->where('active', true)->isNotEmpty())
                            <fieldset class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <legend class="px-1 text-sm font-semibold text-slate-800">Available add-ons</legend>
                                <p class="mb-3 text-xs text-slate-500">Customers can choose these extras with this service.</p>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach($groomingAddons->where('active', true) as $addon)
                                        <label class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                            <input type="checkbox" name="available_add_on_ids[]" value="{{ $addon->id }}" x-model="editAvailableAddonIds" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                            <span class="min-w-0 flex-1">{{ $addon->name }}</span>
                                            <span class="text-xs text-slate-500">₱{{ number_format((float) $addon->rate, 2) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-3 transition hover:border-emerald-300">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Available for booking</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Inactive services remain in past records.</span>
                            </span>
                            <input type="checkbox" name="active" value="1" x-model="editActive" class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                        </label>
                    </div>

                    <footer class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                        <button type="button" @click="editServiceOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Save changes</button>
                    </footer>
                </form>
            </section>
        </div>


        <div x-show="editAddonOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="editAddonOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="edit-grooming-addon-title" class="my-auto flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Service catalog</p>
                        <h2 id="edit-grooming-addon-title" class="mt-1 text-lg font-bold text-slate-900">Edit Add-on</h2>
                        <p class="mt-1 text-sm text-slate-500">Update the add-on details and availability.</p>
                    </div>
                    <button type="button" @click="editAddonOpen = false" aria-label="Close edit add-on form" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-700">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6 18 18"/></svg>
                    </button>
                </header>

                @if($errors->any() && old('_form') === 'grooming-addon-edit')
                    <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Please check the add-on details.</p>
                        <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" :action="'{{ url('/grooming-addons') }}/' + editingAddonId" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="grooming-addon-edit">
                    <input type="hidden" name="grooming_addon_id" x-model="editingAddonId">
                    <input type="hidden" name="active" value="0">

                    <div class="space-y-4 overflow-y-auto p-5">
                        <div>
                            <label for="edit_grooming_addon_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Add-on name <span class="text-red-600">*</span></label>
                            <input id="edit_grooming_addon_name" name="name" x-model="editAddonName" maxlength="255" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit_grooming_addon_category" class="mb-1.5 block text-sm font-semibold text-slate-700">Category <span class="text-red-600">*</span></label>
                                <select id="edit_grooming_addon_category" name="category" x-model="editAddonCategory" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100">
                                    <option value="extra_service">Extra Services</option>
                                    <option value="detangling">Detangling</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit_grooming_addon_rate" class="mb-1.5 block text-sm font-semibold text-slate-700">Additional price <span class="text-red-600">*</span></label>
                                <div class="flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-emerald-700 focus-within:ring-2 focus-within:ring-emerald-100">
                                    <span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">₱</span>
                                    <input id="edit_grooming_addon_rate" type="number" name="rate" min="0" step="0.01" x-model="editAddonRate" required class="w-full border-0 text-sm focus:ring-0">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="edit_grooming_addon_description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea id="edit_grooming_addon_description" name="description" x-model="editAddonDescription" rows="3" maxlength="1000" class="w-full resize-y rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-100"></textarea>
                        </div>
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-3 transition hover:border-emerald-300">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Active and available in Grooming</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Inactive add-ons remain in past records.</span>
                            </span>
                            <input type="checkbox" name="active" value="1" x-model="editAddonActive" class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                        </label>
                    </div>

                    <footer class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                        <button type="button" @click="editAddonOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Save changes</button>
                    </footer>
                </form>
            </section>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="m5 12 4 4L19 6"/>
                        </svg>
                    </div>

                    <p class="text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </p>

                </div>
            </div>
        @endif


        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-center gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </div>

                    <p class="text-sm font-medium text-red-800">
                        {{ session('error') }}
                    </p>

                </div>
            </div>
        @endif


        {{-- Summary --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Services</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $services->total() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Current Page</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $services->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Page</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $services->currentPage() }}</p>
            </div>
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="min-w-0">
        {{-- Services --}}
        <div class="inventory-content-frame overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Service Catalog</h2>
                    <p class="mt-1 text-sm text-slate-500">Services currently available in your grooming system.</p>
                </div>
                <div class="text-sm text-slate-500">
                    {{ $services->firstItem() ?? 0 }}–{{ $services->lastItem() ?? 0 }} of {{ $services->total() }}
                </div>
            </div>

            <form method="GET" action="{{ route('services.index') }}" class="grid grid-cols-1 gap-x-2 gap-y-3 border-b border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[minmax(0,1.3fr)_minmax(0,.8fr)_minmax(0,1.1fr)_auto] md:items-end">
                <div>
                    <label for="service_search" class="mb-1 block text-xs font-semibold text-slate-600">Search services</label>
                    <input id="service_search" type="search" name="search" list="service_name_suggestions" value="{{ request('search') }}" placeholder="Service name or description" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <datalist id="service_name_suggestions">
                        @foreach($serviceNames as $serviceName)
                            <option value="{{ $serviceName }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label for="service_species_filter" class="mb-1 block text-xs font-semibold text-slate-600">Pet type</label>
                    <select id="service_species_filter" name="species" onchange="this.form.requestSubmit()" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                        <option value="">All pet types</option>
                        @foreach(['Dog', 'Cat', 'Other'] as $species)
                            <option value="{{ $species }}" @selected(request('species') === $species)>{{ $species }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="service_addons_filter" class="mb-1 block text-xs font-semibold text-slate-600">Available add-ons</label>
                    <select id="service_addons_filter" name="addons" onchange="this.form.requestSubmit()" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                        <option value="" @selected(request('addons') === '')>All services</option>
                        <option value="with" @selected(request('addons') === 'with')>Has available add-ons</option>
                        <option value="without" @selected(request('addons') === 'without')>No available add-ons</option>
                    </select>
                </div>
                <div class="flex min-h-10 gap-2">
                    <button type="submit" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-900">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                        Search
                    </button>
                </div>
            </form>

            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="w-full min-w-[800px] table-fixed text-left">

                    <colgroup>
                        <col class="w-[18%]">
                        <col class="w-[11%]">
                        <col class="w-[19%]">
                        <col class="w-[12%]">
                        <col class="w-[14%]">
                        <col class="w-[12%]">
                        <col class="w-[14%]">
                    </colgroup>

                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Service
                            </th>

                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Pet Type</th>

                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Description
                            </th>

                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Rate
                            </th>

                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Size
                            </th>

                            <th class="px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="px-3 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($services as $service)

                            <tr class="transition hover:bg-slate-50/80">

                                {{-- Service --}}
                                <td class="px-3 py-4">

                                    <div class="flex items-center">
                                        <div>
                                                <p class="break-words font-semibold leading-5 text-slate-900">
                                                {{ $service->name }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-400">
                                                Grooming Service
                                            </p>
                                        </div>

                                    </div>

                                </td>

                                <td class="px-3 py-4"><span class="inline-flex max-w-full rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800">{{ $service->species ?? 'Dog' }}</span></td>

                                {{-- Description --}}
                                <td class="px-3 py-4">
                                    <div x-data="{ descriptionOpen: false }">
                                        <button type="button" @click="descriptionOpen = true" aria-label="View description for {{ $service->name }}" class="inline-flex min-h-8 items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</button>
                                        <div x-show="descriptionOpen" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/50 p-4" role="presentation" @click.self="descriptionOpen = false" @keydown.escape.window="descriptionOpen = false">
                                            <section role="dialog" aria-modal="true" aria-labelledby="service-description-title-{{ $service->id }}" class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                                                <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                                                    <h2 id="service-description-title-{{ $service->id }}" class="font-bold text-slate-900">{{ $service->name }} description</h2>
                                                    <button type="button" @click="descriptionOpen = false" aria-label="Close description" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100">×</button>
                                                </header>
                                                <p class="whitespace-pre-line break-words px-4 py-4 text-sm leading-6 text-slate-700">{{ $service->description ?: 'No description provided.' }}</p>
                                            </section>
                                        </div>
                                    </div>
                                </td>

                                {{-- Rate --}}
                                <td class="px-3 py-4">

                                    <span class="font-bold text-slate-900">
                                        ₱{{ number_format((float) $service->rate, 2) }}
                                    </span>

                                </td>


                                {{-- Size --}}
                                <td class="px-3 py-4">

                                    <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700">

                                            {{ $service->size }}
                                    </span>

                                </td>


                                {{-- Status --}}
                                <td class="px-3 py-4">

                                    @if($service->active)

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Inactive
                                        </span>

                                    @endif

                                </td>


                                {{-- Action --}}
                                <td class="w-[7rem] min-w-[7rem] px-2 py-4">
                                    <div class="grid w-24 grid-cols-2 gap-x-1">
                                        <button type="button" aria-label="Edit {{ $service->name }}" @click="editingServiceId = {{ $service->id }}; editName = @js($service->name); editSpecies = @js($service->species ?? 'Dog'); editSize = @js($service->size); editRate = @js((string) $service->rate); editDescription = @js($service->description ?? ''); editActive = @js((bool) $service->active); editServiceOpen = true; $nextTick(() => document.getElementById('edit_service_name')?.focus())" class="record-action-secondary col-span-2 block w-full whitespace-nowrap rounded-md px-2 py-1.5 text-center text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-700">Edit</button>
                                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Move this service to Archive? Its sales and grooming history will be kept.');" class="col-span-2 mt-1.5 w-full border-t border-slate-200 pt-1.5">
                                        @csrf
                                        @method('DELETE')
                                            <button type="submit" class="record-action-danger block w-full min-w-0 whitespace-nowrap rounded-md px-2 py-1.5 text-[11px] font-semibold">Delete</button>
                                        </form>
                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">

                                    <div class="mx-auto flex max-w-md flex-col items-center">

                                        <h3 class="text-base font-bold text-slate-900">
                                            No services found
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Add your first grooming service to start building your service catalog.
                                        </p>

                                                     <button type="button" @click="addServiceOpen = true; $nextTick(() => $refs.serviceName.focus())"
                                                         class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="h-4 w-4"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M12 5v14m-7-7h14"/>
                                            </svg>

                                            Add Service
                                        </button>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile Cards --}}
            <div class="divide-y divide-slate-100 md:hidden">

                @forelse($services as $service)

                    <div class="p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div class="flex min-w-0 items-center">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-slate-900">
                                        {{ $service->name }}
                                    </h3>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{ $service->species ?? 'Dog' }} package variant
                                    </p>
                                </div>

                            </div>


                            @if($service->active)

                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>

                            @else

                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>

                            @endif

                        </div>


                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-medium text-slate-400">Pet Type</p>
                                <p class="mt-1 font-semibold text-slate-900">{{ $service->species ?? 'Dog' }}</p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-medium text-slate-400">
                                    Rate
                                </p>

                                <p class="mt-1 font-bold text-slate-900">
                                    ₱{{ number_format((float) $service->rate, 2) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-medium text-slate-400">
                                    Size
                                </p>

                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ $service->size }}
                                </p>
                            </div>

                        </div>


                        <div class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white p-3">
                            <p class="text-xs font-medium text-slate-500">Description</p>
                            <div x-data="{ descriptionOpen: false }">
                                <button type="button" @click="descriptionOpen = true" aria-label="View description for {{ $service->name }}" class="inline-flex min-h-8 items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</button>
                                <div x-show="descriptionOpen" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/50 p-4" role="presentation" @click.self="descriptionOpen = false" @keydown.escape.window="descriptionOpen = false">
                                    <section role="dialog" aria-modal="true" aria-labelledby="mobile-service-description-title-{{ $service->id }}" class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                                        <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                                            <h2 id="mobile-service-description-title-{{ $service->id }}" class="font-bold text-slate-900">{{ $service->name }} description</h2>
                                            <button type="button" @click="descriptionOpen = false" aria-label="Close description" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100">×</button>
                                        </header>
                                        <p class="whitespace-pre-line break-words px-4 py-4 text-sm leading-6 text-slate-700">{{ $service->description ?: 'No description provided.' }}</p>
                                    </section>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                                <button type="button" aria-label="Edit {{ $service->name }}" @click="editingServiceId = {{ $service->id }}; editName = @js($service->name); editSpecies = @js($service->species ?? 'Dog'); editSize = @js($service->size); editRate = @js((string) $service->rate); editDescription = @js($service->description ?? ''); editActive = @js((bool) $service->active); editServiceOpen = true; $nextTick(() => document.getElementById('edit_service_name')?.focus())"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M16.862 4.487a2.121 2.121 0 0 1 3 3L8.25 19.1 4 20l.9-4.25L16.862 4.487Z"/>
                            </svg>

                            Edit
                        </a>
                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Move this service to Archive? Its sales and grooming history will be kept.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="record-action-danger min-h-10 w-full rounded-md px-2 py-1.5 text-xs font-semibold">Delete</button>
                        </form>
                        </div>

                    </div>

                @empty

                    <div class="px-5 py-16 text-center">

                        <div class="mx-auto flex max-w-md flex-col items-center">

                            <h3 class="text-base font-bold text-slate-900">
                                No services found
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Add your first grooming service to start building your service catalog.
                            </p>

                                     <button type="button" @click="addServiceOpen = true; $nextTick(() => $refs.serviceName.focus())"
                                         class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Add Service
                                     </button>

                        </div>

                    </div>

                @endforelse

            </div>

        </div>


        @if($services->hasPages())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                {{ $services->links() }}
            </div>
        @endif
        </div>

        <section aria-labelledby="service-addons-title" x-data="{ addonSearch: '', addonCategory: 'all', addonPage: 1, addonPageSize: 15, addonItems: @js($groomingAddons->map(fn ($addon) => ['id' => (string) $addon->id, 'name' => mb_strtolower($addon->name), 'category' => $addon->category])->values()), matchesAddonFilter(name, category) { const query = this.addonSearch.trim().toLowerCase(); return (this.addonCategory === 'all' || this.addonCategory === category) && (!query || name.includes(query)); }, addonMatches(name, category, id) { if (!this.matchesAddonFilter(name, category)) return false; const matchingItems = this.addonItems.filter((addon) => this.matchesAddonFilter(addon.name, addon.category)); const matchingIndex = matchingItems.findIndex((addon) => addon.id === String(id)); return matchingIndex >= (this.addonPage - 1) * this.addonPageSize && matchingIndex < this.addonPage * this.addonPageSize; }, get visibleAddonCount() { return this.addonItems.filter((addon) => this.matchesAddonFilter(addon.name, addon.category)).length; }, get addonPageCount() { return Math.max(1, Math.ceil(this.visibleAddonCount / this.addonPageSize)); } }" class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 id="service-addons-title" class="font-bold text-slate-900">Add-ons</h2>
                <p class="mt-1 text-sm text-slate-500">Optional grooming extras and their additional prices.</p>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_8rem] items-end gap-3 border-b border-slate-200 bg-slate-50/60 p-4 sm:px-5">
                <div>
                    <label for="grooming-addon-search" class="mb-1 block whitespace-nowrap text-xs font-semibold text-slate-600">Search add-ons</label>
                    <input id="grooming-addon-search" type="search" x-model="addonSearch" @input="addonPage = 1" placeholder="Search by add-on name" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-700">
                </div>
                <div>
                    <label for="grooming-addon-category" class="mb-1 block text-xs font-semibold text-slate-600">Category</label>
                    <select id="grooming-addon-category" x-model="addonCategory" @change="addonPage = 1" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-700">
                        <option value="all">All add-ons</option>
                        <option value="detangling">Detangling</option>
                        <option value="extra_service">Extra services</option>
                    </select>
                </div>
            </div>
            @forelse($groomingAddons as $groomingAddon)
                <div x-cloak x-show="addonMatches(@js(mb_strtolower($groomingAddon->name)), @js($groomingAddon->category), @js((string) $groomingAddon->id))" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] gap-x-2 gap-y-2 border-b border-slate-100 px-3 py-3 last:border-b-0 sm:px-4">
                    <div class="min-w-0">
                        <p class="break-words text-sm font-semibold leading-5 text-slate-900">{{ $groomingAddon->name }}</p>
                        <p class="mt-1 break-words text-xs leading-4 text-slate-500">₱{{ number_format((float) $groomingAddon->rate, 2) }}{{ $groomingAddon->description ? ' · ' . $groomingAddon->description : '' }}</p>
                    </div>
                    <span class="inline-flex h-fit max-w-24 items-center justify-center rounded-full px-2 py-1 text-center text-[10px] font-semibold leading-4 {{ $groomingAddon->category === 'detangling' ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }}">{{ $groomingAddon->category === 'detangling' ? 'Detangling' : 'Extra Services' }}</span>
                    <div class="col-span-2 flex min-w-0 gap-2 border-t border-slate-100 pt-2">
                        <button type="button" @click="openAddonEditor(@js(['id' => $groomingAddon->id, 'name' => $groomingAddon->name, 'category' => $groomingAddon->category, 'rate' => (string) $groomingAddon->rate, 'description' => $groomingAddon->description, 'active' => (bool) $groomingAddon->active]))" class="record-action-secondary inline-flex min-h-8 min-w-0 flex-1 items-center justify-center rounded-md px-2 py-1.5 text-center text-[11px] font-semibold">Edit</button>
                        <form method="POST" action="{{ route('grooming-addons.destroy', $groomingAddon) }}" onsubmit="return confirm('Move this add-on to Archive?');" class="min-w-0 flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="record-action-danger min-h-8 w-full rounded-md px-2 py-1.5 text-[11px] font-semibold">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-5 text-sm text-slate-500">No grooming add-ons have been created yet.</p>
            @endforelse
            <p x-show="addonItems.length > 0 && visibleAddonCount === 0" x-cloak class="px-5 py-5 text-sm text-slate-500">No add-ons match this search and category.</p>
            <div x-show="visibleAddonCount > addonPageSize" x-cloak class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3">
                <button type="button" @click="addonPage = Math.max(1, addonPage - 1)" :disabled="addonPage === 1" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                <span class="text-xs font-medium text-slate-600" x-text="`Page ${addonPage} of ${addonPageCount}`"></span>
                <button type="button" @click="addonPage = Math.min(addonPageCount, addonPage + 1)" :disabled="addonPage >= addonPageCount" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
            </div>
        </section>
        </div>

    </div>
</div>

@endsection