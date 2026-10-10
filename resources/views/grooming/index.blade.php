@extends('layouts.app')

@section('title', 'Grooming Requests')

@section('content')
@php
    $oldAddonEdit = null;
    if (old('_form') === 'grooming-addons') {
        $oldVisitAddonEdit = $visitAddonEditData[(int) old('appointment_id')] ?? null;
        if ($oldVisitAddonEdit) {
            $oldAddonEdit = [
                'appointmentId' => (string) $oldVisitAddonEdit['appointment_id'],
                'petName' => $oldVisitAddonEdit['pet_name'],
                'groupSize' => $oldVisitAddonEdit['group_size'],
                'baseTotal' => $oldVisitAddonEdit['base_total'],
                'paid' => $oldVisitAddonEdit['paid'],
                'currentIds' => $oldVisitAddonEdit['current_add_on_ids'],
                'inactiveAddons' => $oldVisitAddonEdit['inactive_addons'],
                'selectedIds' => array_map('strval', old('add_on_ids', $oldVisitAddonEdit['current_add_on_ids'])),
            ];
        }
    }
@endphp

<div class="min-h-screen bg-slate-50" x-data="{
    addonEditorOpen: @js($oldAddonEdit !== null),
    addonOptions: @js($groomingAddons->map(fn ($addon) => ['id' => (string) $addon->id, 'name' => $addon->name, 'category' => $addon->category, 'rate' => (float) $addon->rate])->values()),
    addonEditor: @js($oldAddonEdit ?? ['appointmentId' => '', 'petName' => '', 'groupSize' => 1, 'baseTotal' => 0, 'paid' => false, 'currentIds' => [], 'inactiveAddons' => [], 'selectedIds' => []]),
    openAddonEditor(visit) {
        this.addonEditor = {
            appointmentId: String(visit.appointment_id),
            petName: visit.pet_name,
            groupSize: Number(visit.group_size || 1),
            baseTotal: Number(visit.base_total || 0),
            paid: Boolean(visit.paid),
            currentIds: (visit.current_add_on_ids || []).map(String),
            inactiveAddons: visit.inactive_addons || [],
            selectedIds: (visit.current_add_on_ids || []).map(String),
        };
        this.addonEditorOpen = true;
    },
    toggleAddon(id, category, checked) {
        let selected = this.addonEditor.selectedIds.filter((selectedId) => selectedId !== id);
        if (checked) {
            if (category === 'detangling') {
                selected = selected.filter((selectedId) => this.addonOptions.find((addon) => addon.id === selectedId)?.category !== 'detangling');
            }
            selected.push(id);
        }
        this.addonEditor.selectedIds = selected;
    },
    get selectedAddonTotal() {
        const activeTotal = this.addonOptions.filter((addon) => this.addonEditor.selectedIds.includes(addon.id)).reduce((total, addon) => total + addon.rate, 0);
        const inactiveTotal = this.addonEditor.inactiveAddons.filter((addon) => this.addonEditor.selectedIds.includes(addon.id)).reduce((total, addon) => total + addon.rate, 0);
        return (activeTotal + inactiveTotal) * this.addonEditor.groupSize;
    },
    get additionalAddonTotal() {
        return this.addonOptions.filter((addon) => this.addonEditor.selectedIds.includes(addon.id) && !this.addonEditor.currentIds.includes(addon.id)).reduce((total, addon) => total + addon.rate, 0) * this.addonEditor.groupSize;
    },
    get updatedVisitTotal() { return this.addonEditor.baseTotal + this.selectedAddonTotal; }
}" @keydown.escape.window="addonEditorOpen = false">

    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-5 sm:px-6 lg:px-8">

        {{-- =========================================================
             PAGE HEADER
        ========================================================== --}}
        <div class="flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between sm:p-5">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Grooming Requests
                </h1>
                <p class="mt-1 text-sm text-emerald-100/80">
                    Manage grooming requests from approval through completion.
                </p>
            </div>


            <a href="{{ route('grooming.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600">

                New Grooming Request

            </a>

        </div>


        <section aria-labelledby="grooming-flow-title" class="inventory-content-frame rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="grooming-flow-title" class="font-bold text-slate-900">New grooming visit</h2>
                    <p class="mt-1 text-xs text-slate-500">Follow these four steps from check-in through payment.</p>
                </div>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Services & Add-ons', 'Choose a service and size for each pet, then select any extras.'],
                    ['Waiver', 'Confirm the waiver and attach a photo when available.'],
                    ['Customer & Pet', 'Select an existing customer or add one, then select or add each pet.'],
                    ['Summary', 'Review the details, complete payment, and print the receipt from the grooming queue.'],
                ] as $index => [$title, $description])
                    <div class="flex min-w-0 items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-800 text-xs font-bold text-white">{{ $index + 1 }}</span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>


        {{-- =========================================================
             STATUS OVERVIEW
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            @php
                $statusOverview = [
                    [
                        'label' => 'Pending',
                        'status' => 'pending',
                        'icon' => '',
                        'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    ],
                    [
                        'label' => 'In Progress',
                        'status' => 'in_progress',
                        'icon' => '',
                        'class' => 'bg-blue-50 text-blue-700 border-blue-200',
                    ],
                    [
                        'label' => 'Completed',
                        'status' => 'completed',
                        'icon' => '',
                        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    ],
                ];
            @endphp


            @foreach($statusOverview as $overview)

                @php
                    $statusCount = match ($overview['status']) {
                        'in_progress' => $appointments->getCollection()->whereIn('status', ['approved', 'received', 'in_progress'])->count(),
                        'completed' => $appointments->getCollection()->whereIn('status', ['ready', 'completed'])->count(),
                        default => $appointments->getCollection()->where('status', $overview['status'])->count(),
                    };
                @endphp

                <div class="rounded-2xl border-4 {{ $overview['class'] }} p-4">

                    <div class="flex items-center justify-between gap-2">

                        <span class="text-2xl font-bold">
                            {{ $statusCount }}
                        </span>

                    </div>

                    <p class="mt-3 text-xs font-semibold">
                        {{ $overview['label'] }}
                    </p>

                </div>

            @endforeach

        </div>


        {{-- =========================================================
             ALERTS
        ========================================================== --}}

        @if(session('success'))

            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 shadow-sm">

                <div>
                    <p class="text-sm font-semibold">
                        Success
                    </p>

                    <p class="mt-0.5 text-sm">
                        {{ session('success') }}
                    </p>
                </div>

            </div>

        @endif


        @if(session('error'))

            <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100">
                    !
                </div>

                <div>
                    <p class="text-sm font-semibold">
                        Something went wrong
                    </p>

                    <p class="mt-0.5 text-sm">
                        {{ session('error') }}
                    </p>
                </div>

            </div>

        @endif


        @if($errors->any())

            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">

                <div class="flex items-start gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100">
                        !
                    </div>

                    <div>

                        <p class="text-sm font-semibold">
                            Please check the following
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>

        @endif


        {{-- =========================================================
             REQUEST TABLE
        ========================================================== --}}
        <div class="inventory-content-frame overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            {{-- Table Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>

                    <h2 class="font-bold text-slate-900">
                        Grooming Queue
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Review and manage active grooming requests.
                    </p>

                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-xl bg-slate-100 px-3 py-2">

                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>

                    <span class="text-xs font-semibold text-slate-600">
                        {{ $appointments->total() }} total requests
                    </span>

                </div>

            </div>


            <form method="GET" action="{{ route('grooming.index') }}" class="grid gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-4 sm:grid-cols-[minmax(0,1fr)_13rem_auto] sm:items-end sm:px-6">
                <div>
                    <label for="grooming-queue-search" class="mb-1 block text-xs font-semibold text-slate-600">Search queue</label>
                    <input id="grooming-queue-search" type="search" name="search" value="{{ request('search') }}" placeholder="Customer or pet name" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-700">
                </div>
                <div>
                    <label for="grooming-queue-status" class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                    <select id="grooming-queue-status" name="status" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-700">
                        <option value="all" @selected(request('status', 'all') === 'all')>All statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-900 sm:flex-none">Apply</button>
                    @if(request()->filled('search') || request('status', 'all') !== 'all')
                        <a href="{{ route('grooming.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear</a>
                    @endif
                </div>
            </form>

            @if($appointments->count())

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[1100px] text-sm">

                        <thead>

                            <tr class="border-b border-slate-100 bg-slate-50/80">

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Request
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Customer
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Pet
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Size
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Price
                                </th>

                                <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Status
                                </th>

                                <th class="w-36 min-w-36 px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            @foreach($appointments as $appointment)

                                <tr id="appointment-{{ $appointment->id }}" class="group scroll-mt-24 transition hover:bg-slate-50/70">

                                    {{-- =================================================
                                         REQUEST
                                    ================================================== --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600">#{{ $appointment->id }}</div>
                                            <div>
                                                <p class="font-semibold text-slate-800">Grooming Request</p>
                                                <p class="mt-0.5 text-xs text-slate-400">{{ $appointment->created_at->format('M d, Y') }} · {{ $appointment->created_at->format('h:i A') }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- CUSTOMER --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-600">{{ strtoupper(substr($appointment->customer->name ?? '—', 0, 1)) }}</div>
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-slate-800">{{ $appointment->customer->name ?? '—' }}</p>
                                                @if($appointment->customer?->phone)
                                                    <p class="mt-0.5 text-xs text-slate-400">{{ $appointment->customer->phone }}</p>
                                                @else
                                                    <p class="mt-0.5 text-xs text-slate-400">No phone number</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>


                                    {{-- =================================================
                                         PET
                                    ================================================== --}}
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-base">
                                                
                                            </div>

                                            <div>

                                                <p class="font-semibold text-slate-800">
                                                    {{ $appointment->pet->name ?? '—' }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-slate-400">

                                                    {{ $appointment->pet->species ?? 'Pet' }}

                                                    @if($appointment->pet?->breed)
                                                        <span class="mx-1">•</span>
                                                        {{ $appointment->pet->breed }}
                                                    @endif

                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- SIZE --}}
                                    <td class="px-6 py-4">
                                        @php
                                            $petSize = $appointment->pet?->weight_category;
                                            $petWeight = $appointment->actual_weight ?: $appointment->pet?->weight;
                                            if (!$petSize && $petWeight !== null) {
                                                $petWeight = (float) $petWeight;
                                                $petSize = $petWeight <= 5 ? 'Small' : ($petWeight <= 15 ? 'Medium' : 'Large');
                                            }
                                        @endphp
                                        <span class="font-semibold text-slate-800">{{ $petSize ?: 'Not set' }}</span>
                                    </td>


                                    {{-- =================================================
                                         PRICE
                                    ================================================== --}}
                                    <td class="px-6 py-4">

                                        @if($appointment->grooming_price !== null)

                                            <span class="font-bold text-slate-800">
                                                ₱{{ number_format((float) $appointment->grooming_price, 2) }}
                                            </span>

                                        @else

                                            <span class="text-xs font-medium text-slate-400">
                                                Not set
                                            </span>

                                        @endif

                                    </td>


                                    {{-- =================================================
                                         STATUS
                                    ================================================== --}}
                                    <td class="px-6 py-4">

                                        @php

                                            $statusConfig = match($appointment->status) {

                                                'pending' => [
                                                    'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    'dot' => 'bg-amber-500',
                                                ],

                                                'approved', 'received', 'in_progress' => [
                                                    'class' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'dot' => 'bg-blue-500',
                                                ],

                                                'ready', 'completed' => [
                                                    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'dot' => 'bg-emerald-500',
                                                ],

                                                'cancelled' => [
                                                    'class' => 'bg-red-50 text-red-700 border-red-200',
                                                    'dot' => 'bg-red-500',
                                                ],

                                                default => [
                                                    'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                                                    'dot' => 'bg-slate-400',
                                                ],

                                            };

                                        @endphp


                                        <div>

                                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusConfig['class'] }}">

                                                <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>

                                                {{ in_array($appointment->status, ['approved', 'received', 'in_progress'], true) ? 'In Progress' : (in_array($appointment->status, ['ready', 'completed'], true) ? 'Completed' : ucfirst(str_replace('_', ' ', $appointment->status))) }}

                                            </span>


                                            @if($appointment->approved_at)

                                                <p class="mt-1.5 text-[11px] text-slate-400">

                                                    Approved
                                                    {{ $appointment->approved_at->format('M d, h:i A') }}

                                                </p>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- =================================================
                                         ACTIONS
                                    ================================================== --}}
                                    <td class="w-36 min-w-36 px-3 py-4 text-center {{ $appointment->status === 'completed' ? 'align-middle' : 'align-top' }}">
                                        <div class="flex w-full flex-col items-center gap-1">
                                            <div class="flex w-full flex-wrap items-center justify-center gap-1">

                                                {{-- ACCEPT --}}
                                                @if($appointment->status === 'pending')

                                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isFrontDesk())

                                                          <form method="POST"
                                                              action="{{ route('grooming.approve', $appointment) }}"
                                                              data-scroll-target="appointment-{{ $appointment->id }}">

                                                            @csrf

                                                            <button type="submit"
                                                                    class="grooming-action-feedback grooming-action-accept inline-flex h-9 w-24 shrink-0 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold">

                                                                Accept

                                                            </button>

                                                        </form>

                                                    @endif

                                                @elseif(in_array($appointment->status, ['approved', 'received', 'in_progress'], true))

                                                    @if(
                                                        auth()->user()->isAdmin() ||
                                                        auth()->user()->isManager() ||
                                                        auth()->user()->isFrontDesk() ||
                                                        auth()->user()->isGroomer()
                                                    )

                                                          <form method="POST"
                                                              action="{{ route('grooming.ready', $appointment) }}"
                                                              data-scroll-target="appointment-{{ $appointment->id }}">

                                                            @csrf

                                                            <button type="submit"
                                                                    class="grooming-action-feedback grooming-action-ready inline-flex h-9 w-24 shrink-0 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold">

                                                                Ready

                                                            </button>

                                                        </form>

                                                    @endif

                                                @elseif($appointment->status === 'completed')
                                                    @if(
                                                        auth()->user()->isAdmin() ||
                                                        auth()->user()->isManager() ||
                                                        auth()->user()->isFrontDesk()
                                                    )
                                                        @if($appointment->sale_id)
                                                            <a href="{{ route('receipts.show', $appointment->sale_id) }}" class="grooming-completed-action grooming-action-feedback rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800">Receipt</a>
                                                        @else
                                                            <a href="{{ route('grooming.payment', $appointment) }}" aria-label="Unpaid, collect payment" title="Collect payment" class="grooming-completed-action grooming-action-feedback grooming-action-unpaid rounded-lg px-3 py-2 text-xs font-bold">Unpaid</a>
                                                        @endif
                                                    @endif
                                                @endif


                                            </div>

                                            @if(in_array($appointment->status, ['approved', 'received'], true))
                                                @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isFrontDesk())
                                                    <form method="POST" action="{{ route('grooming.cancel', $appointment) }}" data-scroll-target="appointment-{{ $appointment->id }}" onsubmit="return confirm('Remove this grooming visit from the active queue? Its history will be retained.');">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg px-2.5 py-2 text-xs font-semibold text-red-500 transition hover:bg-red-50 hover:text-red-700">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if($appointment->status !== 'completed' && (auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isFrontDesk()))
                                                <button type="button" aria-label="Edit add-ons for {{ $appointment->pet?->name ?? 'pet' }}" @click="openAddonEditor(@js($visitAddonEditData[$appointment->id]))" class="record-action-secondary inline-flex h-9 w-24 shrink-0 items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold">Edit</button>
                                            @endif
                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- =========================================================
                     PAGINATION
                ========================================================== --}}
                <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">

                    {{ $appointments->links() }}

                </div>


            @else

                {{-- =========================================================
                     EMPTY STATE
                ========================================================== --}}
                <div class="flex min-h-[420px] flex-col items-center justify-center px-6 py-16 text-center">

                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-100 text-3xl shadow-inner">
                        
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-800">
                        No grooming requests yet
                    </h3>

                    <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                        Create a grooming request to start tracking the pet through the grooming workflow.
                    </p>

                    <a href="{{ route('grooming.create') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">

                        <span>+</span>

                        Create Grooming Request

                    </a>

                </div>

            @endif

        </div>

    </div>

    <div x-show="addonEditorOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="addonEditorOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="grooming-addon-edit-title" class="my-auto flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Grooming visit · <span x-text="addonEditor.paid ? 'Paid' : 'Balance due'"></span></p>
                    <h2 id="grooming-addon-edit-title" class="mt-1 text-lg font-bold">Edit Add-ons</h2>
                    <p class="mt-1 text-sm text-emerald-100/80"><span x-text="addonEditor.petName"></span><span x-show="addonEditor.groupSize > 1"> and <span x-text="addonEditor.groupSize - 1"></span> other pet(s)</span></p>
                </div>
                <button type="button" @click="addonEditorOpen = false" aria-label="Close add-on editor" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </header>

            @if($errors->any() && old('_form') === 'grooming-addons')
                <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Check the add-on and payment details.</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" :action="'{{ url('/grooming') }}/' + addonEditor.appointmentId + '/addons'" class="flex min-h-0 flex-1 flex-col">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="grooming-addons">
                <input type="hidden" name="appointment_id" :value="addonEditor.appointmentId">
                <template x-for="addonId in addonEditor.selectedIds" :key="'selected-' + addonId">
                    <input type="hidden" name="add_on_ids[]" :value="addonId">
                </template>

                <div class="space-y-4 overflow-y-auto p-5">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Available add-ons</h3>
                        <p class="mt-1 text-xs text-slate-500" x-text="addonEditor.paid ? 'Previously paid add-ons are locked. Select new extras to create a separate charge.' : 'Selected extras will be included in the visit balance.'"></p>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <template x-for="addon in addonOptions" :key="addon.id">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50/50" :class="addonEditor.selectedIds.includes(addon.id) ? 'border-emerald-500 bg-emerald-50' : ''">
                                <input type="checkbox" :checked="addonEditor.selectedIds.includes(addon.id)" :disabled="(addonEditor.paid && addonEditor.currentIds.includes(addon.id)) || (addonEditor.paid && addon.category === 'detangling' && addonEditor.currentIds.some((id) => addonOptions.find((current) => current.id === id)?.category === 'detangling'))" @change="toggleAddon(addon.id, addon.category, $event.target.checked)" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700 disabled:opacity-60">
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold" x-text="addon.name"></span>
                                    <span class="block text-xs text-slate-500" x-text="addon.category === 'detangling' ? 'Detangling · choose one' : 'Extra service'"></span>
                                </span>
                                <span class="shrink-0 font-semibold text-slate-800">₱<span x-text="Number(addon.rate).toFixed(2)"></span></span>
                            </label>
                        </template>
                        <p x-show="addonOptions.length === 0" class="sm:col-span-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">No optional add-ons are available for this visit.</p>
                    </div>

                    <div x-show="addonEditor.inactiveAddons.length" x-cloak class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div>
                            <h3 class="text-sm font-semibold text-amber-950">Previously selected add-ons</h3>
                            <p class="mt-1 text-xs text-amber-800" x-text="addonEditor.paid ? 'Already paid and locked to this completed visit.' : 'No longer offered. Keep them or remove them from the unpaid balance.'"></p>
                        </div>
                        <template x-for="addon in addonEditor.inactiveAddons" :key="'inactive-' + addon.id">
                            <label class="flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" :checked="addonEditor.selectedIds.includes(addon.id)" :disabled="addonEditor.paid" @change="toggleAddon(addon.id, addon.category, $event.target.checked)" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700 disabled:opacity-60">
                                <span class="min-w-0 flex-1" x-text="addon.name"></span>
                                <span class="text-xs text-slate-500">₱<span x-text="Number(addon.rate).toFixed(2)"></span></span>
                            </label>
                        </template>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-slate-600" x-text="addonEditor.paid ? 'Additional add-on charge' : 'Updated visit balance'"></span>
                            <strong class="text-lg tabular-nums text-slate-900">₱<span x-text="(addonEditor.paid ? additionalAddonTotal : updatedVisitTotal).toFixed(2)"></span></strong>
                        </div>
                        <p class="mt-1 text-xs text-slate-500" x-text="addonEditor.groupSize > 1 ? `Applied to ${addonEditor.groupSize} pets in this visit.` : 'For this grooming visit.'"></p>
                    </div>

                    <div x-show="addonEditor.paid" x-cloak class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="addon_payment_method" class="mb-1 block text-sm font-semibold text-slate-700">Payment method *</label>
                            <select id="addon_payment_method" name="payment_method" :required="addonEditor.paid" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-100">
                                <option value="">Choose a method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank transfer</option>
                            </select>
                        </div>
                        <div>
                            <label for="addon_amount_paid" class="mb-1 block text-sm font-semibold text-slate-700">Amount received *</label>
                            <input id="addon_amount_paid" type="number" name="amount_paid" min="0" step="0.01" :min="additionalAddonTotal" :required="addonEditor.paid" class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-emerald-700 focus:ring-emerald-100">
                        </div>
                    </div>
                </div>

                <footer class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4">
                    <button type="button" @click="addonEditorOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                    <button type="submit" :disabled="addonEditor.paid && additionalAddonTotal <= 0" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" x-text="addonEditor.paid ? 'Collect Add-on Payment' : 'Update Balance'"></button>
                </footer>
            </form>
        </section>
    </div>

</div>

@endsection