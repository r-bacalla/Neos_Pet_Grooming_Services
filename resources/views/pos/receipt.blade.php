@extends('layouts.app')

@section('title', 'Receipt')

@section('content')

<div class="min-h-screen bg-slate-50 py-6 sm:py-10">
    <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">

    {{-- RECEIPT --}}
    <div class="receipt-sheet overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

        {{-- HEADER --}}
        <div class="bg-emerald-950 px-6 py-6 text-white sm:px-10">
            <div class="flex flex-wrap items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <x-company-logo class="h-12 w-24 object-contain" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Official sales receipt</p>
                        <h1 class="mt-1 text-xl font-bold tracking-tight sm:text-2xl">Neo's Pet Grooming</h1>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-100/10 px-3 py-2 text-xs font-semibold text-emerald-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Payment completed
                </span>
            </div>
        </div>

        {{-- RECEIPT META --}}
        <div class="grid grid-cols-2 gap-4 border-b border-slate-100 bg-slate-50 px-6 py-5 sm:px-10">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Receipt Number
                </p>
                <p class="mt-1 font-bold text-slate-900">
                    {{ $sale->invoice_number ?: '#' . $sale->id }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Date & Time
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $sale->created_at?->format('M d, Y') }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ $sale->created_at?->format('h:i A') }}
                </p>
            </div>
        </div>

        {{-- CUSTOMER --}}
        <div class="border-b border-slate-100 px-6 py-6 sm:px-10">
            <h2 class="text-sm font-bold text-slate-900">Customer and transaction</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Customer
                    </p>

                    @if($sale->customer)
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ $sale->customer->name }}
                        </p>

                        @if($sale->customer->phone)
                            <p class="mt-0.5 text-sm text-slate-500">
                                {{ $sale->customer->phone }}
                            </p>
                        @endif
                    @else
                        <p class="mt-1 font-semibold text-slate-700">
                            Walk-in Customer
                        </p>
                    @endif
                </div>

                @if($sale->cashier)
                    <div class="sm:text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Cashier
                        </p>

                        <p class="mt-1 font-semibold text-slate-900">
                            {{ $sale->cashier->name }}
                        </p>
                    </div>
                @endif

            </div>
        </div>

        {{-- ITEMS --}}
        <div class="px-6 py-6 sm:px-10">

            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">
                        Purchased Items
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Products and grooming services
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">
                    {{ $sale->items->count() }} {{ $sale->items->count() === 1 ? 'item' : 'items' }}
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-200 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                <th class="px-4 py-3 sm:px-5">
                                    Item
                                </th>

                                <th class="px-3 py-3 text-center">
                                    Qty
                                </th>

                                <th class="px-3 py-3 text-right">
                                    Price
                                </th>

                                <th class="px-4 py-3 text-right sm:px-5">
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach($sale->items as $item)

                                <tr class="hover:bg-slate-50/70">

                                    <td class="px-4 py-4 sm:px-5">
                                        <div class="font-semibold text-slate-900">
                                            {{ $item->item_name }}
                                        </div>

                                        @if((float) $item->discount > 0)
                                            <p class="mt-1 text-xs font-semibold text-emerald-700">
                                                Discount: ₱{{ number_format((float) $item->discount, 2) }}
                                            </p>
                                        @endif

                                        @if($item->item_type === 'service')
                                            <span class="mt-1 inline-flex rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-600">
                                                Service
                                            </span>
                                        @elseif($item->item_type === 'product')
                                            <span class="mt-1 inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-600">
                                                Product
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-4 text-center font-medium text-slate-600">
                                        {{ $item->quantity }}
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-right text-slate-600">
                                        ₱{{ number_format((float) $item->unit_price, 2) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-slate-900 sm:px-5">
                                        ₱{{ number_format((float) $item->line_total, 2) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAYMENT SUMMARY --}}
        <div class="receipt-payment-summary border-t border-slate-100 bg-slate-50 px-6 py-6 sm:px-10">
            <div class="grid gap-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                    <h2 class="text-sm font-bold text-slate-900">Payment details</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-900">₱{{ number_format((float) $sale->subtotal, 2) }}</span></div>
                        <div class="flex justify-between gap-3"><span class="text-slate-500">Discount</span><span class="font-medium text-slate-900">−₱{{ number_format((float) $sale->discount, 2) }}</span></div>
                        <div class="flex justify-between gap-3 border-t border-slate-100 pt-3"><span class="text-slate-500">Amount paid</span><span class="font-semibold text-slate-900">₱{{ number_format((float) $sale->amount_paid, 2) }}</span></div>
                        <div class="flex justify-between gap-3"><span class="text-slate-500">Change</span><span class="font-semibold text-emerald-700">₱{{ number_format((float) $sale->change_amount, 2) }}</span></div>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3"><span class="text-slate-500">Method</span><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">{{ str_replace('_', ' ', $sale->payment_method) }}</span></div>
                    </div>
                </div>
                <div class="app-payment-surface flex flex-col justify-between rounded-xl bg-emerald-950 p-5 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Total paid</p>
                        <p class="mt-2 text-3xl font-bold">₱{{ number_format((float) $sale->total, 2) }}</p>
                    </div>
                    <p class="mt-5 border-t border-white/15 pt-3 text-xs text-emerald-100/80">Thank you for choosing Neo's Pet Grooming.</p>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="border-t border-slate-100 px-6 py-7 text-center sm:px-10">

            <p class="font-semibold text-slate-800">
                Thank you for choosing Neo's Pet Grooming!
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Please keep this receipt for your records.
            </p>

        </div>

    </div>

    {{-- ACTIONS --}}
    <div class="receipt-actions mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">

        <a
            href="{{ $returnUrl }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-900"
        >
            {{ $returnLabel }}
        </a>

        <a href="{{ route('receipts.print', $sale) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700">Print Receipt</a>

    </div>

</div>
</div>

@endsection

@push('styles')

<style>
    [x-cloak] {
        display: none !important;
    }

    @media print {

        @page {
            size: 80mm 300mm;
            margin: 4mm;
        }

        html,
        body {
            width: 80mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        body {
            font-size: 11px !important;
        }

        nav,
        .app-sidebar,
        .receipt-actions {
            display: none !important;
        }

        .min-h-screen {
            min-height: 0 !important;
            background: white !important;
            padding: 0 !important;
        }

        .mx-auto {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
        }

        .rounded-2xl,
        .rounded-xl {
            border-radius: 0 !important;
        }

        .shadow-xl,
        .shadow-sm {
            box-shadow: none !important;
        }

        .border {
            border-color: #e5e7eb !important;
        }

        .bg-slate-950,
        .bg-emerald-950 {
            background: white !important;
            color: black !important;
        }

        .bg-slate-50 {
            background: white !important;
        }

        .receipt-sheet .app-payment-surface {
            background: #064e3b !important;
            color: white !important;
            box-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .receipt-sheet .app-payment-surface p {
            color: white !important;
        }

        .text-white,
        .text-emerald-100,
        .text-emerald-200,
        .text-slate-950,
        .text-slate-900 {
            color: black !important;
        }

        .text-slate-300,
        .text-slate-400,
        .text-slate-500 {
            color: #555 !important;
        }

        .px-6,
        .sm\:px-10 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .py-8,
        .py-7,
        .py-6 {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .mt-4 {
            margin-top: 5px !important;
        }

        .mb-4 {
            margin-bottom: 5px !important;
        }

        .overflow-hidden {
            overflow: visible !important;
        }

        table {
            font-size: 10px !important;
            table-layout: fixed !important;
            width: 100% !important;
        }

        th,
        td {
            padding-right: 2px !important;
            padding-left: 2px !important;
        }

        th:first-child,
        td:first-child {
            width: 44% !important;
            overflow-wrap: anywhere;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 10% !important;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 22% !important;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 24% !important;
        }

        .receipt-sheet {
            width: 100% !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .receipt-payment-summary {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        th,
        td {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
        }
    }
</style>

@if($autoPrint ?? false)
    <script>
        window.addEventListener('load', () => window.print());
    </script>
@endif

@endpush
