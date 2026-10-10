@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="min-h-screen bg-slate-50" x-data="{ addProductsOpen: false, selectedInventoryProduct: null, inventoryProductSearch: '', inventoryProductDropdownOpen: false, inventoryProducts: @js($inventoryProductOptions), get filteredInventoryProducts() { const search = this.inventoryProductSearch.trim().toLowerCase(); return this.inventoryProducts.filter(product => product.name.toLowerCase().includes(search)); } }" @keydown.escape.window="addProductsOpen = false">

    <div class="products-page-shell mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-5 flex flex-col gap-4 rounded-2xl border border-emerald-900 bg-emerald-950 p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-4">

            <div>
                <div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Products
                        </h1>
                    </div>
                    <p class="mt-1 text-sm text-emerald-100/80">
                        This catalog displays the same products recorded in Inventory. Stock changes are recorded there.
                    </p>
                </div>

            </div>

                <div class="products-header-actions flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('products.expired') }}" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-800 transition duration-150 hover:border-red-400 hover:bg-red-200 hover:text-red-950 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        Expired products
                    </a>
                    <button type="button" @click="addProductsOpen = true; inventoryProductSearch = ''; selectedInventoryProduct = null; inventoryProductDropdownOpen = false; $nextTick(() => $refs.inventoryProduct.focus())"
                       class="inline-flex items-center justify-center rounded-xl border border-white bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition duration-150 hover:border-emerald-300 hover:bg-slate-50 hover:text-emerald-900 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                        Add Products
                    </button>
                </div>

        </div>

        <div x-show="addProductsOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4" @click.self="addProductsOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="add-products-title" class="my-auto w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="add-products-title" class="text-lg font-bold text-slate-900">Add Products</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose an inventory item and quantity to make available in Products.</p>
                    </div>
                    <button type="button" @click="addProductsOpen = false" aria-label="Close add products dialog" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @if($inventoryProducts->isNotEmpty())
                    <form x-ref="addProductForm" method="POST" action="#" data-route-prefix="{{ url('/inventory/products') }}" class="space-y-4 p-5">
                        @csrf
                        <div class="relative" @click.outside="inventoryProductDropdownOpen = false">
                            <label for="inventory-product-search" class="mb-1 block text-sm font-semibold text-slate-700">Inventory product</label>
                            <div class="relative">
                                <input id="inventory-product-search" x-ref="inventoryProduct" type="text" x-model="inventoryProductSearch" @input="selectedInventoryProduct = null; $refs.addProductForm.action = '#'; $refs.addProductQuantity.max = ''; inventoryProductDropdownOpen = true" autocomplete="off" role="combobox" aria-controls="inventory-product-options" aria-autocomplete="list" :aria-expanded="inventoryProductDropdownOpen" placeholder="Search inventory products..." class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm">
                                <button type="button" @mousedown.prevent @click="inventoryProductDropdownOpen = !inventoryProductDropdownOpen" aria-label="Toggle inventory products" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-900">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 10 5 5 5-5"/></svg>
                                </button>
                            </div>
                            <div id="inventory-product-options" x-show="inventoryProductDropdownOpen" x-cloak role="listbox" class="absolute inset-x-0 top-full z-20 mt-1 max-h-52 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                <template x-for="product in filteredInventoryProducts" :key="product.id">
                                    <button type="button" role="option" @click="selectedInventoryProduct = product; inventoryProductSearch = product.name; inventoryProductDropdownOpen = false; $refs.addProductQuantity.max = product.stock; $refs.addProductForm.action = '{{ url('/inventory/products') }}/' + product.id + '/allocate'" class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                        <span class="min-w-0 truncate" x-text="product.name"></span>
                                        <span class="shrink-0 text-xs text-slate-500"><span x-text="product.stock"></span> in Inventory</span>
                                    </button>
                                </template>
                                <p x-show="filteredInventoryProducts.length === 0" class="px-3 py-3 text-sm text-slate-500">No matching inventory products.</p>
                            </div>
                            <input type="hidden" name="product_id" :value="selectedInventoryProduct?.id || ''" required>
                        </div>
                        <div>
                            <label for="inventory-product-quantity" class="mb-1 block text-sm font-semibold text-slate-700">Quantity to add</label>
                            <input id="inventory-product-quantity" x-ref="addProductQuantity" type="number" name="quantity" min="1" required placeholder="Enter quantity" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                            <button type="button" @click="addProductsOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                            <button type="submit" :disabled="!selectedInventoryProduct" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Add to Products</button>
                        </div>
                    </form>
                @else
                    <div class="p-5">
                        <p class="text-sm text-slate-600">No eligible inventory stock is available to add.</p>
                        <div class="mt-4 flex justify-end">
                            <button type="button" @click="addProductsOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                        </div>
                    </div>
                @endif
            </section>
        </div>


        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">

                <div class="pt-1 text-sm font-medium">
                    {{ session('success') }}
                </div>

            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                    !
                </div>

                <div class="pt-1 text-sm font-medium">
                    {{ session('error') }}
                </div>

            </div>
        @endif


        {{-- Main Card --}}
        <div class="product-inventory-module inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Search Header --}}
            <div class="border-b border-emerald-100 bg-emerald-50/70 px-5 py-5 sm:px-6">

                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Product Inventory</h2>
                            <p class="text-sm text-slate-500">Search and manage your available products.</p>
                        </div>
                    </div>

                    @if($products->total() > 0)
                        <span class="text-sm font-medium text-slate-500">
                            {{ $products->total() }} {{ $products->total() === 1 ? 'product' : 'products' }}
                        </span>
                    @endif
                </div>

                <div class="mb-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-500" aria-label="Product status indicators">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>In Stock</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Low Stock</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>Out of Stock</span>
                </div>

                <form method="GET"
                      action="{{ route('products.index') }}"
                      class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">

                    {{-- Search --}}
                    <div class="relative flex-1">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path stroke-linecap="round"
                                      d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search product, SKU, or category..."
                               class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100">

                    </div>

                    <select name="category" aria-label="Filter by category" onchange="if (!this.value) this.form.querySelector('[name=search]').value = ''; this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                        <option value="" @selected(!request()->filled('category'))>All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round"
                                  d="m21 21-4.3-4.3"/>
                        </svg>

                        Search
                    </button>

                    @if(request('search') || request('category'))
                        <a href="{{ route('products.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Clear
                        </a>
                    @endif

                </form>

            </div>


            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="w-full table-fixed text-left">

                    <thead class="border-b border-emerald-100 bg-emerald-50">
                        <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500">

                            <th class="w-[23%] px-3 py-4">
                                Product
                            </th>

                            <th class="w-[12%] px-3 py-4">
                                Category
                            </th>

                            <th class="w-[12%] px-3 py-4">
                                SKU
                            </th>

                            <th class="w-[10%] px-3 py-4">
                                Price
                            </th>

                            <th class="w-[13%] px-3 py-4">
                                Product Stock
                            </th>

                            <th class="w-[12%] px-3 py-4">
                                Expiry
                            </th>

                            <th class="w-[10%] px-3 py-4">
                                Status
                            </th>

                            <th class="w-[8%] px-2 py-4 text-left">
                                Delete
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    @forelse($products as $product)

                        <tr id="product-{{ $product->id }}-desktop" class="group scroll-mt-24 transition hover:bg-slate-50">

                            {{-- Product --}}
                            <td class="px-3 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg">
                                        
                                    </div>

                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-slate-900">
                                            {{ $product->name }}
                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- Category --}}
                            <td class="px-3 py-4 text-sm text-slate-600">
                                {{ $product->category ?: '—' }}
                            </td>


                            {{-- SKU --}}
                            <td class="px-3 py-4">

                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-medium text-slate-600">
                                    {{ $product->sku }}
                                </span>

                            </td>


                            {{-- Price --}}
                            <td class="px-3 py-4">

                                <span class="font-semibold text-slate-900">
                                    ₱{{ number_format((float) $product->price, 2) }}
                                </span>

                            </td>


                            {{-- Stock --}}
                            <td class="px-3 py-4">

                                @if($product->available_stock <= 0 || $product->available_stock < $product->low_stock_level)

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        {{ $product->available_stock }} out of stock
                                    </span>

                                @elseif($product->available_stock == $product->low_stock_level)

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        {{ $product->available_stock }} low
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $product->available_stock }}
                                    </span>

                                @endif

                            </td>

                            {{-- Expiry --}}
                            <td class="px-3 py-4">

                                @if($product->expiry_date)

                                    <div class="inline-flex items-center gap-2 text-sm font-medium
                                        @if($product->hasExpiredStockBatches() || $product->isExpired()) text-red-700
                                        @elseif($product->hasNearExpiryStockBatches()) text-amber-700
                                        @else text-slate-700
                                        @endif
                                    ">
                                        @if($product->hasExpiredStockBatches() || $product->isExpired())
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-red-500" title="Expired" aria-label="Expired"></span>
                                        @elseif($product->hasNearExpiryStockBatches())
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500" title="Near Expiry" aria-label="Near Expiry"></span>
                                        @endif
                                        {{ $product->expiry_date->format('M d, Y') }}
                                    </div>

                                @else

                                    <span class="text-sm text-slate-400">
                                        No expiry
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="px-3 py-4">
                                <div class="mb-1.5">
                                    @if($product->available_stock <= 0 || $product->available_stock < $product->low_stock_level)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Out of Stock</span>
                                    @elseif($product->available_stock == $product->low_stock_level)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Low Stock</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>In Stock</span>
                                    @endif
                                </div>

                                @if($product->hasNearExpiryStockBatches())

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Near Expiry
                                    </span>

                                @endif

                            </td>

                            <td class="px-2 py-4">
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to Archive? Its stock and history will be kept.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Delete {{ $product->name }}" class="record-action-danger rounded-lg px-3 py-2 text-xs font-semibold">Delete</button>
                                </form>
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="px-6 py-16">

                                <div class="mx-auto flex max-w-md flex-col items-center text-center">

                                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl">
                                        
                                    </div>

                                    <h3 class="text-base font-bold text-slate-900">
                                        No products found
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        @if(request('search'))
                                            No products match "{{ request('search') }}".
                                        @else
                                            You haven't added any products yet.
                                        @endif
                                    </p>

                                    @if(request('search'))

                                        <a href="{{ route('products.index') }}"
                                           class="mt-5 inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                            Clear Search
                                        </a>

                                    @else

                                        <a href="{{ route('inventory.index') }}"
                                           class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                            Open Inventory
                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile / Tablet Cards --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @forelse($products as $product)

                    <div id="product-{{ $product->id }}-mobile" class="scroll-mt-24 p-5">

                        <div class="flex items-start gap-3">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xl">
                                
                            </div>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                    <div>
                                        <h3 class="font-bold text-slate-900">
                                            {{ $product->name }}
                                        </h3>

                                        <div class="mt-1 flex flex-wrap items-center gap-2">

                                            <span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-[11px] font-medium text-slate-600">
                                                {{ $product->sku }}
                                            </span>

                                            @if($product->category)
                                                <span class="text-xs text-slate-400">
                                                    {{ $product->category }}
                                                </span>
                                            @endif

                                        </div>
                                    </div>


                                    {{-- Status --}}
                                    <div class="flex shrink-0 flex-col items-end gap-1.5">
                                    @if($product->available_stock <= 0)
                                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Out of Stock</span>
                                    @elseif($product->available_stock <= $product->low_stock_level)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Low Stock</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">In Stock</span>
                                    @endif
                                    @if($product->hasNearExpiryStockBatches())

                                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Near Expiry
                                        </span>

                                    @endif
                                    </div>

                                </div>


                                {{-- Product Details --}}
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">

                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                            Price
                                        </div>

                                        <div class="mt-1 font-bold text-slate-900">
                                            ₱{{ number_format((float) $product->price, 2) }}
                                        </div>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 p-3">

                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                            Product Stock
                                        </div>

                                        <div class="mt-1">

                                            @if($product->available_stock <= 0 || $product->available_stock < $product->low_stock_level)

                                                <span class="font-bold text-red-600">{{ $product->available_stock }}</span>

                                                    @elseif($product->available_stock == $product->low_stock_level)

                                                <span class="font-bold text-amber-600">
                                                    {{ $product->available_stock }}
                                                </span>

                                                <span class="text-xs text-amber-700">
                                                    Low
                                                </span>

                                            @else

                                                <span class="font-bold text-emerald-600">
                                                    {{ $product->available_stock }}
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                    <div class="rounded-xl bg-slate-50 p-3">

                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                            Expiry
                                        </div>

                                        <div class="mt-1 inline-flex items-center gap-2 text-sm font-semibold
                                            @if($product->hasExpiredStockBatches() || $product->isExpired()) text-red-700
                                            @elseif($product->hasNearExpiryStockBatches()) text-amber-700
                                            @else text-slate-700
                                            @endif
                                        ">

                                            @if($product->expiry_date)
                                                @if($product->hasExpiredStockBatches() || $product->isExpired())
                                                    <span class="h-2 w-2 shrink-0 rounded-full bg-red-500" title="Expired" aria-label="Expired"></span>
                                                @elseif($product->hasNearExpiryStockBatches())
                                                    <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500" title="Near Expiry" aria-label="Near Expiry"></span>
                                                @endif
                                                {{ $product->expiry_date->format('M d, Y') }}
                                            @else
                                                —
                                            @endif

                                        </div>

                                    </div>

                                </div>

                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to Archive? Its stock and history will be kept.');" class="mt-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Delete Product</button>
                                </form>


                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl">
                            
                        </div>

                        <h3 class="mt-4 text-base font-bold text-slate-900">
                            No products found
                        </h3>

                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">

                            @if(request('search'))
                                No products match "{{ request('search') }}".
                            @else
                                You haven't added any products yet.
                            @endif

                        </p>

                        @if(request('search'))

                            <a href="{{ route('products.index') }}"
                               class="mt-5 inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                Clear Search
                            </a>

                        @else

                            <a href="{{ route('inventory.index') }}"
                               class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                Open Inventory
                            </a>

                        @endif

                    </div>

                @endforelse

            </div>


            {{-- Pagination --}}
            @if($products->hasPages())

                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-4 sm:px-6">

                    <div class="flex justify-center sm:justify-end">
                        {{ $products->links() }}
                    </div>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection