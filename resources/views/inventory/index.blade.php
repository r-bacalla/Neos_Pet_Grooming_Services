@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="min-h-screen bg-slate-50" x-data="{ stockInOpen: @js(old('_form') === 'stock-in'), addProductOpen: @js(old('_form') === 'add-product'), editItemOpen: @js(old('_form') === 'edit-product'), stockInProducts: @js($stockProducts->map(fn ($product) => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'stock' => $product->stock])), stockInProductSearch: @js(optional($stockProducts->firstWhere('id', old('product_id')))->name ?? ''), stockInProductDropdownOpen: false, selectedStockInProductId: @js((string) old('product_id', '')), get filteredStockInProducts() { const search = this.stockInProductSearch.trim().toLowerCase(); if (search === '') return this.stockInProducts; return this.stockInProducts.filter(product => product.name.toLowerCase().includes(search) || product.sku.toLowerCase().includes(search)); }, editForm: { id: @js((string) old('product_id', '')), name: @js(old('name', '')), sku: @js(old('sku', '')), category: @js(old('category', '')), cost: @js(old('cost', '')), price: @js(old('price', '')), low_stock_level: @js(old('low_stock_level', 5)), expiry_date: @js(old('expiry_date', '')), expiry_discount: @js(old('expiry_discount', 0)) }, importOpen: false, categoryDropdownOpen: false, categorySearch: @js((string) old('category', '')), inventoryCategories: @js($categories), get filteredCategoryOptions() { const search = this.categorySearch.trim().toLowerCase(); return this.inventoryCategories.filter(category => category.toLowerCase().includes(search)); }, openEditItem(product) { this.editForm = { id: product.id, name: product.name || '', sku: product.sku || '', category: product.category || '', cost: product.cost ?? 0, price: product.price ?? 0, low_stock_level: product.low_stock_level ?? 5, expiry_date: product.expiry_date || '', expiry_discount: product.expiry_discount ?? 0 }; this.editItemOpen = true; this.$nextTick(() => this.$refs.editItemName.focus()); } }" @keydown.escape.window="stockInOpen = false; addProductOpen = false; editItemOpen = false; importOpen = false; categoryDropdownOpen = false">
    <div class="inventory-page-shell mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-4 flex flex-col gap-4 rounded-2xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between sm:p-5">
            <div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Inventory</h1>
                    <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">Record received stock by batch and manage inventory available for product allocation.</p>
                </div>
            </div>

            <div class="inventory-action-tray grid w-full gap-2 sm:grid-cols-[minmax(6rem,auto)_minmax(20rem,1fr)] lg:w-auto">
                <div class="flex min-h-11 items-center justify-between gap-3 rounded-lg border border-white/10 bg-white/10 px-3 py-2 sm:row-span-2 sm:flex-col sm:items-start sm:justify-center">
                    <span class="text-xs font-medium text-emerald-100/80">Products</span>
                    <span class="text-lg font-bold tabular-nums text-white">{{ $products->total() }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="stockInOpen = true; $nextTick(() => $refs.stockInProduct.focus())" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" /></svg>
                        Stock In
                    </button>
                    <button type="button" @click="addProductOpen = true; $nextTick(() => $refs.newProductName.focus())" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14m-7-7h14" /></svg>
                        Add Item
                    </button>
                    <button type="button" @click="importOpen = true" class="col-span-2 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3 py-2.5 text-sm font-semibold text-white transition hover:border-white/50 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/80">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14" /></svg>
                        Import Products
                    </button>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <div>
                        <p class="font-semibold text-emerald-900">
                            Inventory updated
                        </p>
                        <p class="mt-0.5 text-sm text-emerald-700">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="font-semibold text-red-900">
                            Unable to update inventory
                        </p>
                        <p class="mt-0.5 text-sm text-red-700">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <div x-show="addProductOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-emerald-950/60 p-4 backdrop-blur-sm" @click.self="addProductOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="add-item-title" class="my-auto flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Product catalog</p>
                        <h2 id="add-item-title" class="mt-1 text-lg font-bold">Add Item</h2>
                        <p class="mt-1 text-sm text-emerald-100/80">Create a product listing, then record delivered stock separately.</p>
                    </div>
                    <button type="button" @click="addProductOpen = false" aria-label="Close add item form" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('products.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <input type="hidden" name="_form" value="add-product">
                    @if($errors->any() && old('_form') === 'add-product')
                        <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                            <p class="font-semibold">Check the product details below.</p>
                            <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="space-y-4 overflow-y-auto p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="new_product_name" class="mb-1 block text-sm font-semibold text-slate-700">Product name *</label>
                            <input id="new_product_name" x-ref="newProductName" type="text" name="name" value="{{ old('name') }}" maxlength="255" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="new_product_sku" class="mb-1 block text-sm font-semibold text-slate-700">SKU *</label>
                            <input id="new_product_sku" type="text" name="sku" value="{{ old('sku') }}" maxlength="100" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="relative" @click.outside="categoryDropdownOpen = false">
                            <label for="new_product_category" class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                            <div class="relative">
                                <input id="new_product_category" type="text" name="category" x-model="categorySearch" @input="categoryDropdownOpen = true" maxlength="100" placeholder="Type a category" autocomplete="off" role="combobox" aria-controls="inventory-category-options" aria-autocomplete="list" :aria-expanded="categoryDropdownOpen" class="w-full rounded-lg border-slate-300 bg-white py-2.5 pl-3 pr-11 text-sm">
                                <button type="button" @mousedown.prevent @click="categoryDropdownOpen = !categoryDropdownOpen" aria-label="Toggle saved categories" title="Choose a saved category" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-900">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 10 5 5 5-5"/></svg>
                                </button>
                            </div>
                            <div id="inventory-category-options" x-show="categoryDropdownOpen" x-cloak role="listbox" class="absolute inset-x-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                <template x-for="category in filteredCategoryOptions" :key="category">
                                    <button type="button" role="option" @mousedown.prevent @click="categorySearch = category; categoryDropdownOpen = false" class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" x-text="category"></button>
                                </template>
                                <p x-show="filteredCategoryOptions.length === 0" class="px-3 py-2 text-sm text-slate-500">No saved category matches. Your typed category will be added.</p>
                            </div>
                        </div>
                        <div>
                            <label for="new_product_low_stock_level" class="mb-1 block text-sm font-semibold text-slate-700">Low-stock warning level *</label>
                            <input id="new_product_low_stock_level" type="number" name="low_stock_level" min="0" value="{{ old('low_stock_level', 5) }}" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="new_product_cost" class="mb-1 block text-sm font-semibold text-slate-700">Cost *</label>
                            <input id="new_product_cost" type="number" name="cost" min="0" step="0.01" value="{{ old('cost', 0) }}" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="new_product_price" class="mb-1 block text-sm font-semibold text-slate-700">Selling price *</label>
                            <input id="new_product_price" type="number" name="price" min="0" step="0.01" value="{{ old('price', 0) }}" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="new_product_expiry_date" class="mb-1 block text-sm font-semibold text-slate-700">Expiry date</label>
                            <input id="new_product_expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="new_product_expiry_discount" class="mb-1 block text-sm font-semibold text-slate-700">Expiry discount (%)</label>
                            <input id="new_product_expiry_discount" type="number" name="expiry_discount" min="0" max="100" step="0.01" value="{{ old('expiry_discount', 0) }}" class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                    </div>
                    </div>
                    <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4">
                        <button type="button" @click="addProductOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Save Item</button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="editItemOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4" @click.self="editItemOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="edit-item-title" class="my-auto max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Inventory item</p>
                        <h2 id="edit-item-title" class="mt-1 text-lg font-bold text-white">Edit Item</h2>
                        <p class="mt-1 text-sm text-emerald-100/80">Update product details and pricing. Use Stock In to change quantity.</p>
                    </div>
                    <button type="button" @click="editItemOpen = false" aria-label="Close edit item form" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" x-bind:action="$el.dataset.routePrefix + '/' + editForm.id" data-route-prefix="{{ url('/inventory/products') }}" class="space-y-4 p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit-product">
                    <input type="hidden" name="product_id" x-model="editForm.id">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="edit_product_name" class="mb-1 block text-sm font-semibold text-slate-700">Product name *</label>
                            <input id="edit_product_name" x-ref="editItemName" type="text" name="name" x-model="editForm.name" maxlength="255" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_sku" class="mb-1 block text-sm font-semibold text-slate-700">SKU *</label>
                            <input id="edit_product_sku" type="text" name="sku" x-model="editForm.sku" maxlength="100" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_category" class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                            <input id="edit_product_category" type="text" name="category" x-model="editForm.category" maxlength="100" class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_cost" class="mb-1 block text-sm font-semibold text-slate-700">Cost *</label>
                            <input id="edit_product_cost" type="number" name="cost" min="0" step="0.01" x-model="editForm.cost" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_price" class="mb-1 block text-sm font-semibold text-slate-700">Selling price *</label>
                            <input id="edit_product_price" type="number" name="price" min="0" step="0.01" x-model="editForm.price" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_low_stock_level" class="mb-1 block text-sm font-semibold text-slate-700">Low-stock warning level *</label>
                            <input id="edit_product_low_stock_level" type="number" name="low_stock_level" min="0" x-model="editForm.low_stock_level" required class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_expiry_date" class="mb-1 block text-sm font-semibold text-slate-700">Expiry date</label>
                            <input id="edit_product_expiry_date" type="date" name="expiry_date" x-model="editForm.expiry_date" class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label for="edit_product_expiry_discount" class="mb-1 block text-sm font-semibold text-slate-700">Expiry discount (%)</label>
                            <input id="edit_product_expiry_discount" type="number" name="expiry_discount" min="0" max="100" step="0.01" x-model="editForm.expiry_discount" class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="editItemOpen = false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900">Save Changes</button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="stockInOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-emerald-950/60 p-4 backdrop-blur-sm" @click.self="stockInOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="stock-in-title" class="my-auto flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Inventory movement</p>
                        <h2 id="stock-in-title" class="mt-1 text-lg font-bold">Record Product Arrival</h2>
                        <p class="mt-1 text-sm text-emerald-100/80">Add a dated stock batch and its supplier details.</p>
                    </div>
                    <button type="button" @click="stockInOpen = false" aria-label="Close stock-in form" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form method="POST" x-ref="stockInForm" action="{{ old('product_id') ? route('inventory.stock-in', old('product_id')) : ($stockProducts->isNotEmpty() ? route('inventory.stock-in', $stockProducts->first()) : '#') }}" class="flex min-h-0 flex-1 flex-col" data-stock-in-form>
                    @csrf
                    <input type="hidden" name="_form" value="stock-in">
                    @if($errors->any() && old('_form') === 'stock-in')
                        <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                            <p class="font-semibold">Check the arrival details below.</p>
                            <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="space-y-4 overflow-y-auto p-5">
                    <div class="relative" @click.outside="stockInProductDropdownOpen = false">
                        <label for="stock_in_product" class="mb-1 block text-sm font-semibold text-slate-700">Product *</label>
                        <div class="relative">
                            <input id="stock_in_product" x-ref="stockInProduct" type="text" x-model="stockInProductSearch" @input="selectedStockInProductId = ''; stockInProductDropdownOpen = true" autocomplete="off" role="combobox" aria-controls="stock-in-product-options" aria-autocomplete="list" :aria-expanded="stockInProductDropdownOpen" placeholder="Search product or SKU..." class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm" required>
                            <button type="button" @mousedown.prevent @click="stockInProductDropdownOpen = !stockInProductDropdownOpen" aria-label="Toggle product suggestions" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-900">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 10 5 5 5-5"/></svg>
                            </button>
                        </div>
                        <div id="stock-in-product-options" x-show="stockInProductDropdownOpen" x-cloak role="listbox" class="absolute inset-x-0 top-full z-20 mt-1 max-h-60 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                            <template x-for="product in filteredStockInProducts" :key="product.id">
                                <button type="button" role="option" @click="selectedStockInProductId = product.id; stockInProductSearch = product.name + ' · ' + product.stock + ' in stock'; stockInProductDropdownOpen = false; $refs.stockInForm.action = '{{ url('/inventory') }}/' + product.id + '/stock-in'" class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                    <span class="min-w-0">
                                        <span class="block truncate" x-text="product.name"></span>
                                        <span class="block text-xs text-slate-500" x-text="product.sku"></span>
                                    </span>
                                    <span class="shrink-0 text-xs text-slate-500"><span x-text="product.stock"></span> in stock</span>
                                </button>
                            </template>
                            <p x-show="filteredStockInProducts.length === 0" class="px-3 py-3 text-sm text-slate-500">No matching products found.</p>
                        </div>
                        <input type="hidden" name="product_id" :value="selectedStockInProductId || ''" required>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="stock_in_quantity" class="mb-1 block text-sm font-semibold text-slate-700">Received quantity *</label>
                            <input id="stock_in_quantity" type="number" name="quantity" min="1" required value="{{ old('quantity') }}" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label for="stock_in_arrival_at" class="mb-1 block text-sm font-semibold text-slate-700">Product arrival date and time *</label>
                            <input id="stock_in_arrival_at" type="datetime-local" name="arrival_at" required value="{{ old('arrival_at', now()->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label for="stock_in_expiry_date" class="mb-1 block text-sm font-semibold text-slate-700">Batch expiry date</label>
                            <input id="stock_in_expiry_date" type="date" name="expiry_date" min="{{ today()->toDateString() }}" value="{{ old('expiry_date') }}" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label for="stock_in_supplier" class="mb-1 block text-sm font-semibold text-slate-700">Supplier</label>
                            <input id="stock_in_supplier" type="text" name="supplier" maxlength="255" value="{{ old('supplier') }}" placeholder="Supplier name" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="stock_in_reason" class="mb-1 block text-sm font-semibold text-slate-700">Delivery notes</label>
                            <textarea id="stock_in_reason" name="reason" maxlength="500" rows="2" placeholder="Optional delivery details" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                    </div>
                    <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4">
                        <button type="button" @click="stockInOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Record Stock In</button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="importOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-emerald-950/60 p-4 backdrop-blur-sm" @click.self="importOpen = false" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="import-products-title" class="my-auto w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Inventory setup</p>
                        <h2 id="import-products-title" class="mt-1 text-lg font-bold">Import Products</h2>
                        <p class="mt-1 text-sm text-emerald-100/80">Add or update catalog items from a spreadsheet.</p>
                    </div>
                    <button type="button" @click="importOpen = false" aria-label="Close product import" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('inventory.import') }}" enctype="multipart/form-data" class="space-y-4 p-5" data-inventory-import>
                    @csrf
                    <div>
                        <label for="inventory_spreadsheet" class="mb-2 block text-sm font-semibold text-slate-700">Choose a spreadsheet <span class="text-red-600">*</span></label>
                        <input id="inventory_spreadsheet" type="file" name="spreadsheet" accept=".xlsx,.csv" required class="block min-h-12 w-full rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 file:mr-3 file:min-h-12 file:border-0 file:border-r file:border-slate-300 file:bg-white file:px-4 file:font-semibold file:text-emerald-800">
                        <p class="mt-2 text-xs text-slate-500">Supported formats: .xlsx and .csv. Matching SKUs are updated; new SKUs are added.</p>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ asset('inventory-import-template.csv') }}" download class="text-sm font-semibold text-emerald-800 underline underline-offset-4 hover:text-emerald-950">Download column template</a>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="importOpen = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                            <button type="submit" class="rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Import Products</button>
                        </div>
                    </div>
                    <p data-import-status role="status" aria-live="polite" class="min-h-10 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600"></p>
                </form>
            </section>
        </div>

        {{-- Inventory Card --}}
        <div class="inventory-table-card overflow-hidden rounded-3xl border bg-white shadow-sm">

            {{-- Card Header --}}
            <div class="border-b border-emerald-900 bg-emerald-950 px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white">
                            Product Inventory
                        </h2>
                        <p class="mt-1 text-sm text-emerald-100/80">
                            Review stock levels and stock history.
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-end gap-x-8 gap-y-2 text-xs text-emerald-100/80">
                        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>In Stock</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Low Stock</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>Out of Stock</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('inventory.index') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative min-w-0 flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path stroke-linecap="round" d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search product, SKU, or category..." class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    </div>

                    <select name="category" aria-label="Filter inventory by category" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path stroke-linecap="round" d="m21 21-4.3-4.3" />
                        </svg>
                        Search
                    </button>

                    @if(request('search') || request('category'))
                        <a href="{{ route('inventory.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-white/40 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto bg-emerald-950 px-1 pb-1 lg:block">
                <table class="min-w-full divide-y divide-slate-200 bg-white shadow-sm">

                    <thead class="bg-emerald-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Product
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                SKU
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Category
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Inventory Stock
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Selling Price
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Potential Selling Value
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Last Stock In
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="w-[8rem] min-w-[8rem] px-3 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($products as $product)

                        <tr class="transition hover:bg-slate-50/70">

                            {{-- Product --}}
                            <td class="px-6 py-5">
                                <div>
                                    <p class="whitespace-normal font-semibold text-slate-900">
                                        {{ $product->name }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Inventory item
                                    </p>
                                </div>
                            </td>

                            {{-- SKU --}}
                            <td class="px-6 py-5">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-medium text-slate-600">
                                    {{ $product->sku }}
                                </span>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-5 text-sm text-slate-600">
                                {{ $product->category ?: '—' }}
                            </td>

                            {{-- Current Stock --}}
                            <td class="px-6 py-5 text-center">
                                <span class="text-xl font-bold
                                    @if($product->stock <= 0)
                                        text-red-600
                                    @elseif($product->stock <= $product->low_stock_level)
                                        text-amber-600
                                    @else
                                        text-emerald-600
                                    @endif
                                ">
                                    {{ $product->stock }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-right text-sm font-semibold text-slate-700">
                                ₱{{ number_format((float) $product->price, 2) }}
                            </td>

                            <td class="px-6 py-5 text-center text-sm font-semibold text-slate-900">
                                ₱{{ number_format($product->potentialSellingValue(), 2) }}
                            </td>

                            <td class="px-6 py-5 text-sm text-slate-600">
                                @if($product->last_stock_in_at)
                                    <span class="font-semibold text-slate-800">
                                        {{ $product->last_stock_in_quantity }} units
                                    </span>
                                    <span class="mt-1 block text-xs text-slate-400">
                                        {{ $product->last_stock_in_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">No stock-in yet</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5">

                                @if($product->stock <= 0)

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Out of Stock
                                    </span>

                                @elseif($product->stock <= $product->low_stock_level)

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Low Stock
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        In Stock
                                    </span>

                                @endif

                                @if($product->hasExpiredStockBatches() || $product->isExpired())
                                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Expired
                                    </span>
                                @endif

                            </td>

                            <td class="w-[8rem] min-w-[8rem] px-3 py-5">
                                <div class="grid w-24 grid-cols-2 gap-x-1">
                                    <a href="{{ route('inventory.history', $product) }}" class="record-action-secondary inline-flex min-h-8 w-full items-center justify-center whitespace-nowrap rounded-md px-2 text-center text-[11px] font-semibold">View</a>
                                    <button type="button" @click="openEditItem(JSON.parse($event.currentTarget.dataset.editItem))" data-edit-item="{{ json_encode(['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'category' => $product->category, 'cost' => $product->cost, 'price' => $product->price, 'low_stock_level' => $product->low_stock_level, 'expiry_date' => $product->expiry_date?->toDateString(), 'expiry_discount' => $product->expiry_discount]) }}" class="record-action-secondary inline-flex min-h-8 w-full items-center justify-center whitespace-nowrap rounded-md px-2 text-center text-[11px] font-semibold">Edit</button>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to Archive? Its stock and history will be kept.');" class="col-span-2 mt-1.5 w-full border-t border-slate-200 pt-1.5">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="record-action-danger inline-flex min-h-8 w-full items-center justify-center whitespace-nowrap rounded-md px-2 text-[11px] font-semibold">Delete</button>
                                    </form>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">

                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                    No products found
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    There are currently no products available in inventory.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Mobile / Tablet Cards --}}
            <div class="space-y-3 rounded-xl border border-emerald-900 bg-emerald-950 px-2 pb-2 pt-0 lg:hidden">

                @forelse($products as $product)

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                        {{-- Product Header --}}
                        <div class="flex items-start justify-between gap-4">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="min-w-0">
                                    <h3 class="whitespace-normal font-semibold text-slate-900">
                                        {{ $product->name }}
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        SKU:
                                        <span class="font-mono font-medium text-slate-700">
                                            {{ $product->sku }}
                                        </span>
                                    </p>
                                </div>

                            </div>

                            @if($product->stock <= 0)

                                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                    Out of Stock
                                </span>

                            @elseif($product->stock <= $product->low_stock_level)

                                <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    Low Stock
                                </span>

                            @else

                                <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    In Stock
                                </span>

                            @endif

                            @if($product->hasExpiredStockBatches() || $product->isExpired())
                                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Expired</span>
                            @endif

                        </div>

                        {{-- Stock Information --}}
                        <div class="mt-5 grid grid-cols-2 gap-3">

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                    Inventory Stock
                                </p>

                                <p class="mt-1 text-2xl font-bold
                                    @if($product->stock <= 0)
                                        text-red-600
                                    @elseif($product->stock <= $product->low_stock_level)
                                        text-amber-600
                                    @else
                                        text-emerald-600
                                    @endif
                                ">
                                    {{ $product->stock }}
                                </p>
                            </div>

                        </div>

                        {{-- Details --}}
                        <div class="mt-4 flex items-center justify-between text-sm">

                            <span class="text-slate-500">
                                Category
                            </span>

                            <span class="font-medium text-slate-700">
                                {{ $product->category ?: '—' }}
                            </span>

                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-500">Last Stock In</span>
                            <span class="text-right font-medium text-slate-700">
                                @if($product->last_stock_in_at)
                                    {{ $product->last_stock_in_quantity }} units · {{ $product->last_stock_in_at->format('M d, Y') }}
                                @else
                                    No stock-in yet
                                @endif
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Selling Price</p>
                                <p class="mt-1 text-lg font-bold text-slate-900">₱{{ number_format((float) $product->price, 2) }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Potential Selling Value</p>
                                <p class="mt-1 text-lg font-bold text-slate-900">₱{{ number_format($product->potentialSellingValue(), 2) }}</p>
                                <p class="mt-1 text-xs text-slate-400">Price x In Stock</p>
                            </div>
                        </div>

                        <div class="mt-4 grid w-full grid-cols-2 gap-2">
                            <a href="{{ route('inventory.history', $product) }}" class="record-action-secondary inline-flex min-h-10 w-full items-center justify-center rounded-md px-3 text-center text-sm font-semibold">
                                View
                            </a>

                            <button type="button" @click="openEditItem(JSON.parse($event.currentTarget.dataset.editItem))" data-edit-item="{{ json_encode(['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'category' => $product->category, 'cost' => $product->cost, 'price' => $product->price, 'low_stock_level' => $product->low_stock_level, 'expiry_date' => $product->expiry_date?->toDateString(), 'expiry_discount' => $product->expiry_discount]) }}" class="record-action-secondary inline-flex min-h-10 w-full items-center justify-center rounded-md px-3 text-center text-sm font-semibold">Edit</button>

                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to Archive? Its stock and history will be kept.');" class="col-span-2 border-t border-slate-200 pt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="record-action-danger inline-flex min-h-10 w-full items-center justify-center rounded-md px-3 text-sm font-semibold">Delete</button>
                            </form>
                        </div>

                    </div>

                @empty

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                            No products found
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            There are currently no products available in inventory.
                        </p>

                    </div>

                @endforelse

            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    {{ $products->links() }}
                </div>
            @endif

        </div>

    </div>
</div>

@endsection