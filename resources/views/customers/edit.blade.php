@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')

<div class="mx-auto max-w-4xl space-y-6">

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

        <div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Edit Customer
                </h1>

                <p class="mt-0.5 text-sm text-emerald-100/80">
                    Update customer information.
                </p>
            </div>

        </div>

        <a
            href="{{ route('customers.show', $customer) }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M19 12H5"></path>
                <path d="m12 19-7-7 7-7"></path>
            </svg>

            Back to Customer
        </a>

    </div>


    {{-- =========================================================
        FORM CARD
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg shadow-sm ring-1 ring-slate-200">
                    
                </div>

                <div>
                    <h2 class="font-bold text-slate-900">
                        Customer Information
                    </h2>

                    <p class="mt-0.5 text-sm text-slate-500">
                        Update the contact details for
                        <span class="font-semibold text-slate-700">
                            {{ $customer->name }}
                        </span>.
                    </p>
                </div>

            </div>

        </div>


        <div class="p-5 sm:p-6">

            {{-- =================================================
                VALIDATION ERRORS
            ================================================== --}}
            @if ($errors->any())

                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">

                    <div class="flex items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-bold text-red-600">
                            !
                        </div>

                        <div class="min-w-0">

                            <h3 class="font-semibold text-red-800">
                                Please fix the following
                            </h3>

                            <ul class="mt-2 space-y-1 text-sm text-red-700">

                                @foreach ($errors->all() as $error)

                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>{{ $error }}</span>
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =================================================
                FORM
            ================================================== --}}
            <form
                method="POST"
                action="{{ route('customers.update', $customer) }}"
                class="space-y-8"
            >

                @csrf
                @method('PUT')


                {{-- =================================================
                    BASIC INFORMATION
                ================================================== --}}
                <section>

                    <div class="mb-4 flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg">
                            
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900">
                                Basic Information
                            </h3>

                            <p class="text-xs text-slate-500">
                                Update the customer's name.
                            </p>
                        </div>

                    </div>


                    <div>

                        <label
                            for="name"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Customer Name
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $customer->name) }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-emerald-700"
                        >

                        @error('name')

                            <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
                                <span>!</span>
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </section>


                {{-- =================================================
                    CONTACT INFORMATION
                ================================================== --}}
                <section class="border-t border-slate-200 pt-7">

                    <div class="mb-4 flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg">
                            
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900">
                                Contact Information
                            </h3>

                            <p class="text-xs text-slate-500">
                                Keep the customer's contact details up to date.
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- Phone --}}
                        <div>

                            <label
                                for="phone"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Phone
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
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3.08 5.18 2 2 0 0 1 5.06 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L9 10.73a16 16 0 0 0 4.27 4.27l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 21 15.88z"></path>
                                    </svg>

                                </div>

                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', $customer->phone) }}"
                                    autocomplete="tel"
                                    placeholder="09XXXXXXXXX"
                                    class="w-full rounded-xl border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-emerald-700"
                                >

                            </div>

                            @error('phone')

                                <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
                                    <span>!</span>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Email
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
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="m3 7 9 6 9-6"></path>
                                    </svg>

                                </div>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $customer->email) }}"
                                    autocomplete="email"
                                    placeholder="customer@example.com"
                                    class="w-full rounded-xl border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-emerald-700"
                                >

                            </div>

                            @error('email')

                                <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
                                    <span>!</span>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    ADDRESS
                ================================================== --}}
                <section class="border-t border-slate-200 pt-7">

                    <div class="mb-4 flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg">
                            
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900">
                                Address
                            </h3>

                            <p class="text-xs text-slate-500">
                                Update the customer's address information.
                            </p>
                        </div>

                    </div>


                    <div>

                        <label
                            for="address"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Customer Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            autocomplete="street-address"
                            placeholder="Enter customer address"
                            class="w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-emerald-700"
                        >{{ old('address', $customer->address) }}</textarea>

                        @error('address')

                            <p class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
                                <span>!</span>
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </section>


                {{-- =================================================
                    LOYALTY INFORMATION
                ================================================== --}}
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-4 sm:p-5">

                    <div>
                        <div class="min-w-0">

                            <h3 class="font-bold text-amber-900">
                                Loyalty Program
                            </h3>

                            <p class="mt-1 text-sm text-amber-800">
                                Current loyalty visits
                            </p>

                            <div class="mt-3 inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2.5 shadow-sm ring-1 ring-amber-200">

                                <span class="text-xl font-bold text-amber-800">
                                    {{ $customer->loyalty_visits ?? 0 }}
                                </span>

                                <span class="text-sm font-medium text-amber-700">
                                    visit{{ ($customer->loyalty_visits ?? 0) == 1 ? '' : 's' }}
                                </span>

                            </div>

                            <p class="mt-3 text-xs leading-5 text-amber-800">
                                Loyalty visits are displayed for reference and are not editable from this form.
                            </p>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    FORM ACTIONS
                ================================================== --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">

                    <a
                        href="{{ route('customers.show', $customer) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-800 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M19 12H5"></path>
                            <path d="m12 5-7 7 7 7"></path>
                        </svg>

                        Update Customer
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection