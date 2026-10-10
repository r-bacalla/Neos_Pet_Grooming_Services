@extends('layouts.app')

@section('title', 'Payment Done')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:py-12">
    <main class="mx-auto w-full max-w-xl">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between gap-3 bg-emerald-950 px-5 py-4 text-white sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Checkout complete</p>
                    <p class="mt-1 text-sm font-semibold">{{ $sale->invoice_number ?: '#' . $sale->id }}</p>
                </div>
                <span class="rounded-full border border-emerald-300/30 bg-emerald-100/10 px-3 py-1.5 text-xs font-semibold text-emerald-100">Paid</span>
            </div>

            <div class="px-5 py-8 text-center sm:px-8 sm:py-10">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-4 border-emerald-500 bg-emerald-50 text-emerald-700">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">Payment Done</h1>
                <p class="mt-2 text-sm text-slate-500">Your payment has been recorded. View the receipt for your records.</p>

                <dl class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-sm text-slate-500">Amount paid</dt>
                        <dd class="text-lg font-bold text-slate-900">₱{{ number_format((float) $sale->amount_paid, 2) }}</dd>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                        <dt class="text-sm text-slate-500">Payment method</dt>
                        <dd class="text-sm font-semibold capitalize text-slate-800">{{ str_replace('_', ' ', $sale->payment_method) }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <a href="{{ route('receipts.show', $sale) }}" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-800 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900">View Receipt</a>
                </div>
                <a href="{{ $returnUrl }}" class="mt-4 inline-flex min-h-10 items-center justify-center px-4 text-sm font-semibold text-slate-500 transition hover:text-slate-900">{{ $returnLabel }}</a>
            </div>
        </section>
    </main>
</div>
@endsection