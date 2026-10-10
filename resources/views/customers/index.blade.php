@extends('layouts.app')

@section('title', 'Customers')

@section('content')

<div class="space-y-6" x-data="{
    addCustomerOpen: @js(($errors->any() && old('_form') === 'customer-create') || request()->boolean('add')),
    viewCustomerOpen: false,
    editCustomerOpen: @js($errors->any() && old('_form') === 'customer-edit'),
    selectedCustomer: {},
    editCustomer: {
        id: @js((string) old('customer_id', '')),
        name: @js(old('_form') === 'customer-edit' ? old('name', '') : ''),
        phone: @js(old('_form') === 'customer-edit' ? old('phone', '') : ''),
        email: @js(old('_form') === 'customer-edit' ? old('email', '') : ''),
        address: @js(old('_form') === 'customer-edit' ? old('address', '') : ''),
    },
    openCustomerView(customer) {
        this.selectedCustomer = customer;
        this.viewCustomerOpen = true;
        this.$nextTick(() => { this.$refs.customerViewBody.scrollTop = 0; });
    },
    openCustomerEdit(customer) { this.editCustomer = { ...customer }; this.viewCustomerOpen = false; this.editCustomerOpen = true; },
}" @keydown.escape.window="addCustomerOpen = false; viewCustomerOpen = false; editCustomerOpen = false">

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Customers
            </h1>
            <p class="mt-1 text-sm text-emerald-100/80">
                Manage customers, pets, grooming history, and loyalty.
            </p>
            </div>

        <button type="button" @click="addCustomerOpen = true; $nextTick(() => $refs.customerName.focus())" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            <span class="text-lg leading-none">+</span>
            Add Customer
        </button>

    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('error') }}</div>
    @endif

    <template x-teleport="body">
    <div x-show="addCustomerOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="addCustomerOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="add-customer-title" class="my-auto w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex shrink-0 items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Customer records</p>
                    <h2 id="add-customer-title" class="mt-1 text-lg font-bold">Add Customer</h2>
                </div>
                <button type="button" @click="addCustomerOpen = false" aria-label="Close add customer dialog" class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20">Close</button>
            </header>

            @if($errors->any())
                <div class="mx-5 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Please check the customer details.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4 p-5">
                @csrf
                <input type="hidden" name="_form" value="customer-create">
                <div>
                    <label for="new-customer-name" class="mb-1 block text-sm font-semibold text-slate-700">Customer name <span class="text-red-600">*</span></label>
                    <input id="new-customer-name" x-ref="customerName" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-emerald-700 focus:bg-white focus:ring-emerald-100">
                    @error('name')<p class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="new-customer-phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
                        <input id="new-customer-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-emerald-700 focus:bg-white focus:ring-emerald-100">
                        @error('phone')<p class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="new-customer-email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input id="new-customer-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-emerald-700 focus:bg-white focus:ring-emerald-100">
                        @error('email')<p class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="new-customer-address" class="mb-1 block text-sm font-semibold text-slate-700">Address <span class="text-red-600">*</span></label>
                    <textarea id="new-customer-address" name="address" rows="2" autocomplete="street-address" required class="w-full resize-y rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-emerald-700 focus:bg-white focus:ring-emerald-100">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>@enderror
                </div>

                <footer class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="addCustomerOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900">Save Customer</button>
                </footer>
            </form>
        </section>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="viewCustomerOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="viewCustomerOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="customer-preview-title" class="my-auto flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Customer profile</p>
                    <h2 id="customer-preview-title" class="mt-1 text-lg font-bold" x-text="selectedCustomer.name"></h2>
                </div>
                <button type="button" @click="viewCustomerOpen = false" aria-label="Close customer details" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-xl text-white hover:bg-white/20">×</button>
            </header>
            <div x-ref="customerViewBody" class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain p-5">
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">Phone</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900" x-text="selectedCustomer.phone || 'Not provided'"></dd></div>
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">Email</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900" x-text="selectedCustomer.email || 'Not provided'"></dd></div>
                    <div class="rounded-lg bg-slate-50 p-3 sm:col-span-2"><dt class="text-xs font-semibold uppercase text-slate-500">Address</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900" x-text="selectedCustomer.address || 'Not provided'"></dd></div>
                </dl>
                <section class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <div class="flex items-center justify-between gap-3"><h3 class="text-sm font-semibold text-amber-900">Loyalty Rewards</h3><span class="text-xs font-semibold text-amber-800" x-text="`${selectedCustomer.loyalty_visits || 0} visits`"></span></div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <article class="rounded-md border border-amber-200 bg-white p-3"><div class="flex items-center justify-between gap-2"><span class="text-xs font-semibold text-slate-600">5 visits</span><strong class="text-sm text-emerald-800">20% off</strong></div><p class="mt-1 text-xs text-slate-500" x-text="selectedCustomer.loyalty_visits >= 5 ? 'Reward milestone reached' : (5 - (selectedCustomer.loyalty_visits || 0) === 1 ? '1 visit to go' : `${5 - (selectedCustomer.loyalty_visits || 0)} visits to go`)"></p></article>
                        <article class="rounded-md border border-amber-200 bg-white p-3"><div class="flex items-center justify-between gap-2"><span class="text-xs font-semibold text-slate-600">10 visits</span><strong class="text-sm text-emerald-800">100% off</strong></div><p class="mt-1 text-xs text-slate-500" x-text="selectedCustomer.loyalty_visits >= 10 ? 'Reward milestone reached' : (10 - (selectedCustomer.loyalty_visits || 0) === 1 ? '1 visit to go' : `${10 - (selectedCustomer.loyalty_visits || 0)} visits to go`)"></p></article>
                    </div>
                </section>
                <section class="rounded-lg border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-200 px-3 py-2.5"><h3 class="text-sm font-semibold text-slate-800">Pets</h3><span class="text-xs text-slate-500" x-text="`${selectedCustomer.pets?.length || 0} registered`"></span></div>
                    <div class="divide-y divide-slate-100">
                        <template x-for="pet in selectedCustomer.pets || []" :key="pet.id">
                            <article class="grid gap-3 p-3 sm:grid-cols-[minmax(0,1fr)_7rem] sm:items-start">
                                <div class="min-w-0 space-y-3">
                                    <div><h4 class="font-semibold text-slate-900" x-text="pet.name"></h4><p class="mt-0.5 text-sm text-slate-500" x-text="pet.species"></p></div>
                                    <dl class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
                                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Breed</dt><dd class="mt-0.5 text-slate-800" x-text="pet.breed"></dd></div>
                                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Sex</dt><dd class="mt-0.5 text-slate-800" x-text="pet.sex"></dd></div>
                                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Size</dt><dd class="mt-0.5 text-slate-800" x-text="pet.size"></dd></div>
                                        <div class="col-span-2 sm:col-span-3"><dt class="text-xs font-semibold uppercase text-slate-500">Notes</dt><dd class="mt-0.5 whitespace-pre-wrap text-slate-800" x-text="pet.notes"></dd></div>
                                    </dl>
                                </div>
                                @unless(auth()->user()->isFrontDesk())
                                <div class="flex gap-2 sm:flex-col">
                                    <a :href="pet.edit_url" class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit Pet</a>
                                    <form method="POST" :action="pet.delete_url" onsubmit="return confirm('Move this pet to Archive? Its grooming history will be kept.');" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Delete Pet</button>
                                    </form>
                                </div>
                                @endunless
                            </article>
                        </template>
                        <p x-show="!selectedCustomer.pets?.length" class="px-3 py-3 text-sm text-slate-500">No pets registered.</p>
                    </div>
                </section>
                <section class="overflow-hidden rounded-lg border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-200 px-3 py-2.5"><h3 class="text-sm font-semibold text-slate-800">Purchase History</h3><span class="text-xs text-slate-500" x-text="`${selectedCustomer.sales?.length || 0} transactions`"></span></div>
                    <div class="divide-y divide-slate-100">
                        <template x-for="sale in selectedCustomer.sales || []" :key="sale.id">
                            <article class="grid grid-cols-1 gap-3 px-3 py-3 text-sm sm:grid-cols-[minmax(0,1fr)_minmax(16rem,1fr)] sm:items-center">
                                <div class="min-w-0"><p class="break-all font-semibold text-slate-900" x-text="sale.invoice"></p><p class="mt-0.5 text-xs text-slate-500" x-text="sale.date"></p></div>
                                <dl class="grid min-w-0 grid-cols-3 gap-2 text-xs sm:gap-3">
                                    <div class="min-w-0"><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Items</dt><dd class="mt-0.5 text-slate-600" x-text="sale.items"></dd></div>
                                    <div class="min-w-0"><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Cashier</dt><dd class="mt-0.5 truncate text-slate-600" x-text="sale.cashier"></dd></div>
                                    <div class="min-w-0 text-right"><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total</dt><dd class="mt-0.5 whitespace-nowrap font-bold text-slate-900" x-text="sale.total"></dd></div>
                                </dl>
                            </article>
                        </template>
                        <p x-show="!selectedCustomer.sales?.length" class="px-3 py-3 text-sm text-slate-500">No purchases have been recorded for this customer.</p>
                    </div>
                </section>
            </div>
            <footer class="flex shrink-0 justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                <button type="button" @click="viewCustomerOpen = false" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Close</button>
                <a :href="selectedCustomer.profile_url" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Full profile</a>
                <button type="button" @click="openCustomerEdit(selectedCustomer)" class="min-h-10 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">Edit customer</button>
            </footer>
        </section>
    </div>
    </template>

    <template x-teleport="body">
    <div x-show="editCustomerOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="editCustomerOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="edit-customer-title" class="my-auto w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 bg-emerald-950 px-5 py-4 text-white">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Customer records</p><h2 id="edit-customer-title" class="mt-1 text-lg font-bold">Edit Customer</h2></div>
                <button type="button" @click="editCustomerOpen = false" aria-label="Close edit customer dialog" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 bg-white/10 text-xl text-white hover:bg-white/20">×</button>
            </header>
            @if($errors->any() && old('_form') === 'customer-edit')
                <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-semibold">Please check the customer details.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" :action="'{{ url('/customers') }}/' + editCustomer.id" class="space-y-4 p-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="customer-edit">
                <input type="hidden" name="customer_id" x-model="editCustomer.id">
                <input type="hidden" name="return_to_index" value="1">
                <div><label for="edit-customer-name" class="mb-1 block text-sm font-semibold text-slate-700">Customer name *</label><input id="edit-customer-name" name="name" x-model="editCustomer.name" required maxlength="255" class="w-full rounded-lg border-slate-300 text-sm">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label for="edit-customer-phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone</label><input id="edit-customer-phone" name="phone" x-model="editCustomer.phone" maxlength="50" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    <div><label for="edit-customer-email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label><input id="edit-customer-email" type="email" name="email" x-model="editCustomer.email" maxlength="255" class="w-full rounded-lg border-slate-300 text-sm"></div>
                </div>
                <div><label for="edit-customer-address" class="mb-1 block text-sm font-semibold text-slate-700">Address *</label><textarea id="edit-customer-address" name="address" x-model="editCustomer.address" required maxlength="1000" rows="3" class="w-full resize-y rounded-lg border-slate-300 text-sm"></textarea></div>
                <footer class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" @click="editCustomerOpen = false" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button><button type="submit" class="min-h-10 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">Save changes</button></footer>
            </form>
        </section>
    </div>
    </template>


    {{-- =========================================================
        SEARCH CARD
    ========================================================== --}}
    <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">

        <form
            method="GET"
            action="{{ route('customers.index') }}"
        >

            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">

                {{-- Search --}}
                <div class="flex-1">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Search Customers
                    </label>

                    <div class="relative">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </div>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Search by name, phone, or email..."
                            class="w-full rounded-xl border-slate-300 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-emerald-700"
                        >

                    </div>

                </div>


                {{-- Search Actions --}}
                <div class="flex gap-2">

                    <button
                        type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 sm:flex-none"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>

                        Search
                    </button>

                    @if(request('search'))

                        <a
                            href="{{ route('customers.index') }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 sm:flex-none"
                        >
                            Clear
                        </a>

                    @endif

                </div>

            </div>

        </form>

    </div>


    {{-- =========================================================
        CUSTOMER LIST
    ========================================================== --}}
    <div class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <div class="flex items-center gap-2">

                        <h3 class="text-lg font-bold text-slate-900">
                            Customer List
                        </h3>

                        @if(request('search'))

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                Search results
                            </span>

                        @endif

                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $customers->total() }} customer{{ $customers->total() == 1 ? '' : 's' }} registered
                    </p>
                </div>

                @if(request('search'))

                    <div class="text-sm text-slate-500">
                        Searching for
                        <span class="font-semibold text-slate-700">
                            "{{ request('search') }}"
                        </span>
                    </div>

                @endif

            </div>

        </div>


        @if($customers->count())

            {{-- =================================================
                DESKTOP TABLE
            ================================================== --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-full">

                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Customer
                            </th>

                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Phone
                            </th>

                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Email
                            </th>

                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Pets
                            </th>

                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Loyalty
                            </th>

                            <th class="w-[8rem] min-w-[8rem] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($customers as $customer)
                            @php
                                $customerViewData = [
                                    'id' => (string) $customer->id,
                                    'name' => $customer->name,
                                    'phone' => $customer->phone,
                                    'email' => $customer->email,
                                    'address' => $customer->address,
                                    'loyalty_visits' => (int) ($customer->loyalty_visits ?? 0),
                                    'profile_url' => route('customers.show', $customer),
                                    'pets' => $customer->pets->map(fn ($pet) => [
                                        'id' => (string) $pet->id,
                                        'name' => $pet->name,
                                        'species' => $pet->species ?: 'Type not recorded',
                                        'breed' => $pet->breed ?: '—',
                                        'sex' => $pet->sex ?: '—',
                                        'size' => $pet->species === 'Cat' ? 'Not required' : ($pet->weight_category ?: '—'),
                                        'notes' => $pet->notes ?: 'No notes recorded',
                                        'edit_url' => route('customers.show', ['customer' => $customer, 'edit_pet' => $pet->id]),
                                        'delete_url' => route('pets.destroy', $pet),
                                    ])->values(),
                                    'sales' => $customer->sales->sortByDesc('created_at')->map(fn ($sale) => [
                                        'id' => (string) $sale->id,
                                        'invoice' => $sale->invoice_number ?: '—',
                                        'date' => optional($sale->created_at)->format('M d, Y h:i A') ?? '—',
                                        'items' => $sale->items->count(),
                                        'total' => '₱'.number_format((float) $sale->total, 2),
                                        'cashier' => $sale->cashier?->name ?? '—',
                                    ])->values(),
                                ];
                            @endphp

                            <tr class="group transition hover:bg-slate-50">

                                {{-- CUSTOMER --}}
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-600">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">

                                            <div class="font-semibold text-slate-900">
                                                {{ $customer->name }}
                                            </div>

                                            @if($customer->address)

                                                <div class="mt-0.5 max-w-xs truncate text-xs text-slate-500">
                                                    {{ $customer->address }}
                                                </div>

                                            @else

                                                <div class="mt-0.5 text-xs text-slate-400">
                                                    No address provided
                                                </div>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- PHONE --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    @if($customer->phone)

                                        <div class="flex items-center gap-2 text-sm text-slate-700">

                                            <span class="text-slate-400">
                                                
                                            </span>

                                            {{ $customer->phone }}

                                        </div>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- EMAIL --}}
                                <td class="px-6 py-4">

                                    @if($customer->email)

                                        <div class="max-w-[220px] truncate text-sm text-slate-700">
                                            {{ $customer->email }}
                                        </div>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- PETS --}}
                                <td class="px-6 py-4 text-center">

                                    <div class="inline-flex items-center gap-2">

                                        <span class="flex h-8 min-w-8 items-center justify-center rounded-lg bg-blue-50 px-2 text-sm font-bold text-blue-700">
                                            {{ $customer->pets->count() }}
                                        </span>

                                        <span class="hidden text-xs text-slate-500 xl:inline">
                                            pet{{ $customer->pets->count() == 1 ? '' : 's' }}
                                        </span>

                                    </div>

                                </td>


                                {{-- LOYALTY --}}
                                <td class="px-6 py-4 text-center">

                                    <div class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2">
                                        <div class="text-left">

                                            <div class="text-sm font-bold text-amber-700">
                                                {{ $customer->loyalty_visits ?? 0 }}
                                            </div>

                                            <div class="text-[10px] font-medium uppercase tracking-wide text-amber-600">
                                                visits
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- ACTIONS --}}
                                <td class="w-[8rem] min-w-[8rem] px-3 py-5">

                                    <div class="grid w-24 grid-cols-2 gap-x-1">

                                        <button type="button" @click="openCustomerView(@js($customerViewData))" class="record-action-secondary inline-flex w-full items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs font-semibold">
                                            View
                                        </button>

                                        <button type="button" @click="openCustomerEdit(@js(['id' => (string) $customer->id, 'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email, 'address' => $customer->address]))" class="record-action-secondary inline-flex w-full items-center justify-center rounded-lg px-2 py-1.5 text-xs font-semibold">
                                            Edit
                                        </button>

                                        @if(!auth()->user()->isFrontDesk())
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Move this customer and their pets to Archive? Their records will be kept.');" class="col-span-2 mt-1.5 w-full border-t border-slate-200 pt-1.5">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="record-action-danger block w-full whitespace-nowrap rounded-lg px-2 py-1.5 text-xs font-semibold">Delete</button>
                                            </form>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                MOBILE CUSTOMER CARDS
            ================================================== --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @foreach($customers as $customer)
                    @php
                        $customerViewData = [
                            'id' => (string) $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'address' => $customer->address,
                            'loyalty_visits' => (int) ($customer->loyalty_visits ?? 0),
                            'profile_url' => route('customers.show', $customer),
                            'pets' => $customer->pets->map(fn ($pet) => [
                                'id' => (string) $pet->id,
                                'name' => $pet->name,
                                'species' => $pet->species ?: 'Type not recorded',
                                'breed' => $pet->breed ?: '—',
                                'sex' => $pet->sex ?: '—',
                                'size' => $pet->species === 'Cat' ? 'Not required' : ($pet->weight_category ?: '—'),
                                'notes' => $pet->notes ?: 'No notes recorded',
                                'edit_url' => route('customers.show', ['customer' => $customer, 'edit_pet' => $pet->id]),
                                'delete_url' => route('pets.destroy', $pet),
                            ])->values(),
                            'sales' => $customer->sales->sortByDesc('created_at')->map(fn ($sale) => [
                                'id' => (string) $sale->id,
                                'invoice' => $sale->invoice_number ?: '—',
                                'date' => optional($sale->created_at)->format('M d, Y h:i A') ?? '—',
                                'items' => $sale->items->count(),
                                'total' => '₱'.number_format((float) $sale->total, 2),
                                'cashier' => $sale->cashier?->name ?? '—',
                            ])->values(),
                        ];
                    @endphp

                    <div class="p-4 sm:p-5">

                        {{-- Customer Identity --}}
                        <div class="flex items-start justify-between gap-3">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-600">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>

                                <div class="min-w-0">

                                    <h4 class="truncate font-bold text-slate-900">
                                        {{ $customer->name }}
                                    </h4>

                                    @if($customer->address)

                                        <p class="mt-0.5 truncate text-xs text-slate-500">
                                            {{ $customer->address }}
                                        </p>

                                    @endif

                                </div>

                            </div>

                            <div class="shrink-0 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                {{ $customer->pets->count() }} pet{{ $customer->pets->count() == 1 ? '' : 's' }}
                            </div>

                        </div>


                        {{-- Contact Information --}}
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">

                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">

                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Phone
                                </div>

                                <div class="mt-0.5 truncate text-sm font-medium text-slate-700">
                                    {{ $customer->phone ?: 'Not provided' }}
                                </div>

                            </div>


                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">

                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Email
                                </div>

                                <div class="mt-0.5 truncate text-sm font-medium text-slate-700">
                                    {{ $customer->email ?: 'Not provided' }}
                                </div>

                            </div>

                        </div>


                        {{-- Loyalty --}}
                        <div class="mt-3 flex items-center justify-between rounded-xl bg-amber-50 px-3.5 py-3">

                            <div class="flex items-center gap-2">

                                <span class="text-sm font-semibold text-amber-800">
                                    Loyalty Visits
                                </span>

                            </div>

                            <span class="text-lg font-bold text-amber-700">
                                {{ $customer->loyalty_visits ?? 0 }}
                            </span>

                        </div>


                        {{-- Actions --}}
                        <div class="mt-4 grid grid-cols-2 gap-2">

                            <button type="button" @click="openCustomerView(@js($customerViewData))" class="record-action-secondary inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold">
                                View Customer
                            </button>

                            <button type="button" @click="openCustomerEdit(@js(['id' => (string) $customer->id, 'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email, 'address' => $customer->address]))" class="record-action-secondary inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold">
                                Edit
                            </button>

                            @if(!auth()->user()->isFrontDesk())
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Move this customer and their pets to Archive? Their records will be kept.');" class="col-span-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="record-action-danger inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold">Delete Customer</button>
                                </form>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- =================================================
                PAGINATION
            ================================================== --}}
            @if($customers->hasPages())

                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">

                    {{ $customers->links() }}

                </div>

            @endif


        @else

            {{-- =================================================
                EMPTY STATE
            ================================================== --}}
            <div class="px-6 py-20 text-center">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl">
                    
                </div>

                <h3 class="mt-5 text-lg font-bold text-slate-900">
                    No customers found
                </h3>

                @if(request('search'))

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        No customers matched
                        <span class="font-semibold text-slate-700">
                            "{{ request('search') }}"
                        </span>.
                        Try a different name, phone number, or email.
                    </p>

                    <a
                        href="{{ route('customers.index') }}"
                        class="mt-5 inline-flex items-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"
                    >
                        Clear Search
                    </a>

                @else

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        Start building your customer database by adding your first customer.
                    </p>

                    <button type="button" @click="addCustomerOpen = true; $nextTick(() => $refs.customerName.focus())" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900">
                        <span class="text-lg leading-none">+</span>
                        Add Customer
                    </button>

                @endif

            </div>

        @endif

    </div>

</div>

@endsection