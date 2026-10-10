@extends('layouts.app')

@section('title', 'New Grooming Request')

@section('content')

<div class="min-h-screen bg-slate-50">

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- =========================================================
             PAGE HEADER
        ========================================================== --}}
        <div class="mb-6">

            <div class="flex items-center gap-3">

                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-xl text-white shadow-sm">
                    
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Grooming
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        New Grooming Request
                    </h1>
                </div>

            </div>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                Enter the customer's and pet's information to create a grooming request.
                The grooming price will be selected from the pet size when the pet arrives.
            </p>

        </div>


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}
        @if($errors->any())

            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 font-bold text-red-600">
                        !
                    </div>

                    <div class="min-w-0">

                        <p class="font-semibold text-red-800">
                            Please check the following
                        </p>

                        <ul class="mt-2 space-y-1 pl-5 text-sm text-red-600 list-disc">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>

        @endif


        <form x-data="{
                  customers: @js($customers->values()),
                  selectedCustomerId: '',
                  customerName: @js(old('customer_name')),
                  customerPhone: @js(old('customer_phone')),
                  customerEmail: @js(old('customer_email')),
                  customerAddress: @js(old('customer_address')),
                  species: @js(old('species', '')),
                  petSize: @js(old('weight_category', '')),
                  chooseCustomer() {
                      const customer = this.customers.find(item => String(item.id) === String(this.selectedCustomerId));
                      if (customer) {
                          this.customerName = customer.name;
                          this.customerPhone = customer.phone || '';
                          this.customerEmail = customer.email || '';
                          this.customerAddress = customer.address || '';
                      }
                  }
              }"
              method="POST"
              action="{{ route('grooming.store') }}"
              class="space-y-5">

            @csrf


            {{-- =====================================================
                 CUSTOMER INFORMATION
            ====================================================== --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-5 sm:px-6">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-lg">
                            
                        </div>

                        <div>
                            <h2 class="font-bold text-slate-900">
                                Customer Information
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Contact details for the pet owner.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="p-5 sm:p-6">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- Customer Name --}}
                        <div class="md:col-span-2">

                            <label for="existing_customer"
                                   class="mb-2 block text-sm font-semibold text-slate-700">
                                Select Existing Customer
                            </label>

                            <select id="existing_customer"
                                    x-model="selectedCustomerId"
                                    @change="chooseCustomer()"
                                    class="mb-4 w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">
                                <option value="">New customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}</option>
                                @endforeach
                            </select>

                            <label for="customer_name"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Customer Name
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    
                                </span>

                                <input type="text"
                                       id="customer_name"
                                       name="customer_name"
                                       x-model="customerName"
                                       required
                                       autocomplete="name"
                                       placeholder="Enter customer's full name"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                            </div>

                        </div>


                        {{-- Phone --}}
                        <div>

                            <label for="customer_phone"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Phone Number
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    
                                </span>

                                <input type="text"
                                       id="customer_phone"
                                       name="customer_phone"
                                       x-model="customerPhone"
                                       required
                                       autocomplete="tel"
                                       placeholder="09XX XXX XXXX"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                            </div>

                        </div>


                        {{-- Email --}}
                        <div>

                            <label for="customer_email"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Email
                                <span class="font-normal text-slate-400">(Optional)</span>

                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    
                                </span>

                                <input type="email"
                                       id="customer_email"
                                       name="customer_email"
                                       x-model="customerEmail"
                                       autocomplete="email"
                                       placeholder="customer@example.com"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                            </div>

                        </div>


                        {{-- Address --}}
                        <div class="md:col-span-2">

                            <label for="customer_address"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Address
                                <span class="text-red-500">*</span>

                            </label>

                            <textarea id="customer_address"
                                      name="customer_address"
                                      rows="3"
                                      required
                                      x-model="customerAddress"
                                      placeholder="Enter customer's address"
                                      class="w-full resize-none rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-slate-200">{{ old('customer_address') }}</textarea>

                        </div>

                    </div>

                </div>

            </section>


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
                                Basic information about the pet being groomed.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="p-5 sm:p-6">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- Pet Name --}}
                        <div>

                            <label for="pet_name"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Pet Name
                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    
                                </span>

                                <input type="text"
                                       id="pet_name"
                                       name="pet_name"
                                       value="{{ old('pet_name') }}"
                                       required
                                       placeholder="Enter pet's name"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                            </div>

                        </div>


                        {{-- Species --}}
                        <div>

                            <label for="species"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Species
                                <span class="text-red-500">*</span>

                            </label>

                            <select id="species"
                                    name="species"
                                    x-model="species"
                                    @change="if (species !== 'Dog') petSize = ''"
                                    required
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                                <option value="">
                                    Select species
                                </option>

                                <option value="Dog"
                                    {{ old('species') === 'Dog' ? 'selected' : '' }}>
                                     Dog
                                </option>

                                <option value="Cat"
                                    {{ old('species') === 'Cat' ? 'selected' : '' }}>
                                     Cat
                                </option>

                                <option value="Other"
                                    {{ old('species') === 'Other' ? 'selected' : '' }}>
                                     Other
                                </option>

                            </select>

                        </div>


                        {{-- Breed --}}
                        <div>

                            <label for="breed"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Breed
                                <span class="font-normal text-slate-400">(Optional)</span>

                            </label>

                            <input type="text"
                                   id="breed"
                                   name="breed"
                                   value="{{ old('breed') }}"
                                   placeholder="e.g. Golden Retriever"
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">

                        </div>


                        <div x-show="species === 'Dog'" x-cloak>

                            <label for="weight_category"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Pet Size
                                <span class="text-red-500">*</span>

                            </label>

                            <select id="weight_category"
                                    name="weight_category"
                                    x-model="petSize"
                                    :required="species === 'Dog'"
                                    :disabled="species !== 'Dog'"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm transition focus:border-slate-400 focus:bg-white focus:ring-slate-200">
                                <option value="">Select approximate size</option>
                                @foreach(['Small', 'Medium', 'Large', 'XLarge'] as $category)
                                    <option value="{{ $category }}" @selected(old('weight_category') === $category)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-1.5 text-xs text-slate-400">
                                The selected size will determine the grooming price.
                            </p>

                        </div>


                        {{-- Notes --}}
                        <div class="md:col-span-2">

                            <label for="notes"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Grooming / Pet Notes
                                <span class="font-normal text-slate-400">(Optional)</span>

                            </label>

                            <textarea id="notes"
                                      name="notes"
                                      rows="4"
                                      placeholder="Special requests, behavior, allergies, grooming concerns, or other important information..."
                                      class="w-full resize-none rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-slate-200">{{ old('notes') }}</textarea>

                            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">

                                <span></span>

                                <span>
                                    Include anything the groomer should know before handling the pet.
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 REQUEST SUMMARY
            ====================================================== --}}
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm">
                        ℹ️
                    </div>

                    <div>

                        <p class="text-sm font-semibold text-blue-900">
                            Before submitting
                        </p>

                        <p class="mt-1 text-xs leading-5 text-blue-700">
                            Make sure the customer's contact information and pet details
                            are correct. The request will proceed through the grooming
                            approval workflow after submission.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 ACTIONS
            ====================================================== --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">

                <a href="{{ route('grooming.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">

                    Cancel

                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">

                    Submit Grooming Request

                </button>

            </div>

        </form>

    </div>

</div>

@endsection