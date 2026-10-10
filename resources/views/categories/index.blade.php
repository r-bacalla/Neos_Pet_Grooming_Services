@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-6">
        <header class="flex flex-col gap-3 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h1 class="text-2xl font-bold text-white">Categories</h1>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-lg border border-white/30 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Back to Products</a>
        </header>

        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        @if(auth()->user()->canManageInventory())
            <form method="POST" action="{{ route('categories.store') }}" class="inventory-content-frame flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
                @csrf
                <label for="new-category" class="sr-only">Category name</label>
                <input id="new-category" name="name" value="{{ old('name') }}" maxlength="255" required placeholder="New category name" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Add Category</button>
            </form>
        @endif

        @if($categories->isNotEmpty())
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" aria-label="Product categories">
                @foreach($categories as $category)
                    <a href="{{ route('categories.index', ['category' => $category->id]) }}#category-products" @class([
                        'flex min-h-24 items-center justify-between gap-4 rounded-lg border p-4 transition hover:-translate-y-0.5 hover:shadow-sm',
                        'border-emerald-700 bg-emerald-50 shadow-sm' => $selectedCategory?->id === $category->id,
                        'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50' => $selectedCategory?->id !== $category->id,
                    ])>
                        <span class="min-w-0 truncate font-semibold text-slate-900">{{ $category->name }}</span>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold tabular-nums text-slate-600">
                            {{ $category->products_count }} {{ $category->products_count === 1 ? 'product' : 'products' }}
                        </span>
                    </a>
                @endforeach
            </section>
        @else
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">No categories yet.</div>
        @endif

        @if($selectedCategory)
            <section id="category-products" class="inventory-content-frame overflow-hidden rounded-lg border border-slate-200 bg-white">
                <header class="flex flex-col gap-2 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">{{ $selectedCategory->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $selectedCategory->products->count() }} product{{ $selectedCategory->products->count() === 1 ? '' : 's' }}</p>
                    </div>
                    <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-slate-600 underline underline-offset-2 hover:text-slate-900">All categories</a>
                </header>
                <div class="divide-y divide-slate-100">
                    @forelse($selectedCategory->products as $product)
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $product->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">SKU: {{ $product->sku }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold tabular-nums text-slate-800">{{ $product->available_stock }} available</p>
                                <p class="mt-0.5 text-xs text-slate-500">₱{{ number_format((float) $product->price, 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">No products in this category.</p>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</div>
@endsection