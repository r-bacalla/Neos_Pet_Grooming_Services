@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:p-5">
            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                Settings
            </h1>

            <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">
                Configure product expiry warnings and automatic discounts.
            </p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <div>
                        <p class="font-semibold text-emerald-900">
                            Settings saved
                        </p>

                        <p class="mt-0.5 text-sm text-emerald-700">
                            {{ session('success') }}
                        </p>
                    </div>

                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="font-semibold text-red-900">
                            Please check the following
                        </p>

                        <ul class="mt-2 space-y-1 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"></span>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        @endif

        {{-- Settings Card --}}
        <div class="inventory-content-frame overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            {{-- Card Header --}}
            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">
                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V20h-2v-.08a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06-1.42-1.42.06-.06A1.7 1.7 0 009.4 15a1.7 1.7 0 00-1.56-1.03H7v-2h.84A1.7 1.7 0 009.4 10a1.7 1.7 0 00-.34-1.88L9 8.06l1.42-1.42.06.06a1.7 1.7 0 001.88.34A1.7 1.7 0 0013.39 5.5V5h2v.5a1.7 1.7 0 001.03 1.54 1.7 1.7 0 001.88-.34l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0019.4 10a1.7 1.7 0 001.56 1.03H21v2h-.04A1.7 1.7 0 0019.4 15z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Product Expiry Settings
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Control when products receive expiry warnings and automatic discounts.
                        </p>
                    </div>

                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="divide-y divide-slate-200">

                    {{-- Expiry Warning --}}
                    <div class="px-5 py-6 sm:px-7 sm:py-7">

                        <div class="grid gap-6 lg:grid-cols-[1fr_320px] lg:items-start">

                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <label for="expiry_warning_days"
                                               class="text-sm font-bold text-slate-900">
                                            Expiry Warning Period
                                        </label>
                                    </div>
                                </div>

                                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">
                                    Products will be marked as almost expired when they are
                                    within this number of days from their expiry date.
                                </p>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="expiry_warning_days"
                                        id="expiry_warning_days"
                                        min="1"
                                        max="365"
                                        value="{{ old('expiry_warning_days', $expiryWarningDays) }}"
                                        required
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 pr-16 text-base font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-4 focus:ring-slate-100"
                                    >

                                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-medium text-slate-400">
                                        days
                                    </span>
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    Allowed range: 1–365 days
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Discount Percentage --}}
                    <div class="px-5 py-6 sm:px-7 sm:py-7">

                        <div class="grid gap-6 lg:grid-cols-[1fr_320px] lg:items-start">

                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 14l6-6m2.5-2.5l.5-.5M6 18h.01M18 6h.01M7 7h.01"/>
                                        </svg>
                                    </div>

                                    <label for="expiry_discount_percent"
                                           class="text-sm font-bold text-slate-900">
                                        Near-Expiry Discount
                                    </label>
                                </div>

                                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">
                                    This discount is applied automatically to products that
                                    are near their expiry date.
                                </p>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="expiry_discount_percent"
                                        id="expiry_discount_percent"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        value="{{ old('expiry_discount_percent', $expiryDiscountPercent) }}"
                                        required
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 pr-12 text-base font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-4 focus:ring-slate-100"
                                    >

                                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-lg font-semibold text-slate-400">
                                        %
                                    </span>
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    Allowed range: 0%–100%
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Enable Discount --}}
                    <div class="px-5 py-6 sm:px-7 sm:py-7">

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-start gap-4">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                                        <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <label for="expiry_discount_enabled"
                                               class="block text-sm font-bold text-slate-900">
                                            Enable automatic near-expiry discount
                                        </label>

                                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                                            When enabled, the configured discount will automatically
                                            be used during checkout for eligible near-expiry products.
                                        </p>
                                    </div>

                                </div>

                                {{-- Toggle --}}
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input
                                        type="hidden"
                                        name="expiry_discount_enabled"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="expiry_discount_enabled"
                                        value="1"
                                        id="expiry_discount_enabled"
                                        class="peer sr-only"
                                        {{ old('expiry_discount_enabled', $expiryDiscountEnabled) ? 'checked' : '' }}
                                    >

                                    <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-slate-900 peer-focus:ring-4 peer-focus:ring-slate-200"></span>

                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                                </label>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">

                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 11c0-1.1.9-2 2-2s2 .9 2 2c0 .8-.4 1.5-1 1.8-.6.4-1 1-1 1.7V15m-2 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                        </svg>

                        Changes apply to eligible products during checkout.
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200 sm:w-auto"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Settings
                    </button>

                </div>

            </form>

        </div>

        {{-- Configuration Summary --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Warning Window
                        </p>

                        <p class="mt-0.5 text-lg font-bold text-slate-900">
                            {{ old('expiry_warning_days', $expiryWarningDays) }} days
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 14l6-6m2.5-2.5l.5-.5M6 18h.01M18 6h.01M7 7h.01"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Discount
                        </p>

                        <p class="mt-0.5 text-lg font-bold text-slate-900">
                            {{ old('expiry_discount_percent', $expiryDiscountPercent) }}%
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection