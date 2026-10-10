@extends('layouts.app')

@section('title', 'Grooming Report')

@section('content')

<div class="report-page min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-6 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Grooming Report
                </h1>

                <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">
                    See which grooming services were sold and the revenue they generated during the selected dates.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ auth()->user()->isFrontDesk() ? route('dashboard') : route('reports.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/10">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ auth()->user()->isFrontDesk() ? 'Back to Dashboard' : 'Back to Reports' }}
                </a>
                @unless(auth()->user()->isFrontDesk())
                    @include('reports.partials.delete-all')
                @endunless
            </div>

        </div>


        {{-- Date Filter --}}
        <div class="inventory-content-frame mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Choose report dates
                        </h2>
                        <p class="text-xs text-slate-500">
                            Only completed grooming sales between these dates are included.
                        </p>
                    </div>

                </div>
            </div>

            <div class="p-5 sm:p-6">

                                        <form method="{{ auth()->user()->isFrontDesk() ? 'GET' : 'POST' }}"
                                                action="{{ auth()->user()->isFrontDesk() ? route('reports.grooming') : route('reports.generate', 'grooming') }}">
                                            @unless(auth()->user()->isFrontDesk())
                                                @csrf
                                            @endunless

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">

                        <div>
                            <label for="from"
                                   class="mb-2 block text-sm font-semibold text-slate-700">
                                From
                            </label>

                            <input type="date"
                                   name="from"
                                   id="from"
                                   value="{{ $from }}"
                                   required
                                   class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100">
                        </div>

                        <div>
                            <label for="to"
                                   class="mb-2 block text-sm font-semibold text-slate-700">
                                To
                            </label>

                            <input type="date"
                                   name="to"
                                   id="to"
                                   value="{{ $to }}"
                                   required
                                   class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100">
                        </div>

                        <div>
                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200">

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                </svg>

                                Download grooming PDF
                            </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>


        {{-- Report Summary --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Services Sold --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Grooming services sold
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format((float) $items->sum('quantity')) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Number of completed grooming line items
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v12m6-6H6"/>
                        </svg>
                    </div>

                </div>
            </div>


            {{-- Revenue --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Grooming sales value
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">
                            ₱{{ number_format((float) $items->sum('total'), 2) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Total value of completed grooming services
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10V6m0 12v-2m8-4a8 8 0 11-16 0 8 8 0 0116 0z"/>
                        </svg>
                    </div>

                </div>
            </div>

        </div>


        {{-- Service Mix --}}
        @php
            $groomingRevenueTotal = (float) ($items->sum('total') ?? 0);
            $topService = $items->sortByDesc('total')->first();
            $groomingServiceShare = $groomingRevenueTotal > 0 && $topService ? (($topService->total / $groomingRevenueTotal) * 100) : 0;
            $groomingColors = ['#8b5cf6', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#14b8a6'];
            $groomingSlices = [];
            $groomingSliceStart = 0;

            foreach ($items as $index => $item) {
                $serviceShare = $groomingRevenueTotal > 0 ? ((float) $item->total / $groomingRevenueTotal) * 100 : 0;
                $groomingSlices[] = [
                    'label' => $item->item_name,
                    'value' => (float) $item->total,
                    'share' => $serviceShare,
                    'start' => $groomingSliceStart,
                    'end' => $groomingSliceStart + $serviceShare,
                    'color' => $groomingColors[$index % count($groomingColors)],
                ];
                $groomingSliceStart += $serviceShare;
            }

            $groomingPieGradient = collect($groomingSlices)
                ->map(fn ($slice) => $slice['color'] . ' ' . $slice['start'] . '% ' . $slice['end'] . '%')
                ->implode(', ');
        @endphp

        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="grooming-mix-title">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 id="grooming-mix-title" class="text-base font-bold text-slate-900">Service mix</h2>
                    <p class="mt-1 text-xs text-slate-500">Share of revenue by top grooming service.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-violet-500"></span> Revenue
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)] lg:items-center">
                <div class="flex items-center justify-center py-2">
                    <div class="relative flex shrink-0 items-center justify-center rounded-full shadow-inner ring-8 ring-slate-100" style="width: 14rem; height: 14rem; flex: 0 0 14rem; background: radial-gradient(circle, #ffffff 0 62%, transparent 63% 100%), conic-gradient({{ $groomingPieGradient ?: '#e2e8f0 0 100%' }});">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white text-center shadow-inner">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top</p>
                                <p class="text-lg font-bold text-slate-900">{{ $topService ? number_format((float) $groomingServiceShare, 0) . '%' : '0%' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total grooming revenue</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">₱{{ number_format((float) $groomingRevenueTotal, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Revenue by service</p>
                        <div class="space-y-2">
                            @forelse($groomingSlices as $slice)
                                <div class="flex items-center justify-between gap-3 text-xs text-slate-600">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $slice['color'] }};"></span>
                                        <span class="truncate">{{ $slice['label'] }}</span>
                                    </div>
                                    <span class="shrink-0 font-semibold text-slate-700">{{ number_format($slice['share'], 0) }}%</span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">No grooming sales for these dates.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Top Services --}}
        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="grooming-chart-title">
            <div>
                <h2 id="grooming-chart-title" class="text-base font-bold text-slate-900">Top services by revenue</h2>
                <p class="mt-1 text-xs text-slate-500">A quick comparison of completed grooming service value.</p>
            </div>
            @php $groomingChartMax = max((float) ($items->take(8)->max('total') ?? 0), 1); @endphp
            <div class="mt-5 space-y-4">
                @forelse($items->take(8) as $item)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0 truncate font-semibold text-slate-700">{{ $item->item_name }}</span>
                            <span class="shrink-0 font-semibold tabular-nums text-slate-900">₱{{ number_format((float) $item->total, 2) }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $item->item_name }}: ₱{{ number_format((float) $item->total, 2) }} revenue">
                            <div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, ((float) $item->total / $groomingChartMax) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500">No grooming sales for these dates.</p>
                @endforelse
            </div>
        </section>


        {{-- Grooming Services --}}
        <div class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Section Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Service performance
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Services ranked by quantity, with their total sales value.
                    </p>
                </div>

                @if($items->count() > 0)
                    <div class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ $items->count() }}
                        {{ $items->count() === 1 ? 'service' : 'services' }}
                    </div>
                @endif

            </div>


            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                #
                            </th>

                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Service
                            </th>

                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Quantity
                            </th>

                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Revenue
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($paginatedItems as $index => $item)

                            <tr class="transition hover:bg-slate-50">

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-400">
                                    {{ $paginatedItems->firstItem() + $index }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 3l1.9 4.6L19 9.5l-4 3.3 1.2 5.2-4.2-2.7-4.2 2.7L9 12.8 5 9.5l5.1-1.9L12 3z"/>
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">
                                                {{ $item->item_name }}
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                Grooming service
                                            </p>
                                        </div>

                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-700">
                                    {{ number_format((float) $item->quantity) }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <span class="text-sm font-bold text-slate-900">
                                        ₱{{ number_format((float) $item->total, 2) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-6 py-14 text-center">

                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 3l1.9 4.6L19 9.5l-4 3.3 1.2 5.2-4.2-2.7-4.2 2.7L9 12.8 5 9.5l5.1-1.9L12 3z"/>
                                            </svg>
                                        </div>

                                        <h3 class="text-sm font-bold text-slate-900">
                                            No grooming sales found
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            There are no grooming sales recorded for the selected period.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    @if($items->count() > 0)

                        <tfoot class="border-t border-slate-200 bg-slate-50">

                            <tr>

                                <th colspan="2"
                                    class="px-6 py-4 text-left text-sm font-bold text-slate-900">
                                    Total
                                </th>

                                <th class="px-6 py-4 text-right text-sm font-bold text-slate-900">
                                    {{ number_format((float) $items->sum('quantity')) }}
                                </th>

                                <th class="px-6 py-4 text-right text-sm font-bold text-emerald-600">
                                    ₱{{ number_format((float) $items->sum('total'), 2) }}
                                </th>

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>


            {{-- Mobile Cards --}}
            <div class="divide-y divide-slate-100 md:hidden">

                @forelse($paginatedItems as $index => $item)

                    <div class="p-4">

                        <div class="flex items-start justify-between gap-4">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 3l1.9 4.6L19 9.5l-4 3.3 1.2 5.2-4.2-2.7-4.2 2.7L9 12.8 5 9.5l5.1-1.9L12 3z"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">
                                        {{ $item->item_name }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Service #{{ $paginatedItems->firstItem() + $index }}
                                    </p>
                                </div>

                            </div>

                            <p class="shrink-0 text-sm font-bold text-emerald-600">
                                ₱{{ number_format((float) $item->total, 2) }}
                            </p>

                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                                    Quantity
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    {{ number_format((float) $item->quantity) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                                    Revenue
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    ₱{{ number_format((float) $item->total, 2) }}
                                </p>
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-5 py-14 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 3l1.9 4.6L19 9.5l-4 3.3 1.2 5.2-4.2 2.7L9 12.8 5 9.5l5.1-1.9L12 3z"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-bold text-slate-900">
                            No grooming sales found
                        </h3>

                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                            There are no grooming sales recorded for the selected period.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- Mobile Total --}}
            @if($items->count() > 0)

                <div class="border-t border-slate-200 bg-slate-50 p-4 md:hidden">

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">
                            Total Services
                        </span>

                        <span class="text-sm font-bold text-slate-900">
                            {{ number_format((float) $items->sum('quantity')) }}
                        </span>
                    </div>

                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">
                            Total Revenue
                        </span>

                        <span class="text-base font-bold text-emerald-600">
                            ₱{{ number_format((float) $items->sum('total'), 2) }}
                        </span>
                    </div>

                </div>

            @endif

            @if($paginatedItems->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    {{ $paginatedItems->links() }}
                </div>
            @endif

        </div>

    </div>
</div>

@endsection