@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')

<div class="report-page min-h-screen bg-slate-50" x-data="{ reasonDialogOpen: false, selectedReason: '', showReason(reason) { this.selectedReason = reason; this.reasonDialogOpen = true; } }" @keydown.escape.window="reasonDialogOpen = false">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-6 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Inventory Report
                </h1>

                <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">
                    Track every stock change, including receipts, sales, allocations, adjustments, and expiry write-offs.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/10">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Reports
                </a>
                @include('reports.partials.delete-all')
                <form method="POST" action="{{ route('reports.generate', 'inventory') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        Download inventory PDF
                    </button>
                </form>
            </div>

        </div>


        {{-- Summary --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Total Movements --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total stock movements
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($movements->total()) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            All recorded stock changes
                        </p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>

                </div>

            </div>


            {{-- Current Page --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Showing on this page
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($movements->count()) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Latest movements currently visible
                        </p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <path stroke-linecap="round" d="M9 12h6M9 16h4"/>
                        </svg>
                    </div>

                </div>

            </div>

        </div>


        {{-- Movement Mix --}}
        @php
            $movementMixTotal = (int) ($movementSummary->sum('movements') ?? 0);
            $topMovementType = $movementSummary->sortByDesc('movements')->first();
            $movementShare = $movementMixTotal > 0 && $topMovementType ? (($topMovementType->movements / $movementMixTotal) * 100) : 0;
            $movementColors = ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#14b8a6'];
            $movementSlices = [];
            $movementSliceStart = 0;

            foreach ($movementSummary as $index => $movement) {
                $typeShare = $movementMixTotal > 0 ? ((int) $movement->movements / $movementMixTotal) * 100 : 0;
                $movementSlices[] = [
                    'label' => ucfirst(str_replace('_', ' ', $movement->type)),
                    'value' => (int) $movement->movements,
                    'share' => $typeShare,
                    'start' => $movementSliceStart,
                    'end' => $movementSliceStart + $typeShare,
                    'color' => $movementColors[$index % count($movementColors)],
                ];
                $movementSliceStart += $typeShare;
            }

            $movementPieGradient = collect($movementSlices)
                ->map(fn ($slice) => $slice['color'] . ' ' . $slice['start'] . '% ' . $slice['end'] . '%')
                ->implode(', ');
        @endphp

        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="inventory-mix-title">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 id="inventory-mix-title" class="text-base font-bold text-slate-900">Movement mix</h2>
                    <p class="mt-1 text-xs text-slate-500">Share of stock activity by movement type.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span> Inventory
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)] lg:items-center">
                <div class="flex items-center justify-center py-2">
                    <div class="relative flex shrink-0 items-center justify-center rounded-full shadow-inner ring-8 ring-slate-100" style="width: 14rem; height: 14rem; flex: 0 0 14rem; background: radial-gradient(circle, #ffffff 0 62%, transparent 63% 100%), conic-gradient({{ $movementPieGradient ?: '#e2e8f0 0 100%' }});">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white text-center shadow-inner">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top</p>
                                <p class="text-lg font-bold text-slate-900">{{ $topMovementType ? number_format((float) $movementShare, 0) . '%' : '0%' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total movements</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $movementMixTotal) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Movement type mix</p>
                        <div class="space-y-2">
                            @forelse($movementSlices as $slice)
                                <div class="flex items-center justify-between gap-3 text-xs text-slate-600">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $slice['color'] }};"></span>
                                        <span class="truncate">{{ $slice['label'] }}</span>
                                    </div>
                                    <span class="shrink-0 font-semibold text-slate-700">{{ number_format($slice['share'], 0) }}%</span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">No inventory movements recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Movement Snapshot --}}
        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="inventory-chart-title">
            <div>
                <h2 id="inventory-chart-title" class="text-base font-bold text-slate-900">What changed in inventory</h2>
                <p class="mt-1 text-xs text-slate-500">Movement count grouped by activity type.</p>
            </div>
            @php $movementChartMax = max((int) ($movementSummary->max('movements') ?? 0), 1); @endphp
            <div class="mt-5 space-y-4">
                @forelse($movementSummary as $index => $movement)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $movement->type) }}</span>
                            <span class="shrink-0 font-semibold tabular-nums text-slate-900">{{ number_format((int) $movement->movements) }} movements</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ str_replace('_', ' ', $movement->type) }}: {{ number_format((int) $movement->movements) }} movements">
                            <div class="h-full rounded-full" style="width: {{ min(100, ((int) $movement->movements / $movementChartMax) * 100) }}%; background-color: {{ $movementColors[$index % count($movementColors)] }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500">No inventory movements recorded yet.</p>
                @endforelse
            </div>
        </section>


        {{-- Stock Movement History --}}
        <div class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Section Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Stock change history
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Each row explains what changed, when it changed, and who recorded it.
                    </p>
                </div>

                @if($movements->count() > 0)

                    <div class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        Showing {{ $movements->count() }} movements
                    </div>

                @endif

            </div>


            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto xl:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Date
                            </th>

                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Product
                            </th>

                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Type
                            </th>

                            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Quantity
                            </th>

                            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Before
                            </th>

                            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                After
                            </th>

                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                User
                            </th>

                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Reason
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($movements as $movement)

                            <tr class="transition hover:bg-slate-50">

                                {{-- Date --}}
                                <td class="whitespace-nowrap px-5 py-4">

                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $movement->created_at->format('M d, Y') }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $movement->created_at->format('h:i A') }}
                                    </p>

                                </td>


                                {{-- Product --}}
                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-900">
                                                {{ $movement->product?->name ?? 'Deleted Product' }}
                                            </p>

                                            @if($movement->product?->sku)

                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    SKU: {{ $movement->product->sku }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- Type --}}
                                <td class="px-5 py-4">

                                    @if($movement->type === 'adjustment')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            Adjustment
                                        </span>

                                    @elseif($movement->type === 'sale')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Sale
                                        </span>

                                    @elseif($movement->type === 'stock_in')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Stock In
                                        </span>

                                    @elseif($movement->type === 'stock_out')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Stock Out
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            {{ ucfirst($movement->type ?? 'Unknown') }}
                                        </span>

                                    @endif

                                </td>


                                {{-- Quantity --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right">

                                    @if((int) $movement->quantity > 0)

                                        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-bold text-emerald-700">
                                            +{{ number_format($movement->quantity) }}
                                        </span>

                                    @elseif((int) $movement->quantity < 0)

                                        <span class="inline-flex items-center rounded-lg bg-red-50 px-2.5 py-1 text-sm font-bold text-red-700">
                                            {{ number_format($movement->quantity) }}
                                        </span>

                                    @else

                                        <span class="text-sm font-semibold text-slate-500">
                                            0
                                        </span>

                                    @endif

                                </td>


                                {{-- Before --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-slate-600">
                                    {{ number_format($movement->stock_before) }}
                                </td>


                                {{-- After --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right">

                                    <span class="text-sm font-bold text-slate-900">
                                        {{ number_format($movement->stock_after) }}
                                    </span>

                                </td>


                                {{-- User --}}
                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-2">

                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                            {{ strtoupper(substr($movement->user?->name ?? 'S', 0, 1)) }}
                                        </div>

                                        <span class="max-w-[120px] truncate text-sm text-slate-700">
                                            {{ $movement->user?->name ?? 'System' }}
                                        </span>

                                    </div>

                                </td>


                                {{-- Reason --}}
                                <td class="px-5 py-4">
                                    <button type="button" @click="showReason($event.currentTarget.dataset.reason)" data-reason="{{ $movement->reason ?: 'No reason recorded.' }}" aria-label="View reason for {{ $movement->product?->name ?? 'stock movement' }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        View
                                    </button>
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="px-6 py-14 text-center">

                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>

                                        </div>

                                        <h3 class="text-sm font-bold text-slate-900">
                                            No inventory movements found
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            There are no stock movement records to display.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile / Tablet Cards --}}
            <div class="divide-y divide-slate-100 xl:hidden">

                @forelse($movements as $movement)

                    <div class="p-4 sm:p-5">

                        {{-- Card Header --}}
                        <div class="flex items-start justify-between gap-3">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-bold text-slate-900">
                                        {{ $movement->product?->name ?? 'Deleted Product' }}
                                    </p>

                                    @if($movement->product?->sku)

                                        <p class="mt-0.5 text-xs text-slate-500">
                                            SKU: {{ $movement->product->sku }}
                                        </p>

                                    @endif

                                </div>

                            </div>


                            {{-- Type Badge --}}
                            <div class="shrink-0">

                                @if($movement->type === 'adjustment')

                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                        Adjustment
                                    </span>

                                @elseif($movement->type === 'sale')

                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700">
                                        Sale
                                    </span>

                                @elseif($movement->type === 'stock_in')

                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                        Stock In
                                    </span>

                                @elseif($movement->type === 'stock_out')

                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                        Stock Out
                                    </span>

                                @else

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                        {{ ucfirst($movement->type ?? 'Unknown') }}
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Date --}}
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>

                            {{ $movement->created_at->format('M d, Y h:i A') }}

                        </div>


                        {{-- Stock Values --}}
                        <div class="mt-4 grid grid-cols-3 gap-2">

                            <div class="rounded-xl bg-slate-50 p-3 text-center">

                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    Before
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-700">
                                    {{ number_format($movement->stock_before) }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-50 p-3 text-center">

                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    Change
                                </p>

                                @if((int) $movement->quantity > 0)

                                    <p class="mt-1 text-sm font-bold text-emerald-600">
                                        +{{ number_format($movement->quantity) }}
                                    </p>

                                @elseif((int) $movement->quantity < 0)

                                    <p class="mt-1 text-sm font-bold text-red-600">
                                        {{ number_format($movement->quantity) }}
                                    </p>

                                @else

                                    <p class="mt-1 text-sm font-bold text-slate-500">
                                        0
                                    </p>

                                @endif

                            </div>


                            <div class="rounded-xl bg-slate-50 p-3 text-center">

                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    After
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-900">
                                    {{ number_format($movement->stock_after) }}
                                </p>

                            </div>

                        </div>


                        {{-- User + Reason --}}
                        <div class="mt-4 space-y-3 border-t border-slate-100 pt-4">

                            <div class="flex items-center justify-between gap-3">

                                <span class="text-xs font-medium text-slate-400">
                                    User
                                </span>

                                <div class="flex min-w-0 items-center gap-2">

                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">
                                        {{ strtoupper(substr($movement->user?->name ?? 'S', 0, 1)) }}
                                    </div>

                                    <span class="truncate text-xs font-semibold text-slate-700">
                                        {{ $movement->user?->name ?? 'System' }}
                                    </span>

                                </div>

                            </div>


                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-medium text-slate-400">Reason</span>
                                <button type="button" @click="showReason($event.currentTarget.dataset.reason)" data-reason="{{ $movement->reason ?: 'No reason recorded.' }}" aria-label="View reason for {{ $movement->product?->name ?? 'stock movement' }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    View
                                </button>
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-5 py-14 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>

                        </div>

                        <h3 class="mt-4 text-sm font-bold text-slate-900">
                            No inventory movements found
                        </h3>

                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                            There are no stock movement records to display.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- Pagination --}}
        @if($movements->hasPages())

            <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
                {{ $movements->links() }}
            </div>

        @endif

    </div>

    <div x-show="reasonDialogOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="reasonDialogOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="stock-reason-title" class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 id="stock-reason-title" class="text-lg font-bold text-slate-900">Movement Reason</h2>
                <button type="button" @click="reasonDialogOpen = false" aria-label="Close reason" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <p class="whitespace-pre-wrap break-words text-sm leading-6 text-slate-700" x-text="selectedReason"></p>
            </div>
        </section>
    </div>
</div>

@endsection