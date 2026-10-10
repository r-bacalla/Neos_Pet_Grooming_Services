@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')

@php
    $requestedPet = $customer->pets->firstWhere('id', (int) request('edit_pet'));
    $loyaltyVisits = (int) ($customer->loyalty_visits ?? 0);
    $petEditData = [
        'id' => (string) ($requestedPet?->id ?? ''),
        'name' => $requestedPet?->name ?? '',
        'species' => $requestedPet?->species ?? '',
        'breed' => $requestedPet?->breed ?? '',
        'sex' => $requestedPet?->sex ?? '',
        'birth_date' => optional($requestedPet?->birth_date)->format('Y-m-d') ?? '',
        'weight_category' => $requestedPet?->weight_category ?? '',
        'notes' => $requestedPet?->notes ?? '',
    ];

    if (old('_form') === 'pet-edit') {
        $petEditData = [
            'id' => (string) old('pet_id', ''),
            'name' => old('name', ''),
            'species' => old('species', ''),
            'breed' => old('breed', ''),
            'sex' => old('sex', ''),
            'birth_date' => old('birth_date', ''),
            'weight_category' => old('weight_category', ''),
            'notes' => old('notes', ''),
        ];
    }
@endphp

<div class="space-y-6" x-data="{
    customerEditOpen: @js($errors->any() && old('_form') === 'customer-edit'),
    customerEdit: {
        name: @js(old('_form') === 'customer-edit' ? old('name', $customer->name) : $customer->name),
        phone: @js(old('_form') === 'customer-edit' ? old('phone', $customer->phone ?? '') : ($customer->phone ?? '')),
        email: @js(old('_form') === 'customer-edit' ? old('email', $customer->email ?? '') : ($customer->email ?? '')),
        address: @js(old('_form') === 'customer-edit' ? old('address', $customer->address ?? '') : ($customer->address ?? '')),
    },
    addPetOpen: @js($errors->any() && old('_form') === 'pet-create'),
    editPetOpen: @js(($errors->any() && old('_form') === 'pet-edit') || $requestedPet !== null),
    petEdit: @js($petEditData),
    openPetEdit(pet) { this.petEdit = { ...pet }; this.editPetOpen = true; },
}" @keydown.escape.window="customerEditOpen = false; addPetOpen = false; editPetOpen = false">

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

        <div class="flex items-center gap-3">

            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-xl font-bold text-white shadow-sm">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">

                    <h1 class="text-2xl font-bold tracking-tight text-white">
                        {{ $customer->name }}
                    </h1>

                    <span class="rounded-full bg-amber-300 px-2.5 py-1 text-xs font-bold text-emerald-950">
                        Customer
                    </span>

                </div>

                <p class="mt-1 text-sm text-emerald-100/80">
                    Customer profile, pets, grooming history, purchases, and loyalty.
                </p>
            </div>

        </div>


        <div class="flex flex-col gap-2 sm:flex-row">

            <button
                type="button"
                @click="customerEditOpen = true"
                class="inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-emerald-700 px-3 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-emerald-950"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                </svg>

                Edit Customer
            </button>

            <a
                href="{{ route('customers.index') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M19 12H5"></path>
                    <path d="m12 19-7-7 7-7"></path>
                </svg>

                Back
            </a>

        </div>

    </div>


    {{-- =========================================================
        SUMMARY CARDS
    ========================================================== --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">

        {{-- Pets --}}
        <div class="rounded-xl border border-slate-200 border-t-2 border-t-emerald-700 bg-white p-4 shadow-sm sm:p-5">

            <div class="flex flex-col items-start gap-2">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Pets
                    </p>

                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ $customer->pets->count() }}
                    </p>
                </div>

                <span class="text-xs font-semibold text-slate-400">RECORDS</span>

            </div>

        </div>


        {{-- Grooming --}}
        <div class="rounded-xl border border-slate-200 border-t-2 border-t-emerald-700 bg-white p-4 shadow-sm sm:p-5">

            <div class="flex flex-col items-start gap-2">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Grooming
                    </p>

                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ $customer->groomingAppointments->count() }}
                    </p>
                </div>

                <span class="text-xs font-semibold text-slate-400">VISITS</span>

            </div>

        </div>


        {{-- Purchases --}}
        <div class="rounded-xl border border-slate-200 border-t-2 border-t-emerald-700 bg-white p-4 shadow-sm sm:p-5">

            <div class="flex flex-col items-start gap-2">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Purchases
                    </p>

                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ $customer->sales->count() }}
                    </p>
                </div>

                <span class="text-xs font-semibold text-slate-400">SALES</span>

            </div>

        </div>


        {{-- Loyalty --}}
        <div class="rounded-xl border border-amber-200 border-t-2 border-t-amber-500 bg-amber-50 p-4 shadow-sm sm:p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-600">
                        Loyalty Visits
                    </p>

                    <p class="mt-1 text-2xl font-bold text-amber-700">
                        {{ $loyaltyVisits }}
                    </p>
                    <div class="mt-3 space-y-1.5 text-xs">
                        <p class="flex justify-between gap-2"><span class="font-semibold text-amber-900">5 visits · 20% off</span><span class="text-amber-800">{{ $loyaltyVisits >= 5 ? 'Reached' : (($remainingVisits = 5 - $loyaltyVisits) === 1 ? '1 visit to go' : $remainingVisits.' visits to go') }}</span></p>
                        <p class="flex justify-between gap-2"><span class="font-semibold text-amber-900">10 visits · 100% off</span><span class="text-amber-800">{{ $loyaltyVisits >= 10 ? 'Reached' : (($remainingVisits = 10 - $loyaltyVisits) === 1 ? '1 visit to go' : $remainingVisits.' visits to go') }}</span></p>
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        CUSTOMER INFORMATION
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Customer Information
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        Contact and account information.
                    </p>
                </div>

            </div>

        </div>


        <div class="p-5 sm:p-6">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Name --}}
                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Name
                    </p>

                    <p class="mt-1.5 font-semibold text-slate-900">
                        {{ $customer->name }}
                    </p>

                </div>


                {{-- Phone --}}
                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Phone
                    </p>

                    <p class="mt-1.5 font-semibold text-slate-900">
                        {{ $customer->phone ?: '—' }}
                    </p>

                </div>


                {{-- Email --}}
                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Email
                    </p>

                    <p class="mt-1.5 break-words font-semibold text-slate-900">
                        {{ $customer->email ?: '—' }}
                    </p>

                </div>


                {{-- Address --}}
                <div class="rounded-xl bg-slate-50 p-4 sm:col-span-2 lg:col-span-3">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Address
                    </p>

                    <p class="mt-1.5 font-semibold text-slate-900">
                        {{ $customer->address ?: '—' }}
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        PETS
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Pets
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        {{ $customer->pets->count() }}
                        registered pet{{ $customer->pets->count() == 1 ? '' : 's' }}.
                    </p>
                </div>

            </div>

            @if(!auth()->user()->isFrontDesk())
            <button type="button" @click="addPetOpen = true" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                <span class="text-lg leading-none">+</span>
                Add Pet
            </button>
            @endif

        </div>


        @if($customer->pets->count())

            {{-- Desktop --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-full">

                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Pet
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Species
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Breed
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Sex
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Size
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @foreach($customer->pets as $pet)

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-lg">
                                            
                                        </div>

                                        <div>
                                            <div class="font-semibold text-slate-900">
                                                {{ $pet->name }}
                                            </div>

                                            <div class="text-xs text-slate-500">
                                                Pet
                                            </div>
                                        </div>

                                    </div>

                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $pet->species ?: '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $pet->breed ?: '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $pet->sex ?: '—' }}
                                </td>

                                <td class="px-6 py-4">

                                    @if($pet->species === 'Cat')
                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-sm font-semibold text-slate-700">Not required</span>
                                    @elseif($pet->weight_category)

                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-sm font-semibold text-slate-700">
                                            {{ $pet->weight_category }}
                                        </span>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            —
                                        </span>

                                    @endif

                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        @if(!auth()->user()->isFrontDesk())
                                        <button type="button" @click="openPetEdit(@js(['id' => (string) $pet->id, 'name' => $pet->name, 'species' => $pet->species ?? '', 'breed' => $pet->breed ?? '', 'sex' => $pet->sex ?? '', 'birth_date' => optional($pet->birth_date)->format('Y-m-d') ?? '', 'weight_category' => $pet->weight_category ?? '', 'notes' => $pet->notes ?? '']))" class="record-action-secondary rounded-lg px-3 py-2 text-sm font-semibold">
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('pets.destroy', $pet) }}"
                                            onsubmit="return confirm('Move this pet to Archive? Its grooming history will be kept.');"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="record-action-danger rounded-lg px-3 py-2 text-sm font-semibold"
                                            >
                                                Delete
                                            </button>

                                        </form>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @foreach($customer->pets as $pet)

                    <div class="p-5">

                        <div class="flex items-start justify-between gap-3">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-lg">
                                    
                                </div>

                                <div>

                                    <h3 class="font-bold text-slate-900">
                                        {{ $pet->name }}
                                    </h3>

                                    <p class="text-sm text-slate-500">
                                        {{ $pet->species ?: 'Unknown species' }}
                                    </p>

                                </div>

                            </div>

                            @if($pet->species !== 'Cat' && $pet->weight_category)

                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                    {{ $pet->weight_category }}
                                </span>

                            @endif

                        </div>


                        <div class="mt-4 grid grid-cols-2 gap-2">

                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Breed
                                </p>

                                <p class="mt-1 truncate text-sm font-medium text-slate-700">
                                    {{ $pet->breed ?: '—' }}
                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Sex
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-700">
                                    {{ $pet->sex ?: '—' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-4 grid grid-cols-2 gap-2">

                            @unless(auth()->user()->isFrontDesk())
                            <button type="button" @click="openPetEdit(@js(['id' => (string) $pet->id, 'name' => $pet->name, 'species' => $pet->species ?? '', 'breed' => $pet->breed ?? '', 'sex' => $pet->sex ?? '', 'birth_date' => optional($pet->birth_date)->format('Y-m-d') ?? '', 'weight_category' => $pet->weight_category ?? '', 'notes' => $pet->notes ?? '']))" class="record-action-secondary inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold">
                                Edit Pet
                            </button>

                            <form
                                method="POST"
                                action="{{ route('pets.destroy', $pet) }}"
                                onsubmit="return confirm('Move this pet to Archive? Its grooming history will be kept.');"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="record-action-danger w-full rounded-xl px-4 py-2.5 text-sm font-semibold"
                                >
                                    Delete Pet
                                </button>

                            </form>
                            @endunless

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="px-6 py-14 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                    
                </div>

                <h3 class="mt-4 font-bold text-slate-900">
                    No pets registered
                </h3>

                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Add a pet to keep track of grooming services, size, breed, and history.
                </p>

                @unless(auth()->user()->isFrontDesk())
                    <button type="button" @click="addPetOpen = true" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <span class="text-lg leading-none">+</span>
                        Add First Pet
                    </button>
                @endunless

            </div>

        @endif

    </div>


    <template x-teleport="body">
    <div x-show="customerEditOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="customerEditOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="edit-customer-profile-title" class="my-auto flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex shrink-0 items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Customer records</p><h2 id="edit-customer-profile-title" class="mt-1 text-lg font-bold">Edit Customer</h2></div>
                <button type="button" @click="customerEditOpen = false" aria-label="Close edit customer dialog" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-xl text-white hover:bg-white/20">×</button>
            </header>
            @if($errors->any() && old('_form') === 'customer-edit')
                <div class="mx-5 mt-4 shrink-0 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-semibold">Please check the customer details.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('customers.update', $customer) }}" class="min-h-0 flex-1 flex flex-col">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="customer-edit">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                    <div><label for="profile-edit-customer-name" class="mb-1 block text-sm font-semibold text-slate-700">Customer name *</label><input id="profile-edit-customer-name" name="name" x-model="customerEdit.name" required maxlength="255" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div><label for="profile-edit-customer-phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone</label><input id="profile-edit-customer-phone" name="phone" x-model="customerEdit.phone" maxlength="50" class="w-full rounded-lg border-slate-300 text-sm"></div>
                        <div><label for="profile-edit-customer-email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label><input id="profile-edit-customer-email" type="email" name="email" x-model="customerEdit.email" maxlength="255" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    </div>
                    <div><label for="profile-edit-customer-address" class="mb-1 block text-sm font-semibold text-slate-700">Address *</label><textarea id="profile-edit-customer-address" name="address" x-model="customerEdit.address" required maxlength="1000" rows="3" class="w-full resize-y rounded-lg border-slate-300 text-sm"></textarea></div>
                </div>
                <footer class="flex shrink-0 justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4"><button type="button" @click="customerEditOpen = false" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="min-h-10 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">Save changes</button></footer>
            </form>
        </section>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="addPetOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="addPetOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="add-customer-pet-title" class="my-auto w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">{{ $customer->name }}</p><h2 id="add-customer-pet-title" class="mt-1 text-lg font-bold">Add Pet</h2></div>
                <button type="button" @click="addPetOpen = false" aria-label="Close add pet dialog" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-xl text-white hover:bg-white/20">×</button>
            </header>
            @if($errors->any() && old('_form') === 'pet-create')
                <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-semibold">Please check the pet details.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('pets.store', $customer) }}" class="space-y-4 p-5" x-data="{ species: @js(old('_form') === 'pet-create' ? old('species', '') : ''), petSize: @js(old('_form') === 'pet-create' ? old('weight_category', '') : '') }">
                @csrf
                <input type="hidden" name="_form" value="pet-create">
                <div><label for="add-pet-name" class="mb-1 block text-sm font-semibold text-slate-700">Pet name *</label><input id="add-pet-name" name="name" value="{{ old('_form') === 'pet-create' ? old('name') : '' }}" required maxlength="100" class="w-full rounded-lg border-slate-300 text-sm">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label for="add-pet-species" class="mb-1 block text-sm font-semibold text-slate-700">Pet type *</label><select id="add-pet-species" name="species" x-model="species" required @change="if (species === 'Cat') petSize = ''" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select type</option>@foreach(['Dog', 'Cat', 'Other'] as $species)<option value="{{ $species }}">{{ $species }}</option>@endforeach</select></div>
                    <div><label for="add-pet-breed" class="mb-1 block text-sm font-semibold text-slate-700">Breed</label><input id="add-pet-breed" name="breed" value="{{ old('_form') === 'pet-create' ? old('breed') : '' }}" maxlength="100" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    <div><label for="add-pet-sex" class="mb-1 block text-sm font-semibold text-slate-700">Sex</label><select id="add-pet-sex" name="sex" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select sex</option><option value="male" @selected(old('_form') === 'pet-create' && old('sex') === 'male')>Male</option><option value="female" @selected(old('_form') === 'pet-create' && old('sex') === 'female')>Female</option></select></div>
                    <div x-show="species && species !== 'Cat'" x-cloak class="sm:col-span-2"><label for="add-pet-size" class="mb-1 block text-sm font-semibold text-slate-700">Size *</label><select id="add-pet-size" name="weight_category" x-model="petSize" :required="species && species !== 'Cat'" :disabled="!species || species === 'Cat'" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select size</option>@foreach(['Small', 'Medium', 'Large', 'XLarge'] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach</select>@error('weight_category')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                </div>
                <div><label for="add-pet-notes" class="mb-1 block text-sm font-semibold text-slate-700">Notes</label><textarea id="add-pet-notes" name="notes" rows="3" class="w-full resize-y rounded-lg border-slate-300 text-sm">{{ old('_form') === 'pet-create' ? old('notes') : '' }}</textarea></div>
                <footer class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" @click="addPetOpen = false" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="min-h-10 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">Save Pet</button></footer>
            </form>
        </section>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="editPetOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="editPetOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="edit-customer-pet-title" class="my-auto w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">{{ $customer->name }}</p><h2 id="edit-customer-pet-title" class="mt-1 text-lg font-bold">Edit Pet</h2></div>
                <button type="button" @click="editPetOpen = false" aria-label="Close edit pet dialog" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-xl text-white hover:bg-white/20">×</button>
            </header>
            @if($errors->any() && old('_form') === 'pet-edit')
                <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-semibold">Please check the pet details.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" :action="'{{ url('/pets') }}/' + petEdit.id" class="space-y-4 p-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="pet-edit">
                <input type="hidden" name="pet_id" x-model="petEdit.id">
                <div><label for="edit-pet-name" class="mb-1 block text-sm font-semibold text-slate-700">Pet name *</label><input id="edit-pet-name" name="name" x-model="petEdit.name" required maxlength="100" class="w-full rounded-lg border-slate-300 text-sm"></div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label for="edit-pet-species" class="mb-1 block text-sm font-semibold text-slate-700">Pet type</label><select id="edit-pet-species" name="species" x-model="petEdit.species" @change="if (petEdit.species === 'Cat') petEdit.weight_category = ''" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select type</option><option>Dog</option><option>Cat</option><option>Other</option></select></div>
                    <div><label for="edit-pet-breed" class="mb-1 block text-sm font-semibold text-slate-700">Breed</label><input id="edit-pet-breed" name="breed" x-model="petEdit.breed" maxlength="100" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    <div><label for="edit-pet-sex" class="mb-1 block text-sm font-semibold text-slate-700">Sex</label><select id="edit-pet-sex" name="sex" x-model="petEdit.sex" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select sex</option><option value="male">Male</option><option value="female">Female</option></select></div>
                    <div><label for="edit-pet-birth-date" class="mb-1 block text-sm font-semibold text-slate-700">Birth date</label><input id="edit-pet-birth-date" type="date" name="birth_date" x-model="petEdit.birth_date" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    <div x-show="petEdit.species && petEdit.species !== 'Cat'" x-cloak class="sm:col-span-2"><label for="edit-pet-size" class="mb-1 block text-sm font-semibold text-slate-700">Size *</label><select id="edit-pet-size" name="weight_category" x-model="petEdit.weight_category" :required="petEdit.species && petEdit.species !== 'Cat'" :disabled="!petEdit.species || petEdit.species === 'Cat'" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select size</option>@foreach(['Small', 'Medium', 'Large', 'XLarge'] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach</select>@error('weight_category')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                </div>
                <div><label for="edit-pet-notes" class="mb-1 block text-sm font-semibold text-slate-700">Notes</label><textarea id="edit-pet-notes" name="notes" x-model="petEdit.notes" rows="3" class="w-full resize-y rounded-lg border-slate-300 text-sm"></textarea></div>
                <footer class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" @click="editPetOpen = false" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="min-h-10 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">Save changes</button></footer>
            </form>
        </section>
    </div>
    </template>

    {{-- =========================================================
        GROOMING HISTORY
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Grooming History
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        Previous grooming appointments.
                    </p>
                </div>

            </div>

        </div>


        @if($customer->groomingAppointments->count())

            <div class="hidden overflow-x-auto xl:block">

            <table class="min-w-[720px] w-full">

                    <thead>

                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Date
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Pet
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Service
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Groomer
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($customer->groomingAppointments->sortByDesc('scheduled_start') as $appointment)

                            @php
                                $status = strtolower($appointment->status ?? '');
                            @endphp

                            <tr class="transition hover:bg-slate-50">

                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700">
                                    {{ optional($appointment->scheduled_start)->format('M d, Y h:i A') }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">
                                    {{ $appointment->pet->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700">
                                    {{ $appointment->service->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700">
                                    {{ $appointment->assignedGroomerName() ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4">

                                    @if($status === 'completed')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Completed
                                        </span>

                                    @elseif($status === 'cancelled')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Cancelled
                                        </span>

                                    @elseif($status === 'in_progress')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                            In Progress
                                        </span>

                                    @elseif($status === 'ready')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Ready
                                        </span>

                                    @elseif($status === 'received')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                            Received
                                        </span>

                                    @elseif($status === 'approved')

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Approved
                                        </span>

                                    @else

                                        <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold capitalize text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            {{ str_replace('_', ' ', $appointment->status) }}
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @foreach($customer->groomingAppointments->sortByDesc('scheduled_start') as $appointment)
                    @php
                        $status = strtolower($appointment->status ?? '');
                        $statusTone = match ($status) {
                            'completed', 'approved' => 'bg-emerald-50 text-emerald-700',
                            'cancelled' => 'bg-red-50 text-red-700',
                            'in_progress' => 'bg-emerald-50 text-emerald-800',
                            'ready' => 'bg-amber-50 text-amber-700',
                            'received' => 'bg-sky-50 text-sky-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <article class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-slate-500">{{ optional($appointment->scheduled_start)->format('M d, Y h:i A') }}</p>
                                <h3 class="mt-1 truncate font-semibold text-slate-900">{{ $appointment->pet->name ?? '—' }}</h3>
                                <p class="mt-0.5 truncate text-sm text-slate-600">{{ $appointment->service->name ?? '—' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $statusTone }}">{{ str_replace('_', ' ', $appointment->status) }}</span>
                        </div>
                        <p class="border-t border-slate-100 pt-2 text-xs text-slate-500">Groomer <span class="ml-1 font-medium text-slate-700">{{ $appointment->assignedGroomerName() ?? '—' }}</span></p>
                    </article>
                @endforeach
            </div>

        @else

            <div class="px-6 py-12 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl">
                    
                </div>

                <h3 class="mt-4 font-semibold text-slate-900">
                    No grooming history
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    No grooming appointments have been recorded for this customer yet.
                </p>

            </div>

        @endif

    </div>


    {{-- =========================================================
        PURCHASE HISTORY
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Purchase History
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        Previous POS purchases and invoices.
                    </p>
                </div>

            </div>

        </div>


        @if($customer->sales->count())

            <div class="hidden overflow-x-auto xl:block">

                <table class="min-w-full">

                    <thead>

                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Invoice
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Date
                            </th>

                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Items
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Total
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Cashier
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($customer->sales->sortByDesc('created_at') as $sale)

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-6 py-4">

                                    <span class="rounded-lg bg-emerald-50 px-2.5 py-1.5 text-sm font-bold text-emerald-800">
                                        {{ $sale->invoice_number }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                    {{ $sale->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-emerald-50 px-2 text-xs font-bold text-emerald-800">
                                        {{ $sale->items->count() }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-right">

                                    <span class="font-bold text-slate-900">
                                        ₱{{ number_format($sale->total, 2) }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $sale->user->name ?? '—' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @foreach($customer->sales->sortByDesc('created_at') as $sale)
                    <article class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="break-all font-semibold text-emerald-800">{{ $sale->invoice_number }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $sale->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <span class="whitespace-nowrap font-bold text-slate-900">₱{{ number_format($sale->total, 2) }}</span>
                        </div>
                        <dl class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-2 text-xs">
                            <div><dt class="font-semibold uppercase tracking-wide text-slate-400">Items</dt><dd class="mt-1 text-slate-700">{{ $sale->items->count() }}</dd></div>
                            <div><dt class="font-semibold uppercase tracking-wide text-slate-400">Cashier</dt><dd class="mt-1 truncate text-slate-700">{{ $sale->user->name ?? '—' }}</dd></div>
                        </dl>
                    </article>
                @endforeach
            </div>

        @else

            <div class="px-6 py-12 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl">
                    
                </div>

                <h3 class="mt-4 font-semibold text-slate-900">
                    No purchases yet
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    No purchases have been recorded for this customer.
                </p>

            </div>

        @endif

    </div>


    {{-- =========================================================
        LOYALTY VOUCHERS
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Loyalty Vouchers
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        Customer rewards and voucher status.
                    </p>

                </div>

            </div>

        </div>


        @if($customer->loyaltyVouchers->count())

            <div class="hidden overflow-x-auto xl:block">

                <table class="min-w-full">

                    <thead>

                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Code
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Discount
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Expires
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($customer->loyaltyVouchers->sortByDesc('created_at') as $voucher)

                            @php
                                $voucherStatus = strtolower($voucher->status ?? '');
                            @endphp

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-6 py-4">

                                    <span class="rounded-lg bg-emerald-50 px-3 py-1.5 text-sm font-bold tracking-wide text-emerald-800">
                                        {{ $voucher->code }}
                                    </span>

                                </td>

                                <td class="px-6 py-4">

                                    <span class="font-bold text-slate-900">
                                        {{ number_format($voucher->discount_percent, 0) }}%
                                    </span>

                                </td>

                                <td class="px-6 py-4">

                                    @if($voucherStatus === 'active')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>

                                    @elseif($voucherStatus === 'used')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Used
                                        </span>

                                    @elseif($voucherStatus === 'expired')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Expired
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold capitalize text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            {{ $voucher->status }}
                                        </span>

                                    @endif

                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ optional($voucher->expires_at)->format('M d, Y') ?: '—' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @foreach($customer->loyaltyVouchers->sortByDesc('created_at') as $voucher)
                    @php
                        $voucherStatus = strtolower($voucher->status ?? '');
                        $voucherTone = match ($voucherStatus) {
                            'active' => 'bg-emerald-50 text-emerald-700',
                            'expired' => 'bg-red-50 text-red-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <article class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="break-all font-semibold text-emerald-800">{{ $voucher->code }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Expires {{ optional($voucher->expires_at)->format('M d, Y') ?: '—' }}</p>
                            </div>
                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $voucherTone }}">{{ $voucher->status }}</span>
                        </div>
                        <div class="border-t border-slate-100 pt-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Discount</p>
                            <p class="mt-0.5 font-bold text-slate-900">{{ number_format($voucher->discount_percent, 0) }}%</p>
                        </div>
                    </article>
                @endforeach
            </div>

        @else

            <div class="px-6 py-12 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl">
                    
                </div>

                <h3 class="mt-4 font-semibold text-slate-900">
                    No loyalty vouchers
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    This customer does not have any loyalty vouchers yet.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection