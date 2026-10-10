@extends('layouts.app')

@section('title', 'Receive Pet')

@section('content')

<div class="min-h-screen bg-slate-50">

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- =========================================================
             PAGE HEADER
        ========================================================== --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="flex items-center gap-3">

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 text-xl text-white shadow-sm">
                        
                    </div>

                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Grooming
                        </p>

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            Receive Pet
                        </h1>

                    </div>

                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Confirm the pet's arrival and apply the service price for the selected pet size.
                </p>

            </div>


            <a href="{{ route('grooming.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">

                <span>←</span>

                Back to Grooming

            </a>

        </div>


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}
        @if(session('error'))

            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-bold text-red-600">
                    !
                </div>

                <div>

                    <p class="text-sm font-semibold text-red-800">
                        Unable to receive pet
                    </p>

                    <p class="mt-1 text-sm text-red-600">
                        {{ session('error') }}
                    </p>

                </div>

            </div>

        @endif


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}
        @if($errors->any())

            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-bold text-red-600">
                        !
                    </div>

                    <div>

                        <p class="text-sm font-semibold text-red-800">
                            Please check the following
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>

        @endif


        {{-- =========================================================
             REQUEST SUMMARY
        ========================================================== --}}
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-lg">
                        
                    </div>

                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Grooming Request
                        </p>

                        <p class="mt-0.5 font-bold text-slate-900">
                            Request #{{ $appointment->id }}
                        </p>

                    </div>

                </div>


                <div class="flex items-center gap-2 rounded-xl bg-blue-50 px-3 py-2">

                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>

                    <span class="text-xs font-semibold text-blue-700">
                        Ready to receive

                    </span>

                </div>

            </div>

        </div>


        {{-- =========================================================
             MAIN CONTENT
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">


            {{-- =====================================================
                 PET INFORMATION
            ====================================================== --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-5 sm:px-6">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-lg">
                            
                        </div>

                        <div>

                            <h2 class="font-bold text-slate-900">
                                Pet Information
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Review the customer's and pet's details.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-5 sm:p-6">

                    <div class="space-y-4">


                        {{-- CUSTOMER --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm">
                                
                            </div>

                            <div class="min-w-0">

                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Customer
                                </p>

                                <p class="mt-0.5 truncate font-semibold text-slate-800">
                                    {{ $appointment->customer->name }}
                                </p>

                            </div>

                        </div>


                        {{-- PHONE --}}
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 p-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm">
                                
                            </div>

                            <div class="min-w-0">

                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Phone
                                </p>

                                <p class="mt-0.5 font-medium text-slate-700">
                                    {{ $appointment->customer->phone ?: 'Not provided' }}
                                </p>

                            </div>

                        </div>


                        {{-- PET --}}
                        <div class="rounded-2xl border border-slate-200 p-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-xl">
                                    
                                </div>

                                <div class="min-w-0">

                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                        Pet
                                    </p>

                                    <p class="mt-0.5 text-lg font-bold text-slate-900">
                                        {{ $appointment->pet->name }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">

                                        {{ $appointment->pet->species }}

                                        @if($appointment->pet->breed)
                                            <span class="mx-1">•</span>
                                            {{ $appointment->pet->breed }}
                                        @endif

                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- APPROXIMATE WEIGHT --}}
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                </div>

                                <div>

                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                        Selected Pet Size
                                    </p>

                                    <p class="mt-0.5 font-semibold text-slate-800">

                                        {{ $appointment->pet_size ?: ($appointment->pet->weight_category ?: 'Not provided') }}

                                    </p>

                                </div>

                            </div>

                            @if($appointment->pet_size || $appointment->pet->weight_category)

                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500">
                                    Estimated
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </section>


              @if(false)
              {{-- Legacy exact-weight receive form --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-5 sm:px-6">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-lg">
                        </div>

                        <div>

                            <h2 class="font-bold text-slate-900">
                                Actual Weight
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Measure the pet upon arrival.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-5 sm:p-6">

                    <form method="POST"
                          action="{{ route('grooming.receive.store', $appointment) }}">

                        @csrf


                        {{-- WEIGHT INPUT --}}
                        <div>

                            <label for="actual_weight"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Actual Weight
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       max="999.99"
                                       name="actual_weight"
                                       id="actual_weight"
                                       value="{{ old('actual_weight', $appointment->pet->weight) }}"
                                       required
                                       autofocus
                                       class="w-full rounded-2xl border-slate-200 bg-slate-50 py-5 pl-5 pr-16 text-2xl font-bold text-slate-900 transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100 @error('actual_weight') border-red-400 ring-4 ring-red-100 @enderror">

                                <span class="pointer-events-none absolute right-5 top-1/2 -translate-y-1/2 text-lg font-semibold text-slate-400">
                                    kg
                                </span>

                            </div>

                            @error('actual_weight')

                                <p class="mt-2 text-sm font-medium text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                            <p class="mt-2 text-xs leading-5 text-slate-400">
                                Enter the weight measured on the grooming scale when the pet arrives.
                            </p>

                        </div>


                        {{-- PRICING INFORMATION --}}
                        <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4">

                            <div class="flex items-start gap-3">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm">
                                    
                                </div>

                                <div>

                                    <p class="text-sm font-bold text-blue-900">
                                        Automatic Grooming Pricing
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-blue-700">
                                        After you submit the actual weight, the system will automatically select the active grooming price based on the recorded weight.
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- COMPARISON --}}
                        @if($appointment->pet->weight !== null)

                            <div class="mt-4 flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">

                                <span class="text-xs font-medium text-slate-500">
                                    Previous estimate
                                </span>

                                <span class="font-semibold text-slate-700">
                                    {{ number_format((float) $appointment->pet->weight, 2) }} kg
                                </span>

                            </div>

                        @endif


                        {{-- SUBMIT --}}
                        <button type="submit"
                                class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 py-4 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-slate-800">

                            Receive Pet & Calculate Price

                        </button>


                        <p class="mt-3 text-center text-[11px] text-slate-400">
                            This will record the pet as received and calculate the applicable grooming price.
                        </p>

                    </form>

                </div>

            </section>

            </section>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-5 sm:px-6">
                    <h2 class="font-bold text-slate-900">Confirm Pet Size</h2>
                    <p class="mt-1 text-xs text-slate-500">The grooming price is based on the selected size.</p>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="rounded-2xl bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Selected Pet Size</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $appointment->pet_size ?: ($appointment->pet->weight_category ?: 'Not provided') }}</p>
                    </div>

                    @if($groomingService)
                        <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $groomingService->name }}</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-900">₱{{ number_format((float) $groomingService->rate, 2) }}</p>
                        </div>
                    @else
                        <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm text-red-700">No active service is configured for this pet size.</p>
                    @endif

                    @if($groomingService)
                        <form method="POST" action="{{ route('grooming.receive.store', $appointment) }}">
                            @csrf
                            <button type="submit" class="mt-6 flex w-full items-center justify-center rounded-2xl bg-slate-950 py-4 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800">
                                Receive Pet &amp; Apply Price
                            </button>
                        </form>
                    @else
                        <a href="{{ route('services.create') }}" class="mt-6 flex w-full items-center justify-center rounded-2xl bg-slate-950 py-4 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800">
                            Add Service For This Size
                        </a>
                    @endif
                </div>
            </section>

        </div>


        {{-- =========================================================
             FOOTER NOTE
        ========================================================== --}}
        <div class="flex items-center justify-center gap-2 py-2 text-xs text-slate-400">

            <span></span>

            <span>
                Verify the selected pet size before submitting.
            </span>

        </div>

    </div>

</div>

@endsection