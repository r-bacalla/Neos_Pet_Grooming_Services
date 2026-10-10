@extends('layouts.app')

@section('title', 'Grooming Payment')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-4xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <header class="rounded-xl bg-emerald-950 px-5 py-5 text-white shadow-sm sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Grooming checkout</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-white">Grooming Payment</h1>
                <p class="mt-1 text-sm text-emerald-100/80">Review the visit details and complete checkout.</p>
            </div>
        </header>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">Visit details</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $appointment->customer->name }} · {{ $appointment->customer->phone }}</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($appointments as $visitAppointment)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900">{{ $visitAppointment->pet->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $visitAppointment->service?->name ?? 'Grooming service' }} · {{ $visitAppointment->pet_size ?: ($visitAppointment->pet->weight_category ?? 'Size not recorded') }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-slate-900">₱{{ number_format((float) ($visitAppointment->service?->rate ?? $visitAppointment->grooming_price), 2) }}</p>
                    </div>
                @endforeach
            </div>
            @if($addonLines->isNotEmpty())
                <div class="border-t border-slate-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Grooming add-ons</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($addonLines as $line)
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-600">{{ $line['name'] }} × {{ $line['quantity'] }}</span>
                                <span class="font-medium text-slate-900">₱{{ number_format($line['total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

<div @keydown.escape.window="window.location.href = @js(route('grooming.index'))" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6">
    <section role="dialog" aria-modal="true" aria-labelledby="grooming-payment-title" class="flex max-h-[95vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <header class="flex shrink-0 items-center justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-4 py-3 text-white sm:px-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Checkout</p>
                <h2 id="grooming-payment-title" class="text-xl font-bold text-white">Complete Payment</h2>
            </div>
            <a href="{{ route('grooming.index') }}" aria-label="Close payment" class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20">Close</a>
        </header>

        <form method="POST" action="{{ route('pos.checkout') }}" class="flex min-h-0 flex-1 flex-col" x-data="{ amountPaid: '', total: @js((float) $total), paymentMethod: 'cash', paymentAttempted: false }" x-init="$nextTick(() => $refs.amountPaidInput?.focus())" @submit="if (Number(amountPaid || 0) < total) { $event.preventDefault(); paymentAttempted = true }">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $appointment->customer_id }}">
            <input type="hidden" name="grooming_appointment_id" value="{{ $appointment->id }}">

            <div class="space-y-3 p-4 sm:p-5">
                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Payment could not be completed.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="app-payment-surface rounded-xl bg-slate-950 p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Amount Due</p>
                            <p class="mt-1 text-2xl font-bold">₱{{ number_format($total, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Pets</p>
                            <p class="mt-1 text-lg font-bold">{{ $appointments->count() }}</p>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-white/10 pt-2">
                        <p class="text-xs text-slate-400">{{ $appointment->customer->name }}</p>
                        <p class="mt-1 text-sm font-semibold">{{ $appointments->map(fn ($visitAppointment) => $visitAppointment->pet->name)->implode(', ') }}</p>
                    </div>
                </div>

                <section>
                    <label for="grooming_payment_method" class="mb-1 block text-sm font-semibold text-slate-700">Payment Method</label>
                    <select id="grooming_payment_method" name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 focus:border-slate-400 focus:bg-white focus:ring-slate-200" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </section>

                <section>
                    <label for="grooming_amount_paid" class="mb-1 block text-sm font-semibold text-slate-700">Amount Paid</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">₱</span>
                        <input id="grooming_amount_paid" x-ref="amountPaidInput" type="number" name="amount_paid" min="0" step="0.01" x-model.number="amountPaid" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-9 text-lg font-bold focus:border-slate-400 focus:bg-white focus:ring-slate-200">
                    </div>
                </section>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm" role="status" aria-live="polite">
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500">Subtotal</span>
                        <strong class="text-slate-900">₱{{ number_format($subtotal, 2) }}</strong>
                    </div>
                    @if($voucher)
                        <div class="mt-3 flex justify-between gap-3">
                            <span class="text-slate-500">Loyalty discount ({{ number_format((float) $voucher->discount_percent, 0) }}%)</span>
                            <strong class="text-emerald-700">−₱{{ number_format($discount, 2) }}</strong>
                        </div>
                    @endif
                    <div class="mt-2 flex justify-between border-t border-slate-200 pt-2 text-sm">
                        <span class="font-semibold text-slate-700">Total</span>
                        <strong class="text-base text-slate-900">₱{{ number_format($total, 2) }}</strong>
                    </div>
                    <div class="mt-2 flex justify-between">
                        <span class="text-sm text-slate-500">Change</span>
                        <strong class="text-lg" :class="!paymentAttempted ? 'text-slate-500' : (amountPaid >= total ? 'text-emerald-600' : 'text-red-500')">₱<span x-text="Math.max(0, Number(amountPaid || 0) - total).toFixed(2)"></span></strong>
                    </div>
                    <div x-show="paymentAttempted && amountPaid < total" x-cloak class="mt-2 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Amount paid is less than the total.</div>
                </div>
            </div>

            <footer class="shrink-0 border-t border-slate-100 bg-white p-3 sm:p-4">
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800">Complete Grooming Payment</button>
                <a href="{{ route('grooming.index') }}" class="mt-1 flex w-full items-center justify-center rounded-xl py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">Cancel</a>
            </footer>
        </form>
    </section>
</div>
@endsection