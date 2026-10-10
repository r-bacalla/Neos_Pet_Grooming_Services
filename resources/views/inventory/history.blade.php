@extends('layouts.app')

@section('title', 'Inventory History')

@section('content')
<div class="min-h-screen bg-slate-50" x-data="{ reasonDialogOpen: false, selectedReason: '', showReason(reason) { this.selectedReason = reason; this.reasonDialogOpen = true; } }" @keydown.escape.window="reasonDialogOpen = false">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-col gap-4 rounded-2xl border border-emerald-900 bg-emerald-950 p-5 text-white shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <a href="{{ route('inventory.index') }}" class="text-sm font-semibold text-emerald-100/80 transition hover:text-white">&larr; Inventory</a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-white">{{ $product->name }} History</h1>
                <p class="mt-1 text-sm text-emerald-100/80">Complete stock audit trail for {{ $product->sku }}.</p>
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100/80">In Stock</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ $product->stock }}</p>
            </div>
        </div>

        <div class="inventory-content-frame overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Product History</h2>
                    <p class="mt-1 text-sm text-slate-500">Stock movements for {{ $product->name }}.</p>
                </div>
                <p class="text-xs font-medium text-slate-500">{{ $movements->total() }} {{ Str::plural('movement', $movements->total()) }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Previous</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Change</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">New Stock</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Products Stock</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Stock-in Reference</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Arrival Date</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Supplier</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Changed By / When</th>
                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movements as $movement)
                            <tr class="{{ $movement->archived_at ? 'bg-slate-50 text-slate-400' : '' }}">
                                <td class="px-5 py-4 text-sm font-semibold capitalize">{{ str_replace('_', ' ', $movement->type) }}</td>
                                <td class="px-5 py-4 text-right text-sm">{{ $movement->stock_before }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold {{ $movement->quantity >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold">{{ $movement->stock_after }}</td>
                                <td class="px-5 py-4 text-sm">
                                    @if($movement->available_before !== null && $movement->available_after !== null)
                                        {{ $movement->available_before }} &rarr; {{ $movement->available_after }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm font-medium">{{ $movement->batch_reference ?: '—' }}</td>
                                <td class="px-5 py-4 text-sm">{{ $movement->batch?->received_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm">{{ $movement->batch?->supplier ?: '—' }}</td>
                                <td class="px-5 py-4 text-sm">
                                    <span class="block font-semibold">{{ $movement->user->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-slate-400">{{ $movement->created_at->format('M d, Y h:i A') }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        <button type="button" @click="showReason($event.currentTarget.dataset.reason)" data-reason="{{ $movement->reason ?: 'No reason recorded.' }}" aria-label="View reason for {{ $product->name }} stock movement" class="record-action-secondary inline-flex min-h-9 w-24 items-center justify-center rounded-md px-3 text-xs font-semibold">View</button>
                                    @if(!$movement->archived_at)
                                        <form method="POST" action="{{ route('inventory.movements.archive', $movement) }}" onsubmit="return confirm('Hide this movement from active inventory? It will be archived, not permanently deleted.');" class="w-24">
                                            @csrf
                                            <button type="submit" class="record-action-danger inline-flex min-h-9 w-full items-center justify-center rounded-md px-3 text-xs font-semibold">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-xs italic">Archived</span>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-5 py-12 text-center text-sm text-slate-500">No inventory movements recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>

    <div x-show="reasonDialogOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="reasonDialogOpen = false" role="presentation">
        <section role="dialog" aria-modal="true" aria-labelledby="stock-reason-title" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                <h2 id="stock-reason-title" class="text-lg font-bold text-white">Movement Reason</h2>
                <button type="button" @click="reasonDialogOpen = false" aria-label="Close reason" class="rounded-lg border border-white/20 bg-white/10 p-2 text-white transition hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <p class="whitespace-pre-wrap break-words text-sm leading-6 text-slate-700" x-text="selectedReason"></p>
            </div>
        </section>
    </div>
</div>
@endsection
