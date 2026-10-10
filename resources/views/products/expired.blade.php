@extends('layouts.app')

@section('title', 'Expired Products')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl space-y-5 px-4 py-5 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 rounded-2xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">Inventory control</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">Expired Products</h1>
                <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">Expired stock is written off automatically each day. Cleared batches stay here until you archive them.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15">Back to Products</a>
                <form method="POST" action="{{ route('products.expired.write-off') }}" onsubmit="return confirm('Write off all remaining expired stock? This removes the units from inventory but keeps the batch history.');">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800">Write Off Expired Stock</button>
                </form>
            </div>
        </header>

        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
        @endif

        <section class="grid gap-3 sm:grid-cols-3" aria-label="Expired stock totals">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Expired products</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totals->product_count }}</p>
            </div>
            <div class="inventory-content-frame rounded-xl border border-slate-200 bg-white px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Inventory units remaining</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totals->inventory_units }}</p>
            </div>
            <div class="inventory-content-frame rounded-xl border border-slate-200 bg-white px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Products units remaining</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totals->product_units }}</p>
            </div>
        </section>

        <div class="inventory-content-frame overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Expiry date</th>
                            <th class="px-4 py-3 text-right">Inventory stock</th>
                            <th class="px-4 py-3 text-right">Products stock</th>
                            <th class="px-4 py-3">Write-off status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($batches as $batch)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $batch->product->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $batch->batch_reference ?? $batch->product->sku }}</td>
                                <td class="px-4 py-3 text-red-700">{{ $batch->expiry_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $batch->inventory_quantity }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $batch->available_quantity }}</td>
                                <td class="px-4 py-3">
                                    @if($batch->inventory_quantity > 0 || $batch->available_quantity > 0)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">Pending write-off</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Stock removed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($batch->inventory_quantity === 0 && $batch->available_quantity === 0)
                                        <form method="POST" action="{{ route('products.expired.remove', $batch) }}" onsubmit="return confirm('Move this expired batch to Archive? It can be restored later.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="record-action-danger rounded-lg px-3 py-1.5 text-xs font-semibold">Delete</button>
                                        </form>
                                    @else
                                        <button type="button" disabled title="Write off the expired stock before moving it to Archive" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">No expired products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $batches->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection