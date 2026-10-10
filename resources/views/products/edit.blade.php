@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="min-h-screen bg-slate-50">

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-2xl">
                    
                </div>

                <div>
                    <div class="flex items-center gap-2">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Edit Product
                        </h1>

                        @if($product->active)
                            <span class="hidden rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 sm:inline-flex">
                                Active
                            </span>
                        @else
                            <span class="hidden rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 sm:inline-flex">
                                Inactive
                            </span>
                        @endif

                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Update product information, pricing, stock, and expiry settings.
                    </p>
                </div>

            </div>


            <a href="{{ route('inventory.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>

                Back to Inventory
            </a>

        </div>


        {{-- Success Message --}}
        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">

                <div class="pt-1 text-sm font-medium">
                    {{ session('success') }}
                </div>

            </div>

        @endif


        {{-- Validation Errors --}}
        @if($errors->any())

            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">

                <div class="flex items-start gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 font-bold">
                        !
                    </div>

                    <div class="flex-1">

                        <h3 class="text-sm font-bold">
                            Please correct the following:
                        </h3>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                </div>

            </div>

        @endif


        {{-- Product Form --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-8">

                <div>
                    <div>
                        <h2 class="font-bold text-slate-900">
                            Product Information
                        </h2>

                        <p class="text-sm text-slate-500">
                            Update the details for {{ $product->name }}.
                        </p>
                    </div>

                </div>

            </div>


            <form method="POST"
                  action="{{ route('products.update', $product) }}">

                @csrf
                @method('PUT')


                <div class="space-y-8 px-5 py-6 sm:px-8 sm:py-8">


                    {{-- Basic Information --}}
                    <section>

                        <div class="mb-5">

                            <h3 class="text-base font-bold text-slate-900">
                                Basic Information
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Identify the product and organize it by category.
                            </p>

                        </div>


                        <div class="grid gap-5 md:grid-cols-12">

                            {{-- Product Name --}}
                            <div class="md:col-span-8">

                                <label for="name"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Product Name
                                </label>

                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $product->name) }}"
                                       required
                                       class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100"
                                       placeholder="Enter product name">

                                @error('name')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- SKU --}}
                            <div class="md:col-span-4">

                                <label for="sku"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    SKU
                                </label>

                                <input type="text"
                                       id="sku"
                                       name="sku"
                                       value="{{ old('sku', $product->sku) }}"
                                       required
                                       class="w-full rounded-xl border {{ $errors->has('sku') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 font-mono text-sm text-slate-900 outline-none transition placeholder:font-sans placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100"
                                       placeholder="SKU">

                                @error('sku')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Category --}}
                            <div class="md:col-span-12">

                                <label for="category"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Category
                                </label>

                                <select id="category"
                                       name="category"
                                       class="w-full rounded-xl border {{ $errors->has('category') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100"
                                       >
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category', $product->category) === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>

                                @error('category')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- Pricing --}}
                    <section class="border-t border-slate-100 pt-8">

                        <div class="mb-5">

                            <h3 class="text-base font-bold text-slate-900">
                                Pricing
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Set the product cost and selling price.
                            </p>

                        </div>


                        <div class="grid gap-5 md:grid-cols-2">


                            {{-- Cost --}}
                            <div>

                                <label for="cost"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Cost
                                </label>

                                <div class="relative">

                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-slate-400">
                                        ₱
                                    </span>

                                    <input type="number"
                                           id="cost"
                                           name="cost"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('cost', $product->cost) }}"
                                           required
                                           class="w-full rounded-xl border {{ $errors->has('cost') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white py-3 pl-9 pr-4 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                                </div>

                                @error('cost')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Selling Price --}}
                            <div>

                                <label for="price"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Selling Price
                                </label>

                                <div class="relative">

                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-slate-400">
                                        ₱
                                    </span>

                                    <input type="number"
                                           id="price"
                                           name="price"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('price', $product->price) }}"
                                           required
                                           class="w-full rounded-xl border {{ $errors->has('price') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white py-3 pl-9 pr-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                                </div>

                                @error('price')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- Inventory --}}
                    <section class="border-t border-slate-100 pt-8">

                        <div class="mb-5">

                            <h3 class="text-base font-bold text-slate-900">
                                Inventory
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Stock quantities are updated from Inventory when a batch is received.
                            </p>

                        </div>


                        <div class="grid gap-5 md:grid-cols-2">


                            {{-- Low Stock Level --}}
                            <div>

                                <label for="low_stock_level"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Low Stock Warning Level
                                </label>

                                <input type="number"
                                       id="low_stock_level"
                                       name="low_stock_level"
                                       min="0"
                                       value="{{ old('low_stock_level', $product->low_stock_level) }}"
                                       required
                                       class="w-full rounded-xl border {{ $errors->has('low_stock_level') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                                <p class="mt-2 text-xs text-slate-400">
                                    A low-stock warning will appear when inventory reaches this level.
                                </p>

                                @error('low_stock_level')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- Expiry --}}
                    <section class="border-t border-slate-100 pt-8">

                        <div class="mb-5">

                            <h3 class="text-base font-bold text-slate-900">
                                Expiry Settings
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Configure the product expiry date and optional discount.
                            </p>

                        </div>


                        <div class="grid gap-5 md:grid-cols-2">


                            {{-- Expiry Date --}}
                            <div>

                                <label for="expiry_date"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Expiry Date
                                </label>

                                <input type="date"
                                       id="expiry_date"
                                       name="expiry_date"
                                       value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}"
                                       class="w-full rounded-xl border {{ $errors->has('expiry_date') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                                <p class="mt-2 text-xs text-slate-400">
                                    Leave blank if the product does not expire.
                                </p>

                                @error('expiry_date')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Expiry Discount --}}
                            <div>

                                <label for="expiry_discount"
                                       class="mb-2 block text-sm font-semibold text-slate-700">
                                    Expiry Discount
                                </label>

                                <div class="relative">

                                    <input type="number"
                                           id="expiry_discount"
                                           name="expiry_discount"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           value="{{ old('expiry_discount', $product->expiry_discount) }}"
                                           class="w-full rounded-xl border {{ $errors->has('expiry_discount') ? 'border-red-300 ring-4 ring-red-50' : 'border-slate-200' }} bg-white px-4 py-3 pr-10 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-semibold text-slate-400">
                                        %
                                    </span>

                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    Optional discount percentage for products approaching expiry.
                                </p>

                                @error('expiry_discount')
                                    <p class="mt-2 text-xs font-medium text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    {{-- Product Status --}}
                    <section class="border-t border-slate-100 pt-8">

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">

                            <div class="flex items-start gap-4">

                                <div class="flex-1">

                                    <label for="active"
                                           class="block text-sm font-bold text-slate-900">
                                        Product Status
                                    </label>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Active products are available for sale in the POS.
                                    </p>


                                    {{-- Hidden fallback --}}
                                    <input type="hidden"
                                           name="active"
                                           value="0">


                                    <label class="mt-4 inline-flex cursor-pointer items-center gap-3">

                                        <input type="checkbox"
                                               id="active"
                                               name="active"
                                               value="1"
                                               class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-300"
                                               {{ old('active', $product->active) ? 'checked' : '' }}>

                                        <span class="text-sm font-semibold text-slate-700">
                                            Product is active and available for sale
                                        </span>

                                    </label>

                                </div>

                            </div>

                        </div>

                    </section>

                </div>


                {{-- Form Actions --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">

                    <div class="flex w-full gap-3 sm:w-auto">

                        <a href="{{ route('products.index') }}"
                           class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:flex-none">
                            Cancel
                        </a>

                    </div>


                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 sm:w-auto">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>

                        Update Product
                    </button>

                </div>

            </form>


            {{-- Delete Product --}}
            <div class="border-t border-red-100 bg-red-50/40 px-5 py-5 sm:px-8">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h3 class="text-sm font-bold text-red-700">
                            Delete Product
                        </h3>

                        <p class="mt-1 text-xs text-red-600/80">
                            Move this product to Archive. You can restore it or permanently delete it there.
                        </p>

                    </div>


                    {{-- IMPORTANT: This is a separate form --}}
                    <form method="POST"
                          action="{{ route('products.destroy', $product) }}"
                          onsubmit="return confirm('Move this product to Archive? Its stock and history will be kept.');">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 sm:w-auto">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M6 7h12m-9 0v10m6-10v10M9 7V4h6v3m2 0v13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7h10Z"/>
                            </svg>

                            Delete Product
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection