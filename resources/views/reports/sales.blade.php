@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')

<div x-data="{ expandedSections: { howCustomersPaid: false, bestSellingProducts: false } }" class="report-page min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Sales Report
                </h1>

                <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">
                    See how much the business collected and how customers paid during the selected dates.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a
                    href="{{ auth()->user()->isFrontDesk() ? route('dashboard') : route('reports.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ auth()->user()->isFrontDesk() ? 'Back to Dashboard' : 'Back to Reports' }}
                </a>
                @unless(auth()->user()->isFrontDesk())
                    @include('reports.partials.delete-all')
                @endunless
            </div>

        </div>


        {{-- Date Filter --}}
        <div class="inventory-content-frame mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Choose report dates
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Only completed sales between these dates are included.
                        </p>
                    </div>

                </div>
            </div>

            <form method="{{ auth()->user()->isFrontDesk() ? 'GET' : 'POST' }}" action="{{ auth()->user()->isFrontDesk() ? route('reports.sales') : route('reports.generate', 'sales') }}">
                @unless(auth()->user()->isFrontDesk())
                    @csrf
                @endunless
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-[1fr_1fr_auto] lg:items-end">

                    <div>
                        <label for="from"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            From
                        </label>

                        <input
                            type="date"
                            name="from"
                            id="from"
                            value="{{ $from }}"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100"
                        >
                    </div>

                    <div>
                        <label for="to"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            To
                        </label>

                        <input
                            type="date"
                            name="to"
                            id="to"
                            value="{{ $to }}"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35m2.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Download sales PDF
                    </button>

                </div>
            </form>

        </div>


        {{-- Summary Cards --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Transactions --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Completed transactions
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($summary->transactions ?? 0) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Completed sales
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                        <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14H5V6a2 2 0 012-2z"/>
                        </svg>
                    </div>

                </div>
            </div>


            {{-- Subtotal --}}
            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Sales before discount
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            ₱{{ number_format((float) ($summary->subtotal ?? 0), 2) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Amount charged before discounts and VAT
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-12v12m0-12c1.66 0 3 .67 3 1.5M12 6c-1.66 0-3 .67-3 1.5"/>
                        </svg>
                    </div>

                </div>
            </div>


            {{-- Total Sales --}}
            <div class="inventory-content-frame rounded-2xl bg-slate-900 p-5 shadow-sm">
                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Total Sales
                        </p>

                        <p class="mt-2 text-2xl font-bold text-white">
                            ₱{{ number_format((float) ($summary->total ?? 0), 2) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Final sales amount
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-12v12"/>
                        </svg>
                    </div>

                </div>
            </div>

        </div>


        {{-- Period & Discount --}}
        <div class="mb-6 grid gap-4 lg:grid-cols-2">

            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Reporting Period
                        </p>

                        <p class="mt-1 truncate text-base font-bold text-slate-900 sm:text-lg">
                            {{ \Carbon\Carbon::parse($from)->format('M d, Y') }}
                            <span class="mx-1 text-slate-300">—</span>
                            {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
                        </p>
                    </div>

                </div>
            </div>


            <div class="inventory-content-frame rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 14l6-6m2.5-2.5l.5-.5M6 18h.01M18 6h.01M7 7h.01"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Total Discounts
                        </p>

                        <p class="mt-1 text-lg font-bold text-slate-900">
                            ₱{{ number_format((float) ($summary->discount ?? 0), 2) }}
                        </p>
                    </div>

                </div>
            </div>

        </div>


        {{-- Sales Mix --}}
        @php
            $salesPaymentTotal = (float) ($paymentMethods->sum('total') ?? 0);
            $salesTopPayment = $paymentMethods->sortByDesc('total')->first();
            $salesPaymentShare = $salesPaymentTotal > 0 && $salesTopPayment ? (($salesTopPayment->total / $salesPaymentTotal) * 100) : 0;
            $paymentColors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];
            $paymentSlices = [];
            $paymentSliceStart = 0;

            foreach ($paymentMethods as $index => $payment) {
                $paymentShare = $salesPaymentTotal > 0 ? ((float) $payment->total / $salesPaymentTotal) * 100 : 0;
                $paymentSlices[] = [
                    'label' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Unknown')),
                    'value' => (float) $payment->total,
                    'share' => $paymentShare,
                    'start' => $paymentSliceStart,
                    'end' => $paymentSliceStart + $paymentShare,
                    'color' => $paymentColors[$index % count($paymentColors)],
                ];
                $paymentSliceStart += $paymentShare;
            }

            $paymentPieGradient = collect($paymentSlices)
                ->map(fn ($slice) => $slice['color'] . ' ' . $slice['start'] . '% ' . $slice['end'] . '%')
                ->implode(', ');

            $productSalesTotal = (float) $productSales->sum('total');
            $productSalesForPie = $productSales->take(5);
            $productColors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];
            $productSlices = [];
            $productSliceStart = 0;

            foreach ($productSalesForPie as $index => $product) {
                $productShare = $productSalesTotal > 0 ? ((float) $product->total / $productSalesTotal) * 100 : 0;
                $productSlices[] = [
                    'label' => $product->item_name,
                    'value' => (float) $product->total,
                    'share' => $productShare,
                    'start' => $productSliceStart,
                    'end' => $productSliceStart + $productShare,
                    'color' => $productColors[$index % count($productColors)],
                ];
                $productSliceStart += $productShare;
            }

            $otherProductSales = max(0, $productSalesTotal - (float) $productSalesForPie->sum('total'));
            if ($otherProductSales > 0) {
                $otherProductShare = ($otherProductSales / $productSalesTotal) * 100;
                $productSlices[] = [
                    'label' => 'Other products',
                    'value' => $otherProductSales,
                    'share' => $otherProductShare,
                    'start' => $productSliceStart,
                    'end' => 100,
                    'color' => $productColors[5],
                ];
            }

            $productPieGradient = collect($productSlices)
                ->map(fn ($slice) => $slice['color'] . ' ' . $slice['start'] . '% ' . $slice['end'] . '%')
                ->implode(', ');
            $mixBestSeller = $productSales->first();
        @endphp

        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="sales-mix-title">
            <div>
                <h2 id="sales-mix-title" class="text-base font-bold text-slate-900">Sales mix</h2>
                <p class="mt-1 text-xs text-slate-500">Compare payment methods and product revenue for the selected dates.</p>
            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <section class="rounded-xl border border-slate-200 p-4" aria-labelledby="sales-payment-mix-title">
                    <div>
                        <h3 id="sales-payment-mix-title" class="text-sm font-bold text-slate-900">Payment mix</h3>
                        <p class="mt-1 text-xs text-slate-500">Share of total sales by payment method.</p>
                    </div>
                    <div class="mt-4 grid gap-4 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-center">
                        <div class="flex items-center justify-center py-2">
                            <div class="relative flex shrink-0 items-center justify-center rounded-full shadow-inner ring-8 ring-slate-100" style="width: 14rem; height: 14rem; flex: 0 0 14rem; background: radial-gradient(circle, #ffffff 0 62%, transparent 63% 100%), conic-gradient({{ $paymentPieGradient ?: '#e2e8f0 0 100%' }});">
                                <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white text-center shadow-inner">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top method</p>
                                        <p class="text-lg font-bold text-slate-900">{{ $salesTopPayment ? number_format((float) $salesPaymentShare, 0) . '%' : '0%' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total sales</p>
                                <p class="mt-1 text-lg font-bold text-slate-900">₱{{ number_format($salesPaymentTotal, 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Payment methods</p>
                                <div class="space-y-2">
                                    @forelse($paymentSlices as $slice)
                                        <div class="flex items-center justify-between gap-2 text-xs text-slate-600">
                                            <span class="flex min-w-0 items-center gap-2"><span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $slice['color'] }};"></span><span class="truncate">{{ $slice['label'] }}</span></span>
                                            <span class="shrink-0 font-semibold">{{ number_format($slice['share'], 0) }}%</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500">No payment data for these dates.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 p-4" aria-labelledby="product-sales-mix-title">
                    <div>
                        <h3 id="product-sales-mix-title" class="text-sm font-bold text-slate-900">Product sales mix</h3>
                        <p class="mt-1 text-xs text-slate-500">Share of product revenue by item.</p>
                    </div>
                    <div class="mt-4">
                    <div class="grid gap-4 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-center">
                        <div class="flex items-center justify-center py-2">
                            <div class="relative flex shrink-0 items-center justify-center rounded-full shadow-inner ring-8 ring-slate-100" style="width: 14rem; height: 14rem; flex: 0 0 14rem; background: radial-gradient(circle, #ffffff 0 62%, transparent 63% 100%), conic-gradient({{ $productPieGradient ?: '#e2e8f0 0 100%' }});">
                                <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white px-2 text-center shadow-inner">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product sales</p>
                                        <p class="text-sm font-bold text-slate-900">₱{{ number_format($productSalesTotal, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Revenue by product</p>
                                <div class="space-y-2">
                                    @forelse($productSlices as $slice)
                                        <div class="flex items-center justify-between gap-2 text-xs text-slate-600">
                                            <span class="flex min-w-0 items-center gap-2"><span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $slice['color'] }};"></span><span class="truncate">{{ $slice['label'] }}</span></span>
                                            <span class="shrink-0 font-semibold">{{ number_format($slice['share'], 0) }}%</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500">No product sales for these dates.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="rounded-xl bg-blue-50 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Best seller</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-900">{{ $mixBestSeller?->item_name ?? 'None' }}</p>
                                <p class="mt-0.5 text-xs text-slate-600">{{ $mixBestSeller ? number_format((float) $mixBestSeller->quantity) . ' units sold' : 'No product sales for these dates' }}</p>
                            </div>
                            <div class="rounded-xl bg-amber-50 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Unsold active items ({{ $unsoldProducts->count() }})</p>
                                @if($unsoldProducts->isNotEmpty())
                                    <ul class="mt-1 space-y-0.5 text-xs text-slate-700">
                                        @foreach($unsoldProducts->take(5) as $unsoldProduct)
                                            <li class="truncate">{{ $unsoldProduct }}</li>
                                        @endforeach
                                    </ul>
                                    @if($unsoldProducts->count() > 5)
                                        <p class="mt-1 text-xs text-slate-600">And {{ $unsoldProducts->count() - 5 }} more</p>
                                    @endif
                                @else
                                    <p class="mt-1 text-xs text-slate-600">All active products had sales in this period.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </section>
            </div>
        </section>

        {{-- Payment Snapshot --}}
        <section class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="sales-payment-chart-title">
            <button type="button" @click="expandedSections.howCustomersPaid = !expandedSections.howCustomersPaid" :aria-expanded="expandedSections.howCustomersPaid" aria-controls="payment-breakdown-content" class="flex w-full items-center justify-between gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                <span>
                    <span id="sales-payment-chart-title" class="block text-base font-bold text-slate-900">How customers paid</span>
                    <span class="mt-1 block text-xs text-slate-500">Payment chart and method details for completed sales.</span>
                </span>
                <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-slate-500">
                    <span x-text="expandedSections.howCustomersPaid ? 'Hide' : 'Show'"></span>
                    <svg class="h-4 w-4 transition-transform" :class="expandedSections.howCustomersPaid ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                </span>
            </button>
            <div id="payment-breakdown-content" x-show="expandedSections.howCustomersPaid" x-cloak x-transition>
            @php $paymentChartMax = max((float) ($paymentMethods->max('total') ?? 0), 1); @endphp
            <div class="mt-5 space-y-4">
                @forelse($paymentMethods as $payment)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $payment->payment_method ?? 'Unknown') }}</span>
                            <span class="shrink-0 font-semibold tabular-nums text-slate-900">₱{{ number_format((float) $payment->total, 2) }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ ucfirst($payment->payment_method ?? 'Unknown') }}: ₱{{ number_format((float) $payment->total, 2) }}">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, ((float) $payment->total / $paymentChartMax) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500">No completed payment data for these dates.</p>
                @endforelse
            </div>
            {{-- Payment Methods --}}
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">

            <div class="border-b border-slate-200 px-5 py-3 sm:px-6">
                <div class="flex items-center gap-3 p-2">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                        <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h2m3 0h2m-9 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Payment Methods
                        </h2>

                        <p class="mt-0.5 text-sm text-slate-500">
                            Breakdown of completed transactions by payment method.
                        </p>
                    </div>

                </div>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Payment Method
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Transactions
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Total
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($paymentMethods as $payment)

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                            <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M3 10h18M7 15h2m3 0h2m-9 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>

                                        <span class="font-semibold capitalize text-slate-800">
                                            {{ ucfirst($payment->payment_method ?? 'Unknown') }}
                                        </span>

                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ number_format($payment->transactions) }}
                                </td>

                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    ₱{{ number_format((float) $payment->total, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">

                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 10h18M7 15h2m3 0h2m-9 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>

                                    <p class="mt-3 text-sm font-medium text-slate-700">
                                        No payment data found
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        There are no recorded payments for this period.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            </div>
            </div>
        </section>


        @php
            $productSalesTotalUnits = (float) $productSales->sum('quantity');
            $productSalesTotalRevenue = (float) $productSales->sum('total');
            $productSalesChartMax = max((float) ($productSales->max('quantity') ?? 0), 1);
            $productBestSeller = $productSales->first();
        @endphp

        <section class="inventory-content-frame mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" aria-labelledby="sales-products-title">
            <div class="border-b border-slate-200 px-5 py-3 sm:px-6">
                <button type="button" @click="expandedSections.bestSellingProducts = !expandedSections.bestSellingProducts" :aria-expanded="expandedSections.bestSellingProducts" aria-controls="best-selling-products-content" class="flex w-full items-center justify-between gap-3 rounded-lg p-2 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                    <span>
                        <span id="sales-products-title" class="block text-base font-bold text-slate-900">Best-selling products</span>
                        <span class="mt-1 block text-sm text-slate-500">POS product sales ranked by units sold during the selected dates.</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-slate-500">
                        <span x-text="expandedSections.bestSellingProducts ? 'Hide' : 'Show'"></span>
                        <svg class="h-4 w-4 transition-transform" :class="expandedSections.bestSellingProducts ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </span>
                </button>
            </div>

            <div id="best-selling-products-content" x-show="expandedSections.bestSellingProducts" x-cloak x-transition>
            <div class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-3 sm:px-6">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product units sold</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($productSalesTotalUnits) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product sales value</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">₱{{ number_format($productSalesTotalRevenue, 2) }}</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Best seller</p>
                    <p class="mt-1 truncate text-lg font-bold text-slate-900">{{ $productBestSeller?->item_name ?? 'None' }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $productBestSeller ? number_format((float) $productBestSeller->quantity) . ' units sold' : 'No product sales for these dates' }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Product</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Units sold</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Sales value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($productSales as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="min-w-56 px-5 py-3.5">
                                    <span class="font-semibold text-slate-800">{{ $item->item_name }}</span>
                                    <span class="mt-2 block h-2 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $item->item_name }}: {{ number_format((float) $item->quantity) }} units sold">
                                        <span class="block h-full rounded-full bg-blue-500" style="width: {{ min(100, ((float) $item->quantity / $productSalesChartMax) * 100) }}%"></span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm font-semibold tabular-nums text-slate-700">{{ number_format((float) $item->quantity) }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm font-semibold tabular-nums text-slate-900">₱{{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-500">No products were sold during this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        {{-- Completed Sales --}}
        <div class="inventory-content-frame overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h8.586a2 2 0 011.414.586l2.414 2.414A2 2 0 0120 7.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Completed Sales
                        </h2>

                        <p class="mt-0.5 text-sm text-slate-500">
                            Detailed transaction history for the selected period.
                        </p>
                    </div>

                </div>
            </div>


            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto xl:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Date
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Customer
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Cashier
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Payment
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Items
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Subtotal
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Discount
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                VAT
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($sales as $sale)

                            <tr class="transition hover:bg-slate-50">

                                <td class="w-32 px-5 py-4 text-sm text-slate-600">
                                    <span class="block whitespace-nowrap">{{ $sale->created_at->format('M d, Y') }}</span>
                                    <span class="mt-0.5 block whitespace-nowrap text-xs text-slate-400">{{ $sale->created_at->format('h:i A') }}</span>
                                </td>

                                <td class="min-w-[12rem] max-w-[18rem] px-5 py-4">
                                    <span class="block break-words font-medium leading-5 text-slate-800">
                                        {{ $sale->customer?->name ?? 'Walk-in Customer' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $sale->cashier?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">
                                        {{ ucfirst($sale->payment_method ?? '—') }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center text-sm font-semibold text-slate-700">
                                    {{ $sale->items->sum('quantity') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-slate-600">
                                    ₱{{ number_format((float) $sale->subtotal, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-slate-600">
                                    ₱{{ number_format((float) $sale->discount, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-slate-600">
                                    ₱{{ number_format((float) $sale->vat_amount, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-bold text-slate-900">
                                    ₱{{ number_format((float) $sale->total, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="px-6 py-16 text-center">

                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h8.586a2 2 0 011.414.586l2.414 2.414A2 2 0 0120 7.414V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                        No completed sales found
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        No completed sales were recorded for this period.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile / Tablet Cards --}}
            <div class="divide-y divide-slate-200 xl:hidden">

                @forelse($sales as $sale)

                    <div class="p-5 sm:p-6">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $sale->customer?->name ?? 'Walk-in Customer' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $sale->created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">
                                {{ ucfirst($sale->payment_method ?? '—') }}
                            </span>

                        </div>


                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-400">
                                    Items
                                </p>

                                <p class="mt-1 font-bold text-slate-900">
                                    {{ $sale->items->sum('quantity') }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-400">
                                    Subtotal
                                </p>

                                <p class="mt-1 font-semibold text-slate-900">
                                    ₱{{ number_format((float) $sale->subtotal, 2) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-400">
                                    Discount
                                </p>

                                <p class="mt-1 font-semibold text-slate-900">
                                    ₱{{ number_format((float) $sale->discount, 2) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-900 p-3">
                                <p class="text-xs text-slate-400">
                                    Total
                                </p>

                                <p class="mt-1 font-bold text-white">
                                    ₱{{ number_format((float) $sale->total, 2) }}
                                </p>
                            </div>

                        </div>


                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">

                            <div>
                                <span class="text-slate-400">
                                    Cashier
                                </span>

                                <span class="ml-1 font-medium text-slate-700">
                                    {{ $sale->cashier?->name ?? '—' }}
                                </span>
                            </div>

                            <div>
                                <span class="text-slate-400">
                                    VAT
                                </span>

                                <span class="ml-1 font-semibold text-slate-700">
                                    ₱{{ number_format((float) $sale->vat_amount, 2) }}
                                </span>
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h8.586a2 2 0 011.414.586l2.414 2.414A2 2 0 0120 7.414V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                            No completed sales found
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            No completed sales were recorded for this period.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- Pagination --}}
        @if($sales->hasPages())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                {{ $sales->links() }}
            </div>
        @endif

    </div>
</div>

@endsection