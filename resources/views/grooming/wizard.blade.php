@extends('layouts.app')

@section('title', 'New Grooming Transaction')

@section('content')
<div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 p-3 sm:p-6" role="presentation">
    <div class="mx-auto my-0 flex w-full flex-col items-stretch gap-3 lg:flex-row lg:items-start lg:justify-center"
        :class="showQueue || showServiceSummary ? 'max-w-[1080px]' : 'max-w-3xl'"
        role="dialog" aria-modal="true" aria-labelledby="grooming-wizard-title"
         x-data="{
            step: {{ (int) old('wizard_step', 1) }},
            customerId: @js((string) (auth()->user()->isCustomer() ? ($customerAccount?->id ?? '') : old('customer_id', ''))), petId: @js((string) old('pet_id', '')),
            customerName: @js(old('customer_name', auth()->user()->isCustomer() ? auth()->user()->name : '')), customerPhone: @js(old('customer_phone', $customerAccount?->phone ?? '')),
            customerEmail: @js(old('customer_email', auth()->user()->isCustomer() ? auth()->user()->email : '')), customerAddress: @js(old('customer_address', $customerAccount?->address ?? '')),
            petName: @js(old('pet_name', '')), species: @js(old('species', 'Dog')), breed: @js(old('breed', '')),
            petSize: @js(old('weight_category', old('species') && old('species') !== 'Dog' ? 'Any' : '')),
            petProfileSize: @js(old('profile_size', '')),
            sex: @js(old('sex', '')),
            additionalPets: @js(old('additional_pets', [])).map((pet) => ({ ...pet, profile_size: String(pet.profile_size || ''), service_id: String(pet.service_id || ''), service_name: String(pet.service_name || ''), add_on_ids: (pet.add_on_ids || []).map(String), detangling_addon_id: String(pet.detangling_addon_id || ''), update_saved_profile_size: String(pet.update_saved_profile_size) === '1' })),
            updateSavedPetSize: {{ old('update_saved_profile_size') ? 'true' : 'false' }},
            customerPets: @js($customers->flatMap(fn ($customer) => $customer->pets->map(fn ($pet) => ['id' => (string) $pet->id, 'customer_id' => (string) $customer->id, 'name' => $pet->name, 'species' => $pet->species, 'breed' => $pet->breed, 'weight_category' => $pet->weight_category, 'sex' => $pet->sex]))->values()),
            waiverConfirmed: {{ old('waiver_confirmed') ? 'true' : 'false' }}, waiverFileName: '',
            serviceId: @js((string) old('service_id', '')),
            mainServiceName: @js(old('service_id') ? $services->firstWhere('id', (int) old('service_id'))?->name ?? '' : ''),
            addOnIds: @js(array_map('strval', old('add_on_ids', []))),
            detanglingAddonId: @js((string) old('detangling_addon_id', '')),
            services: @js($services->map(fn ($service) => ['id' => (string) $service->id, 'name' => $service->name, 'species' => $service->species ?? 'Dog', 'size' => $service->size, 'description' => $service->description, 'rate' => (float) $service->rate, 'available_add_on_ids' => array_map('strval', $service->available_add_on_ids ?? [])])->values()),
            groomingAddons: @js($groomingAddons->map(fn ($addon) => ['id' => (string) $addon->id, 'name' => $addon->name, 'category' => $addon->category ?? 'extra_service', 'rate' => (float) $addon->rate])->values()),
            groomerName: @js(old('groomer_name', '')),
            notes: @js(old('notes', '')),
            showQueue: false, showServiceSummary: false,
            expandedMainServiceName: '', hoveredMainServiceName: '',
            get customerPetReady() { return this.customerName.trim() && this.customerPhone.trim() && this.customerAddress.trim() && this.petName.trim() && this.species && (this.species === 'Dog' ? this.petSize : (this.species === 'Other' ? this.petProfileSize : true)) && this.additionalPets.every((pet) => pet.name?.trim() && pet.species && (pet.species === 'Dog' ? pet.weight_category : (pet.species === 'Other' ? pet.profile_size : true))) && this.serviceSelectionReady; },
            petLabel(index) { return `Pet ${index + 1}`; },
            fillCustomer(option) {
                this.customerId = option.value;
                this.customerName = option.dataset.name || '';
                this.customerPhone = option.dataset.phone || '';
                this.customerEmail = option.dataset.email || '';
                this.customerAddress = option.dataset.address || '';
                this.petId = '';
                this.petName = ''; this.breed = ''; this.sex = '';
                this.petProfileSize = '';
                this.updateSavedPetSize = false;
                this.additionalPets = this.additionalPets.map((record) => ({ ...record, pet_id: '', profile_size: '', update_saved_profile_size: false }));
                this.filterPets();
            },
            fillPet(option) {
                this.petId = option.value;
                this.petName = option.dataset.name || '';
                const savedSpecies = option.dataset.species || 'Dog';
                if (savedSpecies !== this.species) {
                    this.changeMainSpecies(savedSpecies);
                }
                this.breed = option.dataset.breed || '';
                this.sex = option.dataset.sex || '';
                this.updateSavedPetSize = false;
                const savedSize = option.dataset.weightCategory || '';
                this.petProfileSize = savedSpecies === 'Other' ? savedSize : '';
                if (savedSpecies === 'Dog' && savedSize !== this.petSize && window.confirm(`This pet's saved size is ${savedSize || 'not set'}, but this visit uses ${this.petSize}. Update the saved pet profile to ${this.petSize}?`)) {
                    this.updateSavedPetSize = true;
                }
            },
            updateMainOtherSize() {
                const savedPet = this.customerPets.find((pet) => pet.id === this.petId);
                this.updateSavedPetSize = Boolean(savedPet && savedPet.species === 'Other' && savedPet.weight_category !== this.petProfileSize);
            },
            addPetRecord() { this.additionalPets.push({ pet_id: '', name: '', species: 'Dog', breed: '', weight_category: '', profile_size: '', sex: '', service_id: '', service_name: '', add_on_ids: [], detangling_addon_id: '', expanded_service_name: '', hovered_service_name: '', update_saved_profile_size: false }); },
            fillAdditionalPet(record, petId) {
                const selectedServiceId = record.service_id;
                const selectedSpecies = record.species;
                record.pet_id = petId;
                record.update_saved_profile_size = false;
                const pet = this.customerPets.find((item) => item.id === String(petId));
                record.name = pet?.name || '';
                record.species = pet?.species || 'Dog';
                record.breed = pet?.breed || '';
                record.sex = pet?.sex || '';
                record.profile_size = record.species === 'Other' ? (pet?.weight_category || '') : '';
                if (selectedSpecies !== record.species || !this.serviceForPet(selectedServiceId, record.species, record.weight_category)) {
                    record.service_id = '';
                    record.service_name = '';
                    record.add_on_ids = [];
                    record.detangling_addon_id = '';
                }
                if (record.species === 'Dog' && pet && pet.weight_category !== record.weight_category && window.confirm(`${pet.name}'s saved size is ${pet.weight_category || 'not set'}, but this visit uses ${record.weight_category}. Update the saved pet profile to ${record.weight_category}?`)) {
                    record.update_saved_profile_size = true;
                }
                this.filterAddons();
            },
            updateAdditionalOtherSize(record) {
                const savedPet = this.customerPets.find((pet) => pet.id === record.pet_id);
                record.update_saved_profile_size = Boolean(savedPet && savedPet.species === 'Other' && savedPet.weight_category !== record.profile_size);
            },
            removePetRecord(index) { this.additionalPets.splice(index, 1); },
            filterPets() {
                Array.from(this.$refs.petSelect.options).forEach((option) => {
                    if (option.value) option.hidden = !this.customerId || option.dataset.customerId !== this.customerId || option.dataset.species !== this.species;
                });
            },
            availableAddons() {
                return this.availableAddonsFor(this.serviceId, this.species, this.petSize, 'extra_service');
            },
            detanglingAddons() {
                return this.availableAddonsFor(this.serviceId, this.species, this.petSize, 'detangling');
            },
            availableAddonsFor(serviceId, species, size, category) {
                const service = this.serviceForPet(serviceId, species, size);
                if (!service) return [];
                const allowedIds = new Set(service.available_add_on_ids || []);
                return this.groomingAddons.filter((addon) => addon.category === category && allowedIds.has(addon.id));
            },
            additionalPetAddons(pet, category) {
                return this.availableAddonsFor(pet.service_id, pet.species, pet.weight_category, category);
            },
            changeMainSpecies(species) {
                this.species = species;
                this.petSize = species === 'Dog' ? '' : 'Any';
                this.petProfileSize = '';
                const selectedPet = this.customerPets.find((pet) => pet.id === this.petId);
                if (selectedPet && selectedPet.species !== species) {
                    this.petId = '';
                    this.petName = '';
                    this.breed = '';
                    this.sex = '';
                    this.updateSavedPetSize = false;
                }
                this.serviceId = '';
                this.mainServiceName = '';
                this.expandedMainServiceName = '';
                this.addOnIds = [];
                this.detanglingAddonId = '';
                this.filterPets();
                this.filterAddons();
            },
            changeAdditionalSpecies(pet, species) {
                pet.species = species;
                pet.weight_category = species === 'Dog' ? '' : 'Any';
                pet.profile_size = '';
                pet.service_id = '';
                pet.service_name = '';
                pet.add_on_ids = [];
                pet.detangling_addon_id = '';
                this.filterAddons();
            },
            toggleMainService(name) {
                if (this.expandedMainServiceName === name) {
                    this.expandedMainServiceName = '';
                    return;
                }
                this.expandedMainServiceName = name;
                if (this.mainServiceName !== name) {
                    this.mainServiceName = name;
                    this.serviceId = '';
                    this.addOnIds = [];
                    this.detanglingAddonId = '';
                }
            },
            availableServicesFor(species, size) {
                const requiredSize = species === 'Dog' ? size : 'Any';
                return this.services.filter((service) => service.species === species && service.size === requiredSize);
            },
            serviceNamesFor(species) {
                return [...new Set(this.services.filter((service) => service.species === species).map((service) => service.name))];
            },
            serviceVariantsFor(species, name) {
                return this.services.filter((service) => service.species === species && service.name === name);
            },
            serviceForPet(serviceId, species, size) {
                return this.availableServicesFor(species, size).find((service) => service.id === String(serviceId));
            },
            get serviceSelectionReady() {
                return Boolean(this.species && (this.species !== 'Dog' || this.petSize) && this.serviceForPet(this.serviceId, this.species, this.petSize)) &&
                    this.additionalPets.every((pet) => Boolean(pet.species && (pet.species !== 'Dog' || pet.weight_category) && this.serviceForPet(pet.service_id, pet.species, pet.weight_category)));
            },
            filterAddons() {
                const mainAvailableIds = [...this.availableAddons(), ...this.detanglingAddons()].map((addon) => addon.id);
                this.addOnIds = this.addOnIds.filter((id) => mainAvailableIds.includes(id));
                if (this.detanglingAddonId && !mainAvailableIds.includes(this.detanglingAddonId)) this.detanglingAddonId = '';
                this.additionalPets.forEach((pet) => {
                    const availableIds = [...this.additionalPetAddons(pet, 'extra_service'), ...this.additionalPetAddons(pet, 'detangling')].map((addon) => addon.id);
                    pet.add_on_ids = (pet.add_on_ids || []).filter((id) => availableIds.includes(id));
                    if (pet.detangling_addon_id && !availableIds.includes(pet.detangling_addon_id)) pet.detangling_addon_id = '';
                });
            },
            serviceTotal() {
                const selectedServices = [
                    this.serviceForPet(this.serviceId, this.species, this.petSize),
                    ...this.additionalPets.map((pet) => this.serviceForPet(pet.service_id, pet.species, pet.weight_category)),
                ].filter(Boolean);
                const addonTotal = this.availableAddons()
                    .filter((addon) => this.addOnIds.includes(addon.id))
                    .reduce((sum, addon) => sum + Number(addon.rate), 0);
                const detangling = this.detanglingAddons().find((addon) => addon.id === this.detanglingAddonId);
                const additionalAddonTotal = this.additionalPets.reduce((total, pet) => {
                    const extras = this.additionalPetAddons(pet, 'extra_service')
                        .filter((addon) => pet.add_on_ids.includes(addon.id))
                        .reduce((sum, addon) => sum + Number(addon.rate), 0);
                    const detanglingAddon = this.additionalPetAddons(pet, 'detangling')
                        .find((addon) => addon.id === pet.detangling_addon_id);
                    return total + extras + Number(detanglingAddon?.rate || 0);
                }, 0);

                return selectedServices.reduce((sum, service) => sum + Number(service.rate), 0) + addonTotal + Number(detangling?.rate || 0) + additionalAddonTotal;
            },
            selectService(serviceId) {
                this.serviceId = String(serviceId);
                const selectedService = this.services.find((service) => service.id === this.serviceId);
                if (selectedService?.size) this.petSize = selectedService.size;
                this.filterAddons();
            },
            serviceLabel() { return this.serviceForPet(this.serviceId, this.species, this.petSize)?.name || 'Choose a service'; },
            serviceLabelFor(serviceId) { return this.services.find((service) => service.id === String(serviceId))?.name || 'Package not selected'; },
            addonLabels() {
                const labels = [
                    ...this.availableAddons().filter((addon) => this.addOnIds.includes(addon.id)).map((addon) => addon.name),
                    ...this.detanglingAddons().filter((addon) => addon.id === this.detanglingAddonId).map((addon) => addon.name),
                ];
                this.additionalPets.forEach((pet) => {
                    const petAddons = [
                        ...this.additionalPetAddons(pet, 'extra_service').filter((addon) => pet.add_on_ids.includes(addon.id)),
                        ...this.additionalPetAddons(pet, 'detangling').filter((addon) => addon.id === pet.detangling_addon_id),
                    ];
                    labels.push(...petAddons.map((addon) => `${pet.name || `Pet ${this.additionalPets.indexOf(pet) + 2}`}: ${addon.name}`));
                });
                return labels;
            },
            petServiceSummary() {
                const rows = [];
                const mainService = this.serviceForPet(this.serviceId, this.species, this.petSize);
                if (this.petName) {
                    rows.push({
                        label: `${this.petName}: ${mainService?.name || 'No service selected'}${mainService ? ` · ₱${Number(mainService.rate).toFixed(2)}` : ''}`,
                    });
                }
                this.additionalPets.forEach((pet) => {
                    const service = this.serviceForPet(pet.service_id, pet.species, pet.weight_category);
                    rows.push({
                        label: `${pet.name || 'Unnamed pet'}: ${service?.name || 'No service selected'}${service ? ` · ₱${Number(service.rate).toFixed(2)}` : ''}`,
                    });
                });
                return rows;
            },
            petAddonSummary() {
                const rows = [];
                const mainAddons = [
                    ...this.availableAddons().filter((addon) => this.addOnIds.includes(addon.id)),
                    ...this.detanglingAddons().filter((addon) => addon.id === this.detanglingAddonId),
                ];
                rows.push({
                    label: `${this.petName || 'Pet'}: ${mainAddons.length ? mainAddons.map((addon) => addon.name).join(', ') : 'None'}`,
                });
                this.additionalPets.forEach((pet) => {
                    const petAddons = [
                        ...this.additionalPetAddons(pet, 'extra_service').filter((addon) => pet.add_on_ids.includes(addon.id)),
                        ...this.additionalPetAddons(pet, 'detangling').filter((addon) => addon.id === pet.detangling_addon_id),
                    ];
                    rows.push({
                        label: `${pet.name || 'Unnamed pet'}: ${petAddons.length ? petAddons.map((addon) => addon.name).join(', ') : 'None'}`,
                    });
                });
                return rows;
            },
            scrollToCurrentStep() {
                this.$nextTick(() => this.$refs['wizard-step-' + this.step]?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            },
            validateCurrentStep() {
                const currentStep = this.$refs['wizard-step-' + this.step];
                const invalidControl = currentStep?.querySelector(':invalid');

                if (!invalidControl) return true;

                invalidControl.reportValidity();
                invalidControl.focus({ preventScroll: true });
                return false;
            },
            next() {
                if (this.step < 4 && this.validateCurrentStep()) {
                    this.step++;
                    this.scrollToCurrentStep();
                }
            },
            back() {
                if (this.step > 1) {
                    this.step--;
                    this.scrollToCurrentStep();
                }
            },
            goToStep(step) {
                this.step = step;
                this.scrollToCurrentStep();
            },
         }"
         x-init="filterPets(); filterAddons()">
        <section class="flex w-full max-w-3xl shrink-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl">
        <div class="flex shrink-0 flex-wrap items-end justify-between gap-3 bg-emerald-950 px-5 py-5 text-white sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Grooming request</p>
                <h1 id="grooming-wizard-title" class="mt-1 text-xl font-bold text-white sm:text-2xl">New Grooming Transaction</h1>
                <p class="mt-1 text-sm text-emerald-100/80">Add the visit details, review the total, then choose when to pay.</p>
            </div>
            <a href="{{ auth()->user()->isCustomer() ? route('pos.index') : route('grooming.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                {{ auth()->user()->isCustomer() ? 'Back to POS' : 'Back to Grooming' }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-3 shrink-0 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid shrink-0 grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 px-5 py-4 md:grid-cols-4 sm:px-6" aria-label="Grooming transaction steps">
            @php
                $steps = [
                    'Services & Add-ons',
                    'Waiver',
                    'Customer & Pet',
                    'Summary',
                ];
            @endphp
            @foreach($steps as $index => $stepConfig)
                <div class="wizard-step flex min-h-14 min-w-0 items-center gap-2 rounded-lg border-2 px-2.5 py-2 text-xs transition-colors sm:px-3 sm:text-sm" :class="step === {{ $index + 1 }} ? 'border-emerald-800 bg-emerald-800 text-white shadow-sm' : (step > {{ $index + 1 }} ? 'border-emerald-200 bg-white text-emerald-900' : 'border-slate-200 bg-white text-slate-500')" :aria-current="step === {{ $index + 1 }} ? 'step' : null" data-step="{{ $index + 1 }}">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/15 font-bold" :class="step === {{ $index + 1 }} ? 'text-white' : 'bg-emerald-50 text-emerald-800'">{{ $index + 1 }}</span>
                    <span class="font-semibold leading-tight">{{ $stepConfig }}</span>
                </div>
            @endforeach
        </div>

        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 px-5 pt-3 sm:px-6" aria-label="Additional grooming panels">
            @unless(auth()->user()->isCustomer())
                <button type="button" @click="showQueue = !showQueue" :aria-expanded="showQueue" class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    <span x-text="showQueue ? 'Hide queue' : 'Show queue'"></span>
                </button>
            @endunless
            <button type="button" @click="showServiceSummary = !showServiceSummary" :aria-expanded="showServiceSummary" class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                <span x-text="showServiceSummary ? 'Hide service summary' : 'Show service summary'"></span>
            </button>
        </div>

        @if($errors->any())
            <div class="mb-3 shrink-0 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Please check the grooming transaction details.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('grooming.store') }}" enctype="multipart/form-data" class="grooming-form flex flex-col">
            @csrf
            <input type="hidden" name="wizard_step" :value="step">
            <input type="hidden" name="customer_id" :value="customerId">
            <input type="hidden" name="pet_id" :value="petId">

            <div class="flex flex-col">
                <section class="flex flex-col border-t border-slate-200 bg-white">
                    <div class="shrink-0 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-800" x-text="`STEP ${step} OF 4`"></p>
                        <h2 class="mt-0.5 text-base font-bold text-slate-900 sm:text-lg" x-text="['Services & Add-ons', 'Waiver', 'Customer & Pet', 'Summary'][step - 1]"></h2>
                    </div>

                    <div x-ref="wizard-step-3" x-show="step === 3" class="scroll-mt-24 space-y-5 p-4 sm:p-5">
                        <p x-show="!serviceSelectionReady" role="alert" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                            A pet’s saved type does not match its selected service. <button type="button" @click="goToStep(1)" class="font-semibold underline">Review services and sizes</button> before continuing.
                        </p>
                        <section class="rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="mb-4 rounded-lg bg-emerald-950 px-4 py-3 text-white">
                                <h3 class="font-semibold text-white">{{ auth()->user()->isCustomer() ? 'Your Information' : 'Customer Information' }}</h3>
                                <p class="mt-1 text-xs text-emerald-100/80">{{ auth()->user()->isCustomer() ? 'Your request will be submitted under your account.' : 'Choose an existing customer to fill their saved details, or add someone new.' }}</p>
                            </div>
                            @unless(auth()->user()->isCustomer())
                            <select x-ref="customerSelect" x-model="customerId" @change="fillCustomer($event.target.selectedOptions[0])" class="mb-4 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option value="">Add a new customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-email="{{ $customer->email }}" data-address="{{ $customer->address }}">{{ $customer->name }} · {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                            @endunless
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="customer_name" class="mb-1 block text-xs font-semibold text-slate-600">Customer name *</label>
                                    <input id="customer_name" name="customer_name" x-model="customerName" @if(auth()->user()->isCustomer()) readonly @endif required class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="customer_phone" class="mb-1 block text-xs font-semibold text-slate-600">Phone *</label>
                                    <input id="customer_phone" name="customer_phone" x-model="customerPhone" required class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="customer_email" class="mb-1 block text-xs font-semibold text-slate-600">Email</label>
                                    <input id="customer_email" name="customer_email" type="email" x-model="customerEmail" @if(auth()->user()->isCustomer()) readonly @endif class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="customer_address" class="mb-1 block text-xs font-semibold text-slate-600">Address *</label>
                                    <input id="customer_address" name="customer_address" x-model="customerAddress" required class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="mb-4 rounded-lg bg-emerald-950 px-4 py-3 text-white">
                                <h3 class="font-semibold text-white" x-text="petLabel(0)">Pet 1</h3>
                                <p class="mt-1 text-xs text-emerald-100/80">Customers can have multiple pets saved to their profile.</p>
                            </div>
                            <select x-ref="petSelect" x-model="petId" @change="fillPet($event.target.selectedOptions[0])" class="mb-4 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option value="">Add a new pet</option>
                                @foreach($customers as $customer)
                                    @foreach($customer->pets as $pet)
                                        <option value="{{ $pet->id }}" data-customer-id="{{ $customer->id }}" data-name="{{ $pet->name }}" data-species="{{ $pet->species }}" data-breed="{{ $pet->breed }}" data-weight-category="{{ $pet->weight_category }}" data-sex="{{ $pet->sex }}">{{ $pet->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="pet_name" class="mb-1 block text-xs font-semibold text-slate-600">Pet name for this visit *</label>
                                    <input id="pet_name" name="pet_name" x-model="petName" required class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="species" class="mb-1 block text-xs font-semibold text-slate-600">Pet type *</label>
                                    <select id="species" x-model="species" disabled class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm">
                                        <option>Dog</option><option>Cat</option><option>Other</option>
                                    </select>
                                    <input type="hidden" name="species" :value="species">
                                </div>
                                <div>
                                    <label for="breed" class="mb-1 block text-xs font-semibold text-slate-600">Breed</label>
                                    <input id="breed" name="breed" x-model="breed" class="w-full rounded-lg border-slate-300 text-sm">
                                </div>
                                <div x-show="species === 'Dog'">
                                    <label for="weight_category" class="mb-1 block text-xs font-semibold text-slate-600">Pet size *</label>
                                    <select id="weight_category" x-model="petSize" disabled class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm">
                                        <option value="">Select size</option><option value="Small">Small</option><option value="Medium">Medium</option><option value="Large">Large</option><option value="XLarge">XLarge</option>
                                    </select>
                                </div>
                                <div x-show="species === 'Other'">
                                    <label for="profile_size" class="mb-1 block text-xs font-semibold text-slate-600">Pet size *</label>
                                    <select id="profile_size" x-model="petProfileSize" @change="updateMainOtherSize()" class="w-full rounded-lg border-slate-300 text-sm">
                                        <option value="">Select size</option><option value="Small">Small</option><option value="Medium">Medium</option><option value="Large">Large</option><option value="XLarge">XLarge</option>
                                    </select>
                                </div>
                                <div x-show="species === 'Cat'">
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Pet size</label>
                                    <div class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">Any size</div>
                                </div>
                                <input type="hidden" name="weight_category" :value="species === 'Dog' ? petSize : ''">
                                <input type="hidden" name="profile_size" :value="species === 'Other' ? petProfileSize : ''">
                                <input type="hidden" name="update_saved_profile_size" :value="updateSavedPetSize ? 1 : 0">
                                <div>
                                    <label for="sex" class="mb-1 block text-xs font-semibold text-slate-600">Gender</label>
                                    <select id="sex" name="sex" x-model="sex" class="w-full rounded-lg border-slate-300 text-sm">
                                        <option value="" disabled>Select gender</option><option>Male</option><option>Female</option>
                                    </select>
                                </div>
                                <div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                                <p class="text-xs text-slate-500">Choose another saved pet or add a new pet profile to this visit.</p>
                            </div>
                            <template x-for="(record, index) in additionalPets" :key="index">
                                <fieldset class="mt-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                                    <legend class="sr-only" x-text="petLabel(index + 1)"></legend>
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <h4 class="text-sm font-semibold text-slate-800" x-text="petLabel(index + 1)"></h4>
                                        <button type="button" @click="removePetRecord(index)" class="rounded px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50">Remove</button>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <label :for="`additional_pet_${index}_select`" class="mb-1 block text-xs font-semibold text-slate-600">Saved pet (optional)</label>
                                            <select :id="`additional_pet_${index}_select`" x-bind:name="'additional_pets[' + index + '][pet_id]'" x-model="record.pet_id" @change="fillAdditionalPet(record, $event.target.value)" class="w-full rounded-lg border-slate-300 text-sm">
                                                <option value="">Add a new pet record</option>
                                                <template x-for="savedPet in customerPets.filter((savedPet) => savedPet.customer_id === customerId && savedPet.species === record.species && savedPet.id !== petId && !additionalPets.some((other, otherIndex) => otherIndex !== index && other.pet_id === savedPet.id))" :key="savedPet.id">
                                                    <option :value="savedPet.id" x-text="savedPet.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label :for="`additional_pet_${index}_name`" class="mb-1 block text-xs font-semibold text-slate-600">Pet name *</label>
                                            <input :id="`additional_pet_${index}_name`" x-bind:name="'additional_pets[' + index + '][name]'" x-model="record.name" required class="w-full rounded-lg border-slate-300 text-sm">
                                        </div>
                                        <div>
                                            <label :for="`additional_pet_${index}_species`" class="mb-1 block text-xs font-semibold text-slate-600">Pet type *</label>
                                            <select :id="`additional_pet_${index}_species`" x-model="record.species" disabled class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm">
                                                <option>Dog</option><option>Cat</option><option>Other</option>
                                            </select>
                                            <input type="hidden" x-bind:name="'additional_pets[' + index + '][species]'" :value="record.species">
                                        </div>
                                        <div x-show="record.species === 'Dog'">
                                            <label :for="`additional_pet_${index}_size`" class="mb-1 block text-xs font-semibold text-slate-600">Pet size *</label>
                                            <select :id="`additional_pet_${index}_size`" x-model="record.weight_category" disabled class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm">
                                                <option value="">Select size</option><option value="Small">Small</option><option value="Medium">Medium</option><option value="Large">Large</option><option value="XLarge">XLarge</option>
                                            </select>
                                        </div>
                                        <div x-show="record.species === 'Other'">
                                            <label :for="`additional_pet_${index}_profile_size`" class="mb-1 block text-xs font-semibold text-slate-600">Pet size *</label>
                                            <select :id="`additional_pet_${index}_profile_size`" x-model="record.profile_size" @change="updateAdditionalOtherSize(record)" class="w-full rounded-lg border-slate-300 text-sm">
                                                <option value="">Select size</option><option value="Small">Small</option><option value="Medium">Medium</option><option value="Large">Large</option><option value="XLarge">XLarge</option>
                                            </select>
                                        </div>
                                        <div x-show="record.species === 'Cat'">
                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Pet size</label>
                                            <div class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">Any size</div>
                                        </div>
                                        <input type="hidden" x-bind:name="'additional_pets[' + index + '][weight_category]'" :value="record.species === 'Dog' ? record.weight_category : ''">
                                        <input type="hidden" x-bind:name="'additional_pets[' + index + '][profile_size]'" :value="record.species === 'Other' ? record.profile_size : ''">
                                        <input type="hidden" x-bind:name="'additional_pets[' + index + '][update_saved_profile_size]'" :value="record.update_saved_profile_size ? 1 : 0">
                                        <div>
                                            <label :for="`additional_pet_${index}_breed`" class="mb-1 block text-xs font-semibold text-slate-600">Breed</label>
                                            <input :id="`additional_pet_${index}_breed`" x-bind:name="'additional_pets[' + index + '][breed]'" x-model="record.breed" class="w-full rounded-lg border-slate-300 text-sm">
                                        </div>
                                        <div>
                                            <label :for="`additional_pet_${index}_sex`" class="mb-1 block text-xs font-semibold text-slate-600">Gender</label>
                                            <select :id="`additional_pet_${index}_sex`" x-bind:name="'additional_pets[' + index + '][sex]'" x-model="record.sex" class="w-full rounded-lg border-slate-300 text-sm">
                                                <option value="" disabled>Select gender</option><option>Male</option><option>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                            </template>
                        </section>
                    </div>

                    <div x-ref="wizard-step-2" x-show="step === 2" x-cloak class="scroll-mt-24 space-y-5 p-4 sm:p-5">
                        <div class="flex flex-wrap items-end justify-between gap-2 rounded-xl bg-emerald-950 px-4 py-4 text-white">
                            <div>
                                <h3 class="font-semibold text-white">Consent to Groom &amp; General Liability Waiver</h3>
                                <p class="mt-1 text-xs text-emerald-100/80">Read the waiver and confirm consent. Customer and pet details are collected in the next step.</p>
                            </div>
                            <span class="text-xs font-bold tracking-wide text-emerald-100">NEO'S PET GROOMING SERVICE</span>
                        </div>

                        <article class="space-y-5 rounded-xl border-2 border-slate-200 bg-slate-50 p-5 text-sm leading-7 text-slate-700 sm:p-7 border-l-4 border-l-emerald-800">
                            <header class="border-b border-slate-200 pb-3 text-center">
                                <p class="text-xs font-bold tracking-[0.12em] text-slate-800">NEO'S PET GROOMING SERVICE</p>
                                <h4 class="mt-1 font-bold text-slate-900">Consent to Groom &amp; General Liability Waiver</h4>
                                <p class="mt-2 text-left">At NEO'S PET GROOMING SERVICE, your pet's safety and well-being are our top priorities. We strive to make every grooming visit as comfortable and pleasant as possible.</p>
                            </header>
                            <section>
                                <h5 class="font-bold text-slate-900">1. Matted Pets</h5>
                                <p>Pets with matted fur may need extra time and care. If the matting is minor and the pet is cooperative, we may try to brush it out with an extra fee. However, if brushing is not possible, not safe, or causes your pet distress, we may need to shave the matted areas. This can carry risks, such as cuts or skin irritation, due to the lack of airflow and skin condition under the mats. <strong>NEO'S PET GROOMING SERVICE is not responsible for any injury sustained during dematting.</strong> Groomers are happy to demonstrate proper brushing techniques and recommend maintaining a 4-6 week grooming schedule to avoid matting.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">2. Pets with Medical Conditions</h5>
                                <p>If your pet has allergies or any medical condition (e.g., epilepsy, heart disease, arthritis, diabetes, asthma, separation anxiety, etc.), it is very important that you inform NEO'S PET GROOMING SERVICE in advance. This allows us to take necessary precautions to ensure your pet's comfort and safety.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">3. Owner Responsibility</h5>
                                <p>Pet owners are financially and legally responsible for their pet's behavior and health. NEO'S PET GROOMING SERVICE is not liable for any claims or damages resulting from grooming services. This includes any unexpected reactions or issues related to the grooming process, during or after the appointment.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">4. Aggravation of Medical Conditions</h5>
                                <p>Sometimes grooming may uncover or worsen pre-existing medical issues. In such cases, NEO'S PET GROOMING SERVICE and its groomers will not be held liable. The pet owner assumes responsibility for any stress or complications that arise during or after grooming, especially with older or sensitive pets.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">5. Enclosures</h5>
                                <p>Pets will be placed in grooming enclosures before and after their grooming session for safety and organization.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">6. Aggressive Pets</h5>
                                <p>Owners must inform NEO'S PET GROOMING SERVICE if their pet is aggressive or has a history of biting people, other animals, or groomers. Muzzles may be used if necessary, and they do not harm the pet when properly applied.</p>
                            </section>
                            <section>
                                <h5 class="font-bold text-slate-900">7. Customer Satisfaction</h5>
                                <p>Your satisfaction is important to us. If you're not satisfied or need adjustments, let us know when you pick up your pet. Once you take your pet home, you may return for adjustments within 24 hours of the appointment. After that, a grooming fee will apply.</p>
                            </section>
                            <p>NEO'S PET GROOMING SERVICE may take pictures of your pet before and after grooming for promotional or educational use.</p>
                            <p>By checking the agreement box below, you confirm that you understand and agree to all the terms listed. You release NEO'S PET GROOMING SERVICE, its staff, and owners from liability related to grooming services or any injuries/damages resulting during your pet's stay.</p>
                            <aside class="rounded-md border border-amber-200 bg-amber-50 p-3 text-slate-900">
                                <h5 class="font-bold">NOTE</h5>
                                <p class="font-bold">We will notify you via call or text once grooming is complete. If you don't pick up your pet within 2 hours, a daycare fee of ₱200 will apply.</p>
                            </aside>
                        </article>

                        <div>
                            <label for="waiver_date" class="mb-1 block text-xs font-semibold text-slate-600">Date</label>
                            <input id="waiver_date" type="text" value="{{ now()->format('F j, Y') }}" readonly class="w-full rounded-lg border-slate-300 bg-slate-50 text-sm text-slate-600">
                        </div>

                        <div>
                            <label for="waiver_photo" class="mb-1 block text-xs font-semibold text-slate-600">Upload a photo of the signed waiver (optional)</label>
                            <input id="waiver_photo" name="waiver_photo" type="file" accept="image/*" @change="waiverFileName=$event.target.files[0]?.name || ''" class="block w-full rounded-lg border border-slate-300 text-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                            <p class="mt-1 text-xs text-slate-500" x-text="waiverFileName || 'Attach a photo of the signed paper waiver if available.'"></p>
                        </div>

                        <label class="flex items-start gap-3 rounded-xl border-2 border-emerald-200 bg-emerald-50 p-4">
                            <input type="checkbox" name="waiver_confirmed" value="1" x-model="waiverConfirmed" required class="mt-1 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                            <span class="text-sm font-semibold text-slate-900">I have read and agree to this Consent to Groom &amp; General Liability Waiver. *</span>
                        </label>
                    </div>

                    <div x-ref="wizard-step-1" x-show="step === 1" x-cloak class="scroll-mt-24 space-y-5 p-4 sm:p-5">
                        <section class="rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="mb-4 rounded-lg bg-emerald-950 px-4 py-3 text-white">
                                <h3 class="font-semibold text-white">Choose a service for each pet</h3>
                                <p class="mt-1 text-xs text-emerald-100/80">Dog prices vary by size. Cats and other pets use the configured Any-size price.</p>
                            </div>
                            <div class="mb-4 max-w-sm">
                                <label class="text-xs font-semibold text-slate-600">Pet type *<select x-model="species" @change="changeMainSpecies($event.target.value)" required class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option>Dog</option><option>Cat</option><option>Other</option></select></label>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-3">
                                <template x-for="serviceName in serviceNamesFor(species)" :key="serviceName">
                                    <section class="group rounded-lg border border-slate-200 p-3 transition hover:border-emerald-500 hover:bg-emerald-50/50" :class="expandedMainServiceName === serviceName ? 'border-emerald-700 bg-emerald-50' : ''">
                                        <button type="button" @mouseenter="hoveredMainServiceName = serviceName" @mouseleave="hoveredMainServiceName = ''" @focus="hoveredMainServiceName = serviceName" @blur="hoveredMainServiceName = ''" @click="toggleMainService(serviceName)" :aria-expanded="expandedMainServiceName === serviceName" class="flex min-h-10 w-full items-center justify-between gap-3 text-left text-sm font-semibold text-slate-900">
                                            <span x-text="serviceName"></span><span aria-hidden="true" class="text-slate-500" x-text="expandedMainServiceName === serviceName ? '−' : '+'"></span>
                                        </button>
                                        <p x-show="hoveredMainServiceName === serviceName" x-cloak class="mt-2 text-xs leading-5 text-slate-600" x-text="serviceVariantsFor(species, serviceName).find((service) => service.description)?.description || 'Choose this service to see available sizes and prices.'"></p>
                                        <div x-show="expandedMainServiceName === serviceName" x-cloak class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            <template x-for="service in serviceVariantsFor(species, serviceName)" :key="service.id">
                                                <label class="flex min-h-11 cursor-pointer items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm transition hover:border-emerald-500 has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50">
                                                    <span class="flex min-w-0 flex-1 items-center gap-3"><input type="radio" name="service_id" :value="service.id" x-model="serviceId" @change="selectService(service.id)" required class="border-slate-300 text-emerald-700 focus:ring-emerald-700"><span class="whitespace-nowrap font-medium text-slate-700" x-text="service.size === 'Any' ? 'Any size' : service.size"></span></span>
                                                    <span class="shrink-0 font-bold text-slate-900" x-text="`₱${Number(service.rate).toFixed(2)}`"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </section>
                                </template>
                                <p x-show="serviceNamesFor(species).length === 0" class="text-sm text-slate-500">No package is configured for this pet type. Ask a manager to add the price in Services.</p>
                            </div>

                            <div class="mt-5 border-t border-slate-200 pt-4">
                                <div class="mb-3">
                                    <h4 class="font-semibold text-slate-900" x-text="`Add-ons for ${petName || 'this pet'}`"></h4>
                                    <p class="mt-1 text-xs text-slate-500">Optional extras configured for the selected service.</p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <fieldset class="min-w-0 rounded-xl border border-amber-200 bg-amber-50/60 p-3.5">
                                        <legend class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-amber-900">Detangling · choose one</legend>
                                        <div class="space-y-2">
                                            <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-100">
                                                <input type="radio" name="detangling_addon_id" value="" x-model="detanglingAddonId" class="border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                                <span class="text-sm font-medium text-slate-800">No detangling</span>
                                            </label>
                                            <template x-for="addon in detanglingAddons()" :key="addon.id">
                                                <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-100">
                                                    <input type="radio" name="detangling_addon_id" :value="addon.id" x-model="detanglingAddonId" class="border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                                    <span class="min-w-0 flex-1 text-sm font-medium text-slate-800" x-text="addon.name"></span>
                                                    <span class="shrink-0 text-xs font-semibold text-slate-600" x-text="`₱${Number(addon.rate).toFixed(2)}`"></span>
                                                </label>
                                            </template>
                                            <p x-show="detanglingAddons().length === 0" class="text-xs text-slate-500">No detangling levels configured.</p>
                                        </div>
                                    </fieldset>
                                    <fieldset class="min-w-0 rounded-xl border border-emerald-200 bg-emerald-50/50 p-3.5">
                                        <legend class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-emerald-900">Extra services · choose any</legend>
                                        <div class="space-y-2">
                                            <template x-for="addon in availableAddons()" :key="addon.id">
                                                <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 transition has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-100">
                                                    <input type="checkbox" name="add_on_ids[]" :value="addon.id" x-model="addOnIds" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                                                    <span class="min-w-0 flex-1 text-sm font-medium text-slate-800" x-text="addon.name"></span>
                                                    <span class="shrink-0 text-xs font-semibold text-slate-600" x-text="`₱${Number(addon.rate).toFixed(2)}`"></span>
                                                </label>
                                            </template>
                                            <p x-show="serviceId && availableAddons().length === 0" class="text-xs text-slate-500">No add-ons are configured for this service.</p>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <template x-for="(pet, index) in additionalPets" :key="`pet-package-${index}`">
                                <section class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <h4 class="text-sm font-semibold text-slate-900" x-text="`Package for ${pet.name || `Pet ${index + 2}`}`"></h4>
                                        <button type="button" @click.stop="removePetRecord(index)" :aria-label="`Remove ${pet.name || `Pet ${index + 2}`} from visit`" title="Remove pet from visit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-red-300 bg-red-50 text-red-700 shadow-sm transition hover:border-red-400 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-1">
                                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                    <div class="mb-4 max-w-sm">
                                        <label class="text-xs font-semibold text-slate-600">Pet type *<select x-model="pet.species" @change="changeAdditionalSpecies(pet, $event.target.value)" required class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option>Dog</option><option>Cat</option><option>Other</option></select></label>
                                    </div>
                                    <div class="mt-3 grid grid-cols-1 gap-2">
                                        <template x-for="serviceName in serviceNamesFor(pet.species)" :key="`pet-${index}-${serviceName}`">
                                                    <section class="group rounded-lg border border-slate-200 bg-white p-3 transition hover:border-emerald-500 hover:bg-emerald-50/50" :class="pet.expanded_service_name === serviceName ? 'border-emerald-700 bg-emerald-50' : ''">
                                                        <button type="button" @mouseenter="pet.hovered_service_name = serviceName" @mouseleave="pet.hovered_service_name = ''" @focus="pet.hovered_service_name = serviceName" @blur="pet.hovered_service_name = ''" @click="if (pet.expanded_service_name === serviceName) { pet.expanded_service_name = '' } else { pet.expanded_service_name = serviceName; if (pet.service_name !== serviceName) { pet.service_name = serviceName; pet.service_id = ''; pet.add_on_ids = []; pet.detangling_addon_id = ''; filterAddons() } }" :aria-expanded="pet.expanded_service_name === serviceName" class="flex min-h-9 w-full items-center justify-between gap-2 text-left text-sm font-semibold text-slate-900">
                                                            <span x-text="serviceName"></span><span aria-hidden="true" class="text-slate-500" x-text="pet.expanded_service_name === serviceName ? '−' : '+'"></span>
                                                        </button>
                                                        <p x-show="pet.hovered_service_name === serviceName" x-cloak class="mt-2 text-xs leading-5 text-slate-600" x-text="serviceVariantsFor(pet.species, serviceName).find((service) => service.description)?.description || 'Choose this service to see available sizes and prices.'"></p>
                                                        <div x-show="pet.expanded_service_name === serviceName" x-cloak class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                    <template x-for="service in serviceVariantsFor(pet.species, serviceName)" :key="`pet-${index}-service-${service.id}`">
                                                                <label class="flex min-h-11 cursor-pointer items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm transition hover:border-emerald-500 has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50">
                                                                    <span class="flex min-w-0 flex-1 items-center gap-3"><input type="radio" x-bind:name="`additional_pets[${index}][service_id]`" :value="service.id" x-model="pet.service_id" @change="pet.weight_category = service.size; filterAddons()" required class="border-slate-300 text-emerald-700 focus:ring-emerald-700"><span class="whitespace-nowrap font-medium text-slate-700" x-text="service.size === 'Any' ? 'Any size' : service.size"></span></span>
                                                                    <span class="shrink-0 font-bold text-slate-900" x-text="`₱${Number(service.rate).toFixed(2)}`"></span>
                                                                </label>
                                                    </template>
                                                </div>
                                            </section>
                                        </template>
                                        <p x-show="serviceNamesFor(pet.species).length === 0" class="text-sm text-slate-500">No package is configured for this pet type.</p>
                                    </div>
                                    <section x-show="pet.service_id" class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                                        <h5 class="text-sm font-semibold text-slate-900" x-text="`Add-ons for ${pet.name || `Pet ${index + 2}`}`"></h5>
                                        <p class="mt-1 text-xs text-slate-500">Choose optional extras for this pet.</p>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <fieldset class="min-w-0 rounded-xl border border-amber-200 bg-amber-50/60 p-3">
                                                <legend class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-amber-900">Detangling · choose one</legend>
                                                <div class="space-y-2">
                                                    <label class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-100"><input type="radio" x-bind:name="`additional_pets[${index}][detangling_addon_id]`" value="" x-model="pet.detangling_addon_id" class="border-slate-300 text-amber-700"><span>No detangling</span></label>
                                                    <template x-for="addon in additionalPetAddons(pet, 'detangling')" :key="`pet-${index}-detangle-${addon.id}`">
                                                        <label class="flex min-h-10 cursor-pointer items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-100"><span class="flex min-w-0 items-center gap-2"><input type="radio" x-bind:name="`additional_pets[${index}][detangling_addon_id]`" :value="addon.id" x-model="pet.detangling_addon_id" class="border-slate-300 text-amber-700"><span x-text="addon.name"></span></span><span class="shrink-0 font-semibold" x-text="`₱${Number(addon.rate).toFixed(2)}`"></span></label>
                                                    </template>
                                                    <p x-show="additionalPetAddons(pet, 'detangling').length === 0" class="text-xs text-slate-500">No detangling options.</p>
                                                </div>
                                            </fieldset>
                                            <fieldset class="min-w-0 rounded-xl border border-emerald-200 bg-emerald-50/50 p-3">
                                                <legend class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-emerald-900">Extra services · choose any</legend>
                                                <div class="space-y-2">
                                                    <template x-for="addon in additionalPetAddons(pet, 'extra_service')" :key="`pet-${index}-addon-${addon.id}`">
                                                        <label class="flex min-h-10 cursor-pointer items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs transition has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-100"><span class="flex min-w-0 items-center gap-2"><input type="checkbox" x-bind:name="`additional_pets[${index}][add_on_ids][]`" :value="addon.id" x-model="pet.add_on_ids" class="rounded border-slate-300 text-emerald-700"><span x-text="addon.name"></span></span><span class="shrink-0 font-semibold" x-text="`₱${Number(addon.rate).toFixed(2)}`"></span></label>
                                                    </template>
                                                    <p x-show="additionalPetAddons(pet, 'extra_service').length === 0" class="text-xs text-slate-500">No extra services configured.</p>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </section>
                                </section>
                            </template>
                            <button type="button" @click="addPetRecord()" class="mt-4 inline-flex min-h-10 items-center rounded-md border border-emerald-800 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 hover:bg-emerald-50">Add another pet</button>
                        </section>

                        <section class="rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-3 rounded-lg bg-emerald-950 px-4 py-3 text-white">
                                <h3 class="font-semibold text-white">Groomer assignment</h3>
                            </div>
                            <label for="groomer_name" class="mb-1 block text-xs font-semibold text-slate-600">Groomer name</label>
                            <input id="groomer_name" name="groomer_name" type="text" x-model="groomerName" autocomplete="off" placeholder="Enter groomer name" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                        </section>

                        <section class="rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-3 rounded-lg bg-emerald-950 px-4 py-3 text-white">
                                <h3 class="font-semibold text-white">Visit notes</h3>
                            </div>
                            <label for="notes" class="mb-1 block text-xs font-semibold text-slate-600">Grooming notes</label>
                            <textarea id="notes" name="notes" x-model="notes" maxlength="5000" rows="3" placeholder="Style request or special instructions" class="w-full resize-y rounded-md border-slate-300 text-sm focus:border-emerald-700 focus:ring-emerald-700"></textarea>
                        </section>
                    </div>

                    <div x-ref="wizard-step-4" x-show="step === 4" x-cloak class="scroll-mt-24 space-y-5 p-4 sm:p-5">
                        <div class="rounded-xl border border-emerald-900 bg-emerald-950 px-4 py-4 text-white shadow-sm sm:px-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-200">Final review</p>
                            <h3 class="mt-1 text-lg font-bold text-white">Review grooming transaction</h3>
                            <p class="mt-1 text-sm text-emerald-100/80">Confirm these details before proceeding to payment.</p>
                        </div>
                        <dl class="divide-y divide-slate-100 overflow-hidden rounded-xl border-2 border-l-4 border-l-emerald-800 border-slate-200 bg-white shadow-sm">
                            <div class="grid grid-cols-[130px_minmax(0,1fr)_120px] items-center gap-3 px-4 py-2 text-sm">
                                <dt class="text-slate-500">Customer</dt>
                                <dd class="break-words font-semibold text-slate-900" x-text="customerName || '—'"></dd>
                                <dd class="flex h-full items-center justify-end self-stretch"><button type="button" @click="goToStep(3)" class="my-auto inline-flex items-center gap-1.5 rounded-md border border-emerald-700/20 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-800 transition hover:border-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M13.25 3.5l3.25 3.25-8.5 8.5-3.5.75.75-3.5 8.5-8.5Z"/><path d="M11.5 4.75l3.75 3.75"/></svg>Edit</button></dd>
                            </div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 px-4 py-2 text-sm"><dt class="text-slate-500">Phone</dt><dd class="break-words text-slate-800" x-text="customerPhone || '—'"></dd></div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 px-4 py-2 text-sm"><dt class="text-slate-500">Email</dt><dd class="break-words text-slate-800" x-text="customerEmail || '—'"></dd></div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 px-4 py-2 text-sm"><dt class="text-slate-500">Address</dt><dd class="break-words text-slate-800" x-text="customerAddress || '—'"></dd></div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)_120px] items-stretch gap-3 px-4 py-2 text-sm">
                                <dt class="text-slate-500">Pet</dt>
                                <dd class="break-words text-slate-800">
                                    <div class="leading-6"><span class="font-semibold text-slate-900">Pet 1:</span> <span x-text="petName ? `${petName} · ${species} · ${species === 'Dog' ? petSize : species === 'Cat' ? 'Any size' : petProfileSize} · ${breed || 'Breed not specified'} · ${sex || 'Gender not specified'}` : '—'"></span></div>
                                    <template x-for="(pet, index) in additionalPets" :key="pet.pet_id || `additional-pet-${index}`">
                                        <div class="leading-6"><span class="font-semibold text-slate-900" x-text="`Pet ${index + 2}:`"></span> <span x-text="`${pet.name || 'Unnamed pet'} · ${pet.species || 'Type not selected'} · ${pet.species === 'Dog' ? pet.weight_category : pet.species === 'Cat' ? 'Any size' : pet.profile_size} · ${pet.breed || 'Breed not specified'} · ${pet.sex || 'Gender not specified'}`"></span></div>
                                    </template>
                                </dd>
                                <dd class="flex items-center justify-end" style="align-self: center"><button type="button" @click="goToStep(3)" class="inline-flex items-center gap-1.5 rounded-md border border-emerald-700/20 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-800 transition hover:border-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M13.25 3.5l3.25 3.25-8.5 8.5-3.5.75.75-3.5 8.5-8.5Z"/><path d="M11.5 4.75l3.75 3.75"/></svg>Edit</button></dd>
                            </div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)_120px] items-center gap-3 px-4 py-2 text-sm">
                                <dt class="text-slate-500">Waiver</dt>
                                <dd class="text-slate-800" x-text="waiverConfirmed ? 'Consent confirmed' : 'Not confirmed'"></dd>
                                <dd class="flex h-full items-center justify-end self-stretch"><button type="button" @click="goToStep(2)" class="my-auto inline-flex items-center gap-1.5 rounded-md border border-emerald-700/20 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-800 transition hover:border-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M13.25 3.5l3.25 3.25-8.5 8.5-3.5.75.75-3.5 8.5-8.5Z"/><path d="M11.5 4.75l3.75 3.75"/></svg>Edit</button></dd>
                            </div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)_120px] items-start gap-3 px-4 py-2 text-sm">
                                <dt class="text-slate-500">Service</dt>
                                <dd class="break-words font-semibold text-slate-900">
                                    <template x-for="entry in petServiceSummary" :key="entry.label">
                                        <div class="leading-6" x-text="entry.label"></div>
                                    </template>
                                </dd>
                                <dd class="flex h-full items-center justify-end self-stretch"><button type="button" @click="goToStep(1)" class="my-auto inline-flex items-center gap-1.5 rounded-md border border-emerald-700/20 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-800 transition hover:border-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M13.25 3.5l3.25 3.25-8.5 8.5-3.5.75.75-3.5 8.5-8.5Z"/><path d="M11.5 4.75l3.75 3.75"/></svg>Edit</button></dd>
                            </div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)_120px] items-start gap-3 px-4 py-2 text-sm">
                                <dt class="text-slate-500">Add-ons</dt>
                                <dd class="break-words text-slate-800">
                                    <template x-for="entry in petAddonSummary" :key="entry.label">
                                        <div class="leading-6" x-text="entry.label"></div>
                                    </template>
                                </dd>
                                <dd class="flex h-full items-center justify-end self-stretch"><button type="button" @click="goToStep(1)" class="my-auto inline-flex items-center gap-1.5 rounded-md border border-emerald-700/20 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-800 transition hover:border-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M13.25 3.5l3.25 3.25-8.5 8.5-3.5.75.75-3.5 8.5-8.5Z"/><path d="M11.5 4.75l3.75 3.75"/></svg>Edit</button></dd>
                            </div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 px-4 py-2 text-sm"><dt class="text-slate-500">Groomer</dt><dd class="text-slate-800" x-text="groomerName.trim() || 'Not assigned'"></dd></div>
                            <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 px-4 py-2 text-sm"><dt class="text-slate-500">Notes</dt><dd class="break-words text-slate-800" x-text="notes || 'None'"></dd></div>
                            <div class="flex items-center justify-between gap-3 bg-emerald-950 px-4 py-4 text-white"><dt class="text-sm font-semibold text-emerald-100">Estimated service total</dt><dd class="text-xl font-bold text-white">₱<span x-text="serviceTotal().toFixed(2)"></span></dd></div>
                        </dl>
                        <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-950">The request will enter the grooming queue as Pending. Choose to pay now or collect payment later from the queue.</p>
                    </div>

                    <div class="sticky bottom-0 z-10 flex shrink-0 items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-5 py-3 shadow-[0_-8px_20px_rgba(27,41,33,0.06)] backdrop-blur sm:px-6">
                        <button type="button" x-show="step > 1" @click="back()" class="min-h-11 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Back</button>
                        <a x-show="step === 1" href="{{ auth()->user()->isCustomer() ? route('pos.index') : route('grooming.index') }}" class="inline-flex min-h-11 items-center rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                        <span></span>
                        <button type="button" x-show="step === 1" @click="next()" :disabled="!serviceSelectionReady" class="min-h-11 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-40">Next: Waiver</button>
                        <button type="button" x-show="step === 2" @click="next()" :disabled="!waiverConfirmed" class="min-h-11 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-40">Next: Customer &amp; Pet</button>
                        <button type="button" x-show="step === 3" @click="next()" :disabled="!customerPetReady" class="min-h-11 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-40">Next: Summary</button>
                        <div x-show="step === 4" class="flex flex-wrap justify-end gap-2">
                            <button type="submit" name="payment_action" value="pay_later" class="min-h-11 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Pay Later and Continue</button>
                            <button type="submit" name="payment_action" value="pay_now" class="min-h-11 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900">Continue to Payment</button>
                        </div>
                    </div>
                </section>

            </div>
        </form>
        </section>

        <div x-show="showQueue || showServiceSummary" x-cloak class="flex w-full flex-col gap-3 lg:w-72">
                @unless(auth()->user()->isCustomer())
                <aside x-show="showQueue" x-cloak>
                    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Today's Grooming Queue</h2>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $queue->count() }} open {{ Str::plural('appointment', $queue->count()) }}</p>
                            </div>
                            <a href="{{ route('grooming.index') }}" aria-label="View all grooming appointments" title="View all appointments" class="text-lg font-semibold text-emerald-800">→</a>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse($queue as $appointment)
                                <div class="flex items-center gap-2.5 px-3 py-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-sm font-bold text-emerald-800">{{ mb_substr($appointment->pet->name, 0, 1) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $appointment->pet->name }} <span class="font-normal text-slate-500">· {{ $appointment->customer->name }}</span></p>
                                        <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ $appointment->service?->name ?? 'Service not selected' }}{{ $appointment->assignedGroomerName() ? ' · ' . $appointment->assignedGroomerName() : ' · Unassigned' }}</p>
                                    </div>
                                    <span class="ml-auto shrink-0 rounded-sm bg-emerald-50 px-2 py-1 text-[10px] font-semibold capitalize text-emerald-800">{{ str_replace('_', ' ', $appointment->status) }}</span>
                                </div>
                            @empty
                                <p class="px-4 py-6 text-sm text-slate-500">No open grooming requests.</p>
                            @endforelse
                        </div>
                        <a href="{{ route('grooming.index') }}" class="flex items-center gap-2 border-t border-slate-100 px-4 py-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">View all appointments <span aria-hidden="true">→</span></a>
                    </section>
                </aside>
                @endunless

                <aside x-show="showServiceSummary" x-cloak>
                    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <h2 class="text-sm font-bold text-slate-900">Service Summary</h2>
                        <div class="mt-3 border-b border-slate-100 pb-3 text-sm">
                            <div class="flex items-start justify-between gap-3"><span class="text-slate-500">Service</span><span class="text-right font-medium text-slate-800" x-text="serviceLabel()"></span></div>
                            <div class="mt-2 flex items-start justify-between gap-3"><span class="text-slate-500">Add-ons</span><span class="text-right text-slate-800" x-text="addonLabels().join(', ') || 'None'"></span></div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3"><span class="text-sm font-semibold text-slate-700">Estimated total</span><span class="text-lg font-bold text-emerald-900">₱<span x-text="serviceTotal().toFixed(2)"></span></span></div>
                        <div class="mt-4 rounded-md bg-emerald-50 px-3 py-3 text-xs leading-5 text-emerald-950">
                            <p class="font-semibold" x-text="waiverConfirmed ? 'Waiver confirmed' : 'Waiver pending'"></p>
                            <p class="mt-1 text-emerald-900" x-text="groomerName.trim() ? `Assigned to ${groomerName.trim()}` : 'No groomer assigned'"></p>
                        </div>
                    </section>
                </aside>
        </div>
        </div>
    </div>
</div>
@endsection
