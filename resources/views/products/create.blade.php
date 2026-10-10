@extends('layouts.app')

@section('title', 'Add Product')

@section('content')

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M20 7.5 12 3 4 7.5m16 0-8 4.5m8-4.5V16.5L12 21m0-9L4 7.5m8 4.5V21m0 0-8-4.5V7.5"/>
                    </svg>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            Add Product
                        </h1>

                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            Inventory
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Add a new product to your inventory.
                    </p>
                </div>
            </div>

            <a href="{{ route('inventory.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                Back to Inventory
            </a>
        </div>


        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex gap-3">

                    <div class="mt-0.5 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5 text-red-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 9v3.75m0 3.75h.008M10.29 3.86 2.82 17a2 2 0 0 0 1.74 3h14.88a2 2 0 0 0 1.74-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-red-800">
                            Please correct the following errors:
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        @endif


        {{-- Form --}}
        <form method="POST"
              action="{{ route('products.store') }}">

            @csrf

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                {{-- Form Header --}}
                <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">
                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Product Information
                            </h2>
                            <p class="text-sm text-slate-500">
                                Enter the details used to manage and sell this product.
                            </p>
                        </div>

                    </div>
                </div>


                <div class="space-y-8 p-5 sm:p-7">

                    {{-- Basic Information --}}
                    <section>
                        <div class="mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Basic Information
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Identify the product and organize it by category.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            {{-- Product Name --}}
                            <div class="md:col-span-1">
                                <label for="name"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Product Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required
                                       autofocus
                                       placeholder="e.g. Premium Dog Shampoo"
                                       class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400
                                       @error('name')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">

                                @error('name')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- SKU --}}
                            <div>
                                <label for="sku"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    SKU
                                    <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                       id="sku"
                                       name="sku"
                                       value="{{ old('sku') }}"
                                       required
                                       placeholder="e.g. DOG-SHAM-001"
                                       class="block w-full rounded-xl border px-4 py-3 text-sm font-mono text-slate-900 uppercase outline-none transition placeholder:font-sans placeholder:normal-case placeholder:text-slate-400
                                       @error('sku')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">

                                @error('sku')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Use a unique stock keeping unit.
                                </p>
                            </div>


                            {{-- Category --}}
                            <div class="md:col-span-2">
                                <label for="category"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Category
                                </label>

                                    <select id="category"
                                        name="category"
                                       class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400
                                       @error('category')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>

                                @error('category')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- Pricing --}}
                    <section class="border-t border-slate-200 pt-8">
                        <div class="mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Pricing
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Set your product cost and selling price.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Cost --}}
                            <div>
                                <label for="cost"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Cost
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="flex overflow-hidden rounded-xl border bg-white transition
                                    @error('cost')
                                        border-red-300 focus-within:ring-4 focus-within:ring-red-100
                                    @else
                                        border-slate-300 focus-within:border-slate-900 focus-within:ring-4 focus-within:ring-slate-100
                                    @enderror">

                                    <span class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                                        ₱
                                    </span>

                                    <input type="number"
                                           id="cost"
                                           name="cost"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('cost', 0) }}"
                                           required
                                           class="w-full border-0 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none focus:ring-0">
                                </div>

                                @error('cost')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- Selling Price --}}
                            <div>
                                <label for="price"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Selling Price
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="flex overflow-hidden rounded-xl border bg-white transition
                                    @error('price')
                                        border-red-300 focus-within:ring-4 focus-within:ring-red-100
                                    @else
                                        border-slate-300 focus-within:border-slate-900 focus-within:ring-4 focus-within:ring-slate-100
                                    @enderror">

                                    <span class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                                        ₱
                                    </span>

                                    <input type="number"
                                           id="price"
                                           name="price"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('price', 0) }}"
                                           required
                                           class="w-full border-0 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none focus:ring-0">
                                </div>

                                @error('price')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1.5 text-xs text-slate-400">
                                    The price customers will pay.
                                </p>
                            </div>

                        </div>
                    </section>


                    {{-- Inventory --}}
                    <section class="border-t border-slate-200 pt-8">
                        <div class="mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Inventory
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                New products start at zero stock. Record received quantities in Inventory.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label for="low_stock_level"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Low Stock Warning Level
                                    <span class="text-red-500">*</span>
                                </label>

                                <input type="number"
                                       id="low_stock_level"
                                       name="low_stock_level"
                                       min="0"
                                       value="{{ old('low_stock_level', 5) }}"
                                       required
                                       class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition
                                       @error('low_stock_level')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">

                                @error('low_stock_level')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1.5 text-xs text-slate-400">
                                    You'll be warned when stock reaches this level.
                                </p>
                            </div>

                        </div>
                    </section>


                    {{-- Expiry --}}
                    <section class="border-t border-slate-200 pt-8">
                        <div class="mb-4">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">
                                Expiry Settings
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Optional settings for products with an expiry date.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Expiry Date --}}
                            <div>
                                <label for="expiry_date"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Expiry Date
                                </label>

                                <input type="date"
                                       id="expiry_date"
                                       name="expiry_date"
                                       value="{{ old('expiry_date') }}"
                                       class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-900 outline-none transition
                                       @error('expiry_date')
                                           border-red-300 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                       @else
                                           border-slate-300 bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-100
                                       @enderror">

                                @error('expiry_date')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Leave blank if the product does not expire.
                                </p>
                            </div>


                            {{-- Expiry Discount --}}
                            <div>
                                <label for="expiry_discount"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Expiry Discount
                                </label>

                                <div class="flex overflow-hidden rounded-xl border bg-white transition
                                    @error('expiry_discount')
                                        border-red-300 focus-within:ring-4 focus-within:ring-red-100
                                    @else
                                        border-slate-300 focus-within:border-slate-900 focus-within:ring-4 focus-within:ring-slate-100
                                    @enderror">

                                    <input type="number"
                                           id="expiry_discount"
                                           name="expiry_discount"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           value="{{ old('expiry_discount', 0) }}"
                                           class="w-full border-0 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none focus:ring-0">

                                    <span class="flex items-center border-l border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                                        %
                                    </span>
                                </div>

                                @error('expiry_discount')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Discount applied when the product is near expiry.
                                </p>
                            </div>

                        </div>
                    </section>

                </div>


                {{-- Form Footer --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-5 sm:flex-row sm:items-center sm:justify-end sm:px-7">

                    <a href="{{ route('products.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200 sm:w-auto">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M5 12.75 9.5 17 19 7.5"/>
                        </svg>

                        Save Product
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>

@endsection