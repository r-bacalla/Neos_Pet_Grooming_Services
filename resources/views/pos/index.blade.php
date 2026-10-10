@extends('layouts.app')

@section('title', 'Point of Sale')

@section('content')

<div
    x-data="posSystem()"
    id="pos-workspace"
    class="pos-workspace min-h-screen rounded-2xl"
>

<div class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">

    {{-- PAGE HEADER --}}
    <div class="mb-5 flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">

        <div>
            <div class="flex items-center gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">
                        Sales
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight text-white">
                        Point of Sale
                    </h1>
                </div>
            </div>

            <p class="mt-2 text-sm text-emerald-100/80">
                Select products and add them to the current sale.
            </p>
        </div>

    </div>


    {{-- MAIN POS GRID --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- PRODUCTS / SERVICES --}}
        <section class="inventory-content-frame min-w-0 overflow-hidden rounded-3xl border-2 border-slate-200 bg-white shadow-sm xl:col-span-8">

            {{-- CATALOG HEADER --}}
            <div class="border-b border-slate-100 p-4 sm:p-5">

                <div class="flex flex-col gap-4">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Product catalog</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Search, filter, and add items to the sale.</p>
                        </div>
                        <p class="text-xs font-semibold text-slate-500"><span class="text-slate-900">{{ $products->count() }}</span> products available</p>
                    </div>

                    {{-- SEARCH --}}
                    <div class="relative">

                        <span class="absolute left-3 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-4-4"></path>
                            </svg>
                        </span>

                        <input
                            type="text"
                            x-model="search"
                            aria-label="Search products"
                            placeholder="Search products..."
                            class="pos-product-search w-full rounded-full border-2 border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-slate-200"
                        >

                        <button
                            type="button"
                            x-show="search.length > 0"
                            @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs text-slate-400 hover:bg-slate-200 hover:text-slate-700"
                        >
                            Clear
                        </button>

                    </div>

                    @php
                        $productCategories = $products
                            ->map(fn ($product) => filled($product->category) ? $product->category : 'Uncategorized')
                            ->unique()
                            ->sort()
                            ->values();
                        $categoryColors = [];
                        foreach ($productCategories as $index => $category) {
                            $categoryColors[$category] = fmod($index * 137.508, 360);
                        }
                    @endphp
                    <div class="-mt-1 flex flex-wrap gap-2" role="group" aria-label="Filter products by category">
                        <button type="button" @click="productCategory = 'All'" :aria-pressed="productCategory === 'All'" :class="productCategory === 'All' ? 'border-slate-800 bg-slate-100 text-slate-900' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-900'" class="rounded-full border-2 px-3 py-1.5 text-xs font-semibold transition-colors">All</button>
                        @foreach($productCategories as $category)
                            <button
                                type="button"
                                @click="productCategory = @js($category)"
                                :aria-pressed="productCategory === @js($category)"
                                :class="productCategory === @js($category) ? 'border-slate-800 bg-slate-100 text-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-900'"
                                class="rounded-full border-2 px-3 py-1.5 text-xs font-semibold transition"
                            >{{ $category }}</button>
                        @endforeach
                    </div>

                </div>

            </div>


            {{-- PRODUCTS --}}
            <div
                x-show="tab === 'products'"
                x-cloak
                class="p-4 sm:p-5"
            >

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

                    @foreach($products as $product)
                        @php
                            $pricingBreakdown = $product->salePricingBreakdown()->values();
                            $saleStock = (int) $pricingBreakdown->sum('quantity');
                            $displayPricing = $pricingBreakdown->first();
                            $productCategory = filled($product->category) ? $product->category : 'Uncategorized';
                            $categoryColor = $categoryColors[$productCategory];
                        @endphp

                        <button
                            type="button"
                            @click="addProduct({
                                id: {{ $product->id }},
                                name: @js($product->name),
                                price: {{ $displayPricing['unit_price'] ?? (float) $product->price }},
                                originalPrice: {{ (float) $product->price }},
                                nearExpiry: @js(($displayPricing['discount_percent'] ?? 0) > 0),
                                expiryDiscountPercent: {{ $displayPricing['discount_percent'] ?? 0 }},
                                priceBreakdown: @js($pricingBreakdown),
                                stock: {{ $saleStock }}
                            })"
                            x-show="matches(@js($product->name), @js(filled($product->category) ? $product->category : 'Uncategorized'))"
                            :disabled="{{ $saleStock }} <= 0"
                            class="group relative flex min-h-[190px] flex-col rounded-2xl border-2 border-slate-200 bg-white p-4 text-left transition hover:-translate-y-0.5 hover:border-emerald-700 hover:bg-emerald-50/70 hover:shadow-[0_8px_20px_rgba(2,43,27,0.12)] active:translate-y-0 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50"
                        >

                            <div class="mt-0 min-w-0">

                                <div class="mb-3 inline-flex max-w-full truncate rounded-full border px-2.5 py-1.5 text-xs font-bold" style="background-color: hsl({{ $categoryColor }} 55% 93%); border-color: hsl({{ $categoryColor }} 35% 76%); color: hsl({{ $categoryColor }} 45% 28%)">
                                    {{ $productCategory }}
                                </div>

                                <div class="truncate font-semibold text-sm text-slate-900">
                                    {{ $product->name }}
                                </div>

                            </div>

                            <div class="mt-auto pt-4">

                                <div>
                                    @if(($displayPricing['discount_percent'] ?? 0) > 0)
                                        <p class="text-xs text-slate-400 line-through">₱{{ number_format((float) $product->price, 2) }}</p>
                                        <p class="text-base font-bold text-emerald-800">₱{{ number_format((float) $displayPricing['unit_price'], 2) }}</p>
                                        <p class="mt-0.5 text-[10px] font-semibold text-emerald-700">{{ number_format((float) $displayPricing['discount_percent'], 0) }}% discount</p>
                                    @else
                                        <p class="text-base font-bold text-slate-950">₱{{ number_format($product->price, 2) }}</p>
                                    @endif

                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        {{ $saleStock }} available to sell
                                    </p>
                                </div>

                                <span class="mt-3 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-full border-2 border-emerald-800 bg-emerald-50 px-3 text-xs font-bold text-emerald-900 shadow-sm transition group-hover:bg-emerald-800 group-hover:text-white group-hover:shadow-md">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14"></path>
                                    </svg>
                                    <span>Add to cart</span>
                                </span>

                            </div>

                        </button>

                    @endforeach

                </div>

                {{-- EMPTY SEARCH --}}
                <div
                    x-show="products.filter(product => matches(product.name, product.category)).length === 0"
                    class="py-16 text-center"
                >
                    <p class="mt-4 font-semibold text-slate-700">
                        No products found
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        Try a different search term.
                    </p>
                </div>

            </div>


            @if(false)
            {{-- SERVICES --}}
            <div
                x-show="tab === 'services'"
                x-cloak
                class="p-4 sm:p-5"
            >

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <h2 class="font-bold text-slate-900">
                            Grooming Services
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Add a grooming service to the current sale.
                        </p>
                    </div>

                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-600">
                        {{ $services->count() }} services
                    </span>

                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

                    @foreach($services as $service)

                        <button
                            type="button"
                            @click="addService({
                                id: {{ $service->id }},
                                name: @js($service->name),
                                price: {{ $service->rate }}
                            })"
                            x-show="matches(@js($service->name))"
                            class="group relative flex min-h-[155px] flex-col rounded-2xl border border-slate-200 bg-white p-4 text-left transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                        >

                            <div class="flex items-start justify-between">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-lg">
                                    
                                </div>

                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">
                                    {{ $service->duration_minutes }} min
                                </span>

                            </div>

                            <div class="mt-4">

                                <div class="truncate font-semibold text-sm text-slate-900">
                                    {{ $service->name }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Grooming service
                                </div>

                            </div>

                            <div class="mt-auto flex items-end justify-between pt-4">

                                <p class="text-base font-bold text-slate-950">
                                    ₱{{ number_format($service->rate, 2) }}
                                </p>

                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-slate-900 group-hover:text-white">
                                    +
                                </span>

                            </div>

                        </button>

                    @endforeach

                </div>

            </div>
            @endif

        </section>


        {{-- CART --}}
        <aside class="inventory-content-frame pos-cart flex min-h-[600px] flex-col overflow-hidden rounded-3xl border-2 border-slate-200 bg-white shadow-sm xl:sticky xl:top-5 xl:col-span-4 xl:h-[calc(100vh-40px)]">

            {{-- CART HEADER --}}
            <div class="border-b border-slate-100 px-5 py-5">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Order
                        </p>

                        <h2 class="mt-1 text-xl font-bold text-slate-900">
                            Current Sale
                        </h2>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            <span x-text="cartItemCount"></span>
                            <span x-text="cartItemCount === 1 ? 'item' : 'items'"></span>
                        </p>
                    </div>

                </div>

            </div>


            {{-- CART ITEMS --}}
            <div class="min-h-0 flex-1 overflow-y-auto px-5">

                {{-- EMPTY CART --}}
                <template x-if="cart.length === 0">

                    <div class="flex h-full min-h-[300px] flex-col items-center justify-center text-center">

                        <svg class="mb-4 h-9 w-9 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L21 8H6M10 20h.01M18 20h.01" />
                        </svg>

                        <h3 class="font-bold text-slate-800">
                            Your cart is empty
                        </h3>

                        <p class="mt-1 max-w-[220px] text-sm leading-5 text-slate-400">
                            Select a product from the catalog to begin.
                        </p>

                    </div>

                </template>


                {{-- CART ITEMS --}}
                <div x-show="cart.length > 0">

                    <template x-for="(item, index) in cart" :key="index">

                        <div class="border-b border-slate-100 py-4">

                            <div class="flex gap-3">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                    :class="item.type === 'service'
                                        ? 'bg-violet-50'
                                        : 'bg-slate-100'"
                                >
                                    <svg x-show="item.type === 'service'" class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path stroke-linecap="round" d="m8.2 7.8 11 11M8.2 16.2 19.5 5"/></svg>
                                    <svg x-show="item.type !== 'service'" class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4v10l-8 4-8-4V7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.3 7.5 4.2 7.5-4.2M12 11.5V21"/></svg>
                                </div>

                                <div class="min-w-0 flex-1">

                                    <div class="flex items-start justify-between gap-2">

                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-900"
                                                x-text="item.name"
                                            ></p>

                                            <div class="mt-0.5 text-xs text-slate-500">
                                                <template x-if="lineDiscount(item) > 0">
                                                    <span>
                                                        <span class="text-slate-400 line-through">₱<span x-text="item.originalPrice.toFixed(2)"></span></span>
                                                        <span class="ml-1 font-semibold text-emerald-800">₱<span x-text="lineUnitPrice(item).toFixed(2)"></span> avg. each</span>
                                                        <span class="ml-1 font-semibold text-emerald-700" x-text="`Save ₱${lineDiscount(item).toFixed(2)} discount`"></span>
                                                    </span>
                                                </template>
                                                <template x-if="lineDiscount(item) <= 0">
                                                    <span>₱<span x-text="lineUnitPrice(item).toFixed(2)"></span> each</span>
                                                </template>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-slate-500 transition hover:bg-red-50 hover:text-red-700"
                                            :aria-label="'Remove ' + item.name"
                                            :title="'Remove ' + item.name"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                                            </svg>
                                        </button>

                                    </div>


                                    <div class="mt-3 flex items-center justify-between">

                                        {{-- QUANTITY --}}
                                        <div class="flex items-center rounded-xl border border-slate-200 bg-white">

                                            <button
                                                type="button"
                                                @click="decrease(index)"
                                                :aria-label="'Decrease quantity of ' + item.name"
                                                class="flex h-8 w-8 items-center justify-center text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                                            >
                                                −
                                            </button>

                                            <input
                                                type="number"
                                                min="1"
                                                :max="item.type === 'product' ? item.stock : null"
                                                :value="item.quantity"
                                                @change="setQuantity(index, $event.target.value); $event.target.value = item.quantity"
                                                @keydown.enter.prevent="$event.target.blur()"
                                                :aria-label="'Quantity of ' + item.name"
                                                class="h-8 w-14 border-0 bg-transparent p-0 text-center text-sm font-semibold text-slate-900 focus:ring-0"
                                            >

                                            <button
                                                type="button"
                                                @click="increase(index)"
                                                :disabled="item.type === 'product' && item.quantity >= item.stock"
                                                :aria-label="'Increase quantity of ' + item.name"
                                                class="flex h-8 w-8 items-center justify-center text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40"
                                            >
                                                +
                                            </button>

                                        </div>

                                        {{-- LINE TOTAL --}}
                                        <p class="text-sm font-bold text-slate-900">
                                            ₱<span x-text="lineTotal(item).toFixed(2)"></span>
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </template>

                </div>

            </div>


            {{-- SUMMARY --}}
            <div class="border-t border-slate-100 bg-slate-50 p-5">

                <div class="space-y-2.5">

                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-800">
                            ₱<span x-text="subtotal.toFixed(2)"></span>
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Discount (included)</span>
                        <span class="font-medium text-slate-800">
                            ₱<span x-text="discount.toFixed(2)"></span>
                        </span>
                    </div>

                </div>

                <div class="my-4 border-t border-slate-200"></div>

                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total</p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-slate-950">₱<span x-text="total.toFixed(2)"></span></p>
                    </div>
                </div>

                {{-- CHECKOUT --}}
                <button
                    type="button"
                    @click="openPayment()"
                    :disabled="cart.length === 0"
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 py-4 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Proceed to Payment
                </button>

                <p class="mt-2 text-center text-[11px] text-slate-400">
                    Review your order before completing the sale.
                </p>

            </div>

        </aside>

    </div>

</div>


{{-- PAYMENT MODAL --}}
<div
    x-show="paymentModal"
    x-cloak
    x-transition.opacity
    @keydown.escape.window="paymentModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6"
>

    <div
        @click.outside="paymentModal = false"
        x-transition.scale
        role="dialog"
        aria-modal="true"
        aria-labelledby="pos-payment-title"
        class="flex max-h-[95vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
    >

        {{-- MODAL HEADER --}}
        <div class="flex items-center justify-between border-b border-emerald-900 bg-emerald-950 px-4 py-3 text-white sm:px-5">

            <div class="flex items-center gap-3">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">
                        Checkout
                    </p>

                    <h2 id="pos-payment-title" class="text-xl font-bold text-white">
                        Complete Payment
                    </h2>
                </div>

            </div>

            <button
                type="button"
                @click="paymentModal = false"
                aria-label="Close payment"
                class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
            >
                Close
            </button>

        </div>


        {{-- MODAL BODY --}}
        <form
            method="POST"
            action="{{ route('pos.checkout') }}"
            data-scroll-target="pos-workspace"
            @submit="if (Number(amountPaid || 0) < total) { $event.preventDefault(); paymentAttempted = true }"
            class="flex min-h-0 flex-1 flex-col"
        >

            @csrf
            <input type="hidden" name="customer_id" :value="customerId">
            <input type="hidden" name="grooming_appointment_id" :value="appointmentId">

            <div class="space-y-3 p-4 sm:p-5">

                {{-- TOTAL --}}
                <div class="app-payment-surface rounded-xl bg-slate-950 p-4 text-white">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Amount Due
                            </p>

                            <p class="mt-1 text-2xl font-bold">
                                ₱<span x-text="total.toFixed(2)"></span>
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-slate-400">
                                Items
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                <span x-text="cartItemCount"></span>
                            </p>
                        </div>

                    </div>

                </div>


                {{-- PAYMENT METHOD --}}
                <div>

                    <label for="pos-payment-method" class="mb-1 block text-sm font-semibold text-slate-700">
                        Payment Method
                    </label>

                    <select
                        id="pos-payment-method"
                        name="payment_method"
                        x-model="paymentMethod"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 focus:border-slate-400 focus:bg-white focus:ring-slate-200"
                        required
                    >
                        <option value="cash"> Cash</option>
                        <option value="card"> Card</option>
                        <option value="gcash"> GCash</option>
                        <option value="bank_transfer"> Bank Transfer</option>
                    </select>

                </div>


                {{-- AMOUNT PAID --}}
                <div>

                    <label for="pos-amount-paid" class="mb-1 block text-sm font-semibold text-slate-700">
                        Amount Paid
                    </label>

                    <div class="relative">

                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">
                            ₱
                        </span>

                        <input
                            type="number"
                            id="pos-amount-paid"
                            step="0.01"
                            min="0"
                            name="amount_paid"
                            x-ref="amountPaidInput"
                            x-model.number="amountPaid"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-9 text-lg font-bold focus:border-slate-400 focus:bg-white focus:ring-slate-200"
                        >

                    </div>

                </div>


                {{-- PAYMENT SUMMARY --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3" role="status" aria-live="polite">

                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">
                            Subtotal
                        </span>

                        <strong class="text-slate-900">
                            ₱<span x-text="subtotal.toFixed(2)"></span>
                        </strong>
                    </div>

                    <div class="mt-2 flex justify-between border-t border-slate-200 pt-2 text-sm">
                        <span class="font-semibold text-slate-700">Total</span>
                        <strong class="text-base text-slate-900">₱<span x-text="total.toFixed(2)"></span></strong>
                    </div>

                    <div class="mt-2 flex justify-between">
                        <span class="text-sm text-slate-500">
                            Change
                        </span>

                        <strong
                            class="text-lg"
                            :class="!paymentAttempted ? 'text-slate-500' : (amountPaid >= total ? 'text-emerald-600' : 'text-red-500')"
                        >
                            ₱<span x-text="change.toFixed(2)"></span>
                        </strong>
                    </div>

                    <div
                        x-show="paymentAttempted && amountPaid < total"
                        x-cloak
                        class="mt-2 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600"
                    >
                        Amount paid is less than the total.
                    </div>

                </div>


                {{-- ITEMS --}}
                <template x-for="(item, index) in cart" :key="'form-' + index">

                    <template x-if="item.type !== 'grooming'">
                    <div>

                        <input
                            type="hidden"
                            :name="'items[' + index + '][item_type]'"
                            :value="item.type"
                        >

                        <input
                            type="hidden"
                            :name="'items[' + index + '][product_id]'"
                            :value="item.type === 'product' ? item.id : ''"
                        >

                        <input
                            type="hidden"
                            :name="'items[' + index + '][service_id]'"
                            :value="item.type === 'service' ? item.id : ''"
                        >

                        <input
                            type="hidden"
                            :name="'items[' + index + '][quantity]'"
                            :value="item.quantity"
                        >

                    </div>
                    </template>

                </template>

            </div>


            {{-- MODAL FOOTER --}}
            <div class="shrink-0 border-t border-slate-100 bg-white p-3 sm:p-4">

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800"
                >
                    Complete Sale
                </button>

                <button
                    type="button"
                    @click="paymentModal = false"
                    class="mt-1 w-full rounded-xl py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>
</div>

@endsection

@push('styles')

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Smooth scrollbar for the POS cart */
    .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
</style>

@endpush

@push('scripts')

<script>
function posSystem() {
    return {

        tab: 'products',

        search: '',

        productCategory: 'All',

        products: @js($products->map(fn ($product) => ['name' => $product->name, 'category' => filled($product->category) ? $product->category : 'Uncategorized'])->values()),

        customerId: @js((string) optional($selectedAppointment?->customer)->id),

        appointmentId: @js((string) optional($selectedAppointment)->id),

        selectedAppointment: @js($selectedAppointment),

        selectedAppointments: @js($selectedAppointments->values()),

        loyaltyVoucherPercent: @js($selectedVoucherPercent),

        autoPayment: @js($autoPayment),

        cart: [],

        paymentModal: false,

        amountPaid: '',

        paymentMethod: 'cash',

        paymentAttempted: false,

        init() {
            if (this.selectedAppointments.length) {
                this.selectedAppointments.forEach((appointment) => {
                    this.cart.push({
                        type: 'grooming',
                        id: appointment.id,
                        name: 'Grooming - ' + (appointment.pet?.name || 'Pet'),
                        price: Number(appointment.grooming_price || 0),
                        quantity: 1
                    });
                });

                if (this.autoPayment) {
                    this.$nextTick(() => {
                        this.paymentAttempted = false;
                        this.paymentModal = true;
                        this.$nextTick(() => this.$refs.amountPaidInput?.focus());
                    });
                }
            }
        },

        matches(name, category) {
            return name.toLowerCase().includes(this.search.toLowerCase()) &&
                (this.productCategory === 'All' || this.productCategory === category);
        },

        addProduct(product) {

            if (product.stock <= 0) {
                alert('This product is out of stock.');
                return;
            }

            const existing = this.cart.find(
                item => item.type === 'product' && item.id === product.id
            );

            if (existing) {

                if (existing.quantity >= product.stock) {
                    alert('Not enough stock.');
                    return;
                }

                existing.quantity++;

            } else {

                this.cart.push({
                    type: 'product',
                    id: product.id,
                    name: product.name,
                    price: Number(product.price),
                    originalPrice: Number(product.originalPrice ?? product.price),
                    nearExpiry: Boolean(product.nearExpiry),
                    expiryDiscountPercent: Number(product.expiryDiscountPercent || 0),
                    priceBreakdown: product.priceBreakdown || [],
                    quantity: 1,
                    stock: product.stock
                });

            }
        },

        addService(service) {

            this.cart.push({
                type: 'service',
                id: service.id,
                name: service.name,
                price: Number(service.price),
                quantity: 1
            });

        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },

        increase(index) {

            const item = this.cart[index];

            if (item.type === 'product' &&
                item.quantity >= item.stock) {

                alert('Not enough stock.');
                return;
            }

            item.quantity++;
        },

        decrease(index) {

            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeItem(index);
            }

        },

        setQuantity(index, value) {
            const item = this.cart[index];
            const requested = Number.parseInt(value, 10);

            if (!Number.isFinite(requested) || requested < 1) {
                return;
            }

            if (item.type === 'product' && requested > item.stock) {
                item.quantity = item.stock;
                alert(`Only ${item.stock} units are available.`);
                return;
            }

            item.quantity = requested;
        },

        lineTotal(item) {
            if (item.type !== 'product' || !item.priceBreakdown?.length) {
                return item.price * item.quantity;
            }

            let remaining = item.quantity;
            let total = 0;

            for (const batch of item.priceBreakdown) {
                const quantity = Math.min(Number(batch.quantity), remaining);
                total += quantity * Number(batch.unit_price);
                remaining -= quantity;
                if (remaining <= 0) break;
            }

            return Math.round(total * 100) / 100;
        },

        lineDiscount(item) {
            if (item.type !== 'product' || !item.priceBreakdown?.length) {
                return item.nearExpiry
                    ? Math.max(Number(item.originalPrice || item.price) - item.price, 0) * item.quantity
                    : 0;
            }

            let remaining = item.quantity;
            let discount = 0;

            for (const batch of item.priceBreakdown) {
                const quantity = Math.min(Number(batch.quantity), remaining);
                discount += Math.max(Number(batch.original_price) - Number(batch.unit_price), 0) * quantity;
                remaining -= quantity;
                if (remaining <= 0) break;
            }

            return Math.round(discount * 100) / 100;
        },

        lineUnitPrice(item) {
            return item.quantity > 0 ? this.lineTotal(item) / item.quantity : 0;
        },

        get cartItemCount() {

            return this.cart.reduce(
                (count, item) => count + item.quantity,
                0
            );

        },

        get subtotal() {

            return this.cart.reduce(
                (sum, item) => sum + this.lineTotal(item),
                0
            );

        },

        get discount() {
            const itemDiscount = this.cart.reduce((sum, item) => sum + this.lineDiscount(item), 0);
            const groomingSubtotal = this.cart
                .filter(item => item.type === 'grooming')
                .reduce((sum, item) => sum + this.lineTotal(item), 0);
            const loyaltyDiscount = groomingSubtotal * (this.loyaltyVoucherPercent / 100);

            return itemDiscount + loyaltyDiscount;
        },

        get taxableAmount() {
            return Math.max(this.subtotal - (this.discount - this.cart.reduce((sum, item) => sum + this.lineDiscount(item), 0)), 0);
        },

        get total() {
            return this.taxableAmount;
        },

        get change() {
            return Math.max(
                Number(this.amountPaid || 0) - this.total,
                0
            );
        },

        openPayment() {

            if (this.cart.length === 0) {
                return;
            }

            this.amountPaid = '';
            this.paymentMethod = 'cash';
            this.paymentAttempted = false;

            this.paymentModal = true;
            this.$nextTick(() => this.$refs.amountPaidInput?.focus());

        }

    }
}
</script>

@endpush
