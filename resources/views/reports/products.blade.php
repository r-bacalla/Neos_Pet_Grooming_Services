@extends('layouts.app')

@section('title', 'Product Report')

@section('content')

<div class="report-page min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-6 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Product Report
                </h1>

                <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">
                    See which products sold, how many units moved, and the sales value for the selected dates.
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
            </div>

        </div>


        {{-- Date Filter --}}
        <div class="inventory-content-frame mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Choose report dates
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Only completed product sales between these dates are included.
                        </p>
                    </div>

                </div>
            </div>

            <form method="POST" action="{{ route('reports.generate', 'products') }}">
                @csrf
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-[1fr_1fr_auto] lg:items-end">

                    <div>
                        <label for="from" class="mb-2 block text-sm font-semibold text-slate-700">
                            From
                        </label>

                        <input type="date" name="from" id="from" value="{{ $from }}" required
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
                    </div>

                    <div>
                        <label for="to" class="mb-2 block text-sm font-semibold text-slate-700">
                            To
                        </label>

                        <input type="date" name="to" id="to" value="{{ $to }}" required
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
                    </div>

                    <button type="submit"
                            class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35m2.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Download products PDF
                    </button>

                </div>
            </form>

        </div>


        {{-- Report Information --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Reporting Period --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Reporting Period
                        </p>

                        <p class="mt-2 text-lg font-bold text-slate-900">
                            {{ \Carbon\Carbon::parse($from)->format('M d, Y') }}
                            <span class="mx-1 text-slate-300">—</span>
                            {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
                        </p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>

                </div>

            </div>

            {{-- Products Count --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Different products sold
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($items->count()) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Number of distinct product names in the results
                        </p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>

                </div>

            </div>

        </div>

        {{-- Product sales bar chart --}}
        @php
            $barChartMax = max(
                collect($salesTrend ?? [])->max(fn ($point) => (float) ($point['value'] ?? 0)) ?: 0,
                1
            );
        @endphp

        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daily product units sold</h2>
                    <p class="mt-1 text-xs text-slate-500">The value above each bar is the number of product units sold that day.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span> Sold
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-4">
                @if(count($salesTrend ?? []))
                    <div class="flex h-52 items-end gap-2 overflow-x-auto pb-2">
                        @foreach(($salesTrend ?? []) as $point)
                            @php
                                $saleValue = (float) ($point['value'] ?? 0);
                                $saleHeight = $barChartMax > 0 ? ($saleValue / $barChartMax) * 82 : 0;
                            @endphp
                            <div class="flex min-w-[38px] flex-1 flex-col items-center gap-2">
                                <div class="relative flex h-40 w-full items-end justify-center">
                                    <span class="absolute text-[10px] font-semibold tabular-nums text-slate-600" style="bottom: calc({{ $saleHeight }}% + 4px)">{{ number_format($saleValue) }}</span>
                                    <span class="block w-3 rounded-t bg-blue-500" style="height: {{ $saleHeight }}%"></span>
                                </div>
                                <span class="text-[10px] font-medium text-slate-500">{{ $point['label'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-52 items-center justify-center text-sm text-slate-500">No product sales for this date range.</div>
                @endif
            </div>
        </section>

        {{-- Product sales overview --}}
        @php
            $chartTotal = max((float) $items->sum('quantity'), 1);
            $pieColors = ['#08734c', '#0b8f61', '#41a57b', '#83c6a0', '#e9a044', '#055b3c'];
            $pieSegments = [];
            $pieStart = 0;

            foreach ($items->take(6) as $index => $item) {
                $segmentShare = $chartTotal > 0 ? (($item->quantity / $chartTotal) * 100) : 0;
                $pieSegments[] = [
                    'label' => $item->item_name,
                    'value' => $segmentShare,
                    'start' => $pieStart,
                    'end' => $pieStart + $segmentShare,
                    'color' => $pieColors[$index % count($pieColors)],
                ];
                $pieStart += $segmentShare;
            }

            $pieGradient = collect($pieSegments)
                ->map(fn ($segment) => $segment['color'] . ' ' . $segment['start'] . '% ' . $segment['end'] . '%')
                ->implode(', ');

        @endphp

        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="product-sales-overview-title">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 id="product-sales-overview-title" class="text-base font-bold text-slate-900">Product sales overview</h2>
                    <p class="mt-1 text-xs text-slate-500">A round view of how product units are distributed across the selected dates.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-600"></span> Sold
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)] lg:items-center">
                <div class="flex items-center justify-center py-2">
                    <div class="relative flex shrink-0 items-center justify-center rounded-full shadow-inner ring-8 ring-slate-100" style="width: 14rem; height: 14rem; flex: 0 0 14rem; background: radial-gradient(circle, #ffffff 0 62%, transparent 63% 100%), conic-gradient({{ $pieGradient ?: '#e2e8f0 0 100%' }});">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white text-center shadow-inner">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Units sold</p>
                                <p class="text-lg font-bold text-slate-900">{{ number_format((float) $soldTotal) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Units sold</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format((float) $soldTotal) }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Product sales value</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">₱{{ number_format((float) $items->sum('total'), 2) }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Product mix</p>
                        <div class="space-y-2">
                            @foreach($pieSegments as $segment)
                                <div class="flex items-center justify-between gap-3 text-xs text-slate-600">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $segment['color'] }};"></span>
                                        <span class="truncate">{{ $segment['label'] }}</span>
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ number_format((float) $segment['value'], 0) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- Products Sold --}}
        <div class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Section Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Product sales
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Units sold and sales value, ranked by quantity.
                    </p>
                </div>

                @if($items->count() > 0)

                    <div class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ $items->count() }}
                        {{ $items->count() === 1 ? 'product' : 'products' }}
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
                                Product
                            </th>

                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Quantity Sold
                            </th>

                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Sales Total
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

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-900">
                                                {{ $item->item_name }}
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                Product sale
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
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>

                                        </div>

                                        <h3 class="text-sm font-bold text-slate-900">
                                            No product sales found
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            There are no product sales recorded for the selected period.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    {{-- Desktop Total --}}
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

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">

                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-bold text-slate-900">
                                        {{ $item->item_name }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Product #{{ $paginatedItems->firstItem() + $index }}
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
                                    Quantity Sold
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    {{ number_format((float) $item->quantity) }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">

                                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                                    Sales Total
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
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>

                        </div>

                        <h3 class="mt-4 text-sm font-bold text-slate-900">
                            No product sales found
                        </h3>

                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                            There are no product sales recorded for the selected period.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- Mobile Total --}}
            @if($items->count() > 0)

                <div class="border-t border-slate-200 bg-slate-50 p-4 md:hidden">

                    <div class="flex items-center justify-between">

                        <span class="text-sm font-bold text-slate-900">
                            Total Quantity
                        </span>

                        <span class="text-sm font-bold text-slate-900">
                            {{ number_format((float) $items->sum('quantity')) }}
                        </span>

                    </div>

                    <div class="mt-2 flex items-center justify-between">

                        <span class="text-sm font-bold text-slate-900">
                            Total Sales
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