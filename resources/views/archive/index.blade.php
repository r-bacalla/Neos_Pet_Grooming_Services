@extends('layouts.app')

@section('title', 'Archive')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:p-5">
            <h1 class="text-2xl font-bold text-white">Archive</h1>
            <p class="mt-1 text-sm text-emerald-100/80">Restore archived records or permanently delete them.</p>
        </header>

        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
        @endif

        <section class="archive-modules-grid" aria-label="Archived record modules">
            @foreach($modules as $key => $module)
            <a href="{{ route('archive.index', ['type' => $key]) }}" class="{{ $key === 'reports' ? 'archive-saved-reports-tile' : '' }} flex min-h-32 flex-col justify-between rounded-lg border p-4 transition hover:-translate-y-0.5 hover:shadow-sm {{ $type === $key ? 'border-emerald-700 bg-emerald-50 shadow-sm' : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50' }}">
                    <span class="text-sm font-semibold text-slate-800">{{ $module['label'] }}</span>
                    <span class="mt-2 text-xs text-slate-500">{{ $module['description'] }}</span>
                    <span class="mt-4 text-2xl font-bold tabular-nums text-slate-900">{{ $counts[$key] }}</span>
                    <span class="text-xs text-slate-500">archived</span>
                </a>
            @endforeach
        </section>

        @if($type)
            <section class="inventory-content-frame overflow-hidden rounded-lg border border-slate-200 bg-white">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">{{ $modules[$type]['label'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $records->total() }} archived record{{ $records->total() === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($records->total() > 0)
                            <form method="POST" action="{{ route('archive.destroy-all', ['type' => $type]) }}" onsubmit="return confirm('Permanently delete all archived {{ strtolower($modules[$type]['label']) }} in this module? Protected records will remain archived. This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="record-action-danger rounded-md px-3 py-2 text-sm font-semibold">Permanently Delete All</button>
                            </form>
                        @endif
                        <a href="{{ route('archive.index') }}" class="text-sm font-semibold text-slate-600 underline underline-offset-2 hover:text-slate-900">All archive modules</a>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($records as $record)
                        <article class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                @if($type === 'expired-batches')
                                    <h3 class="font-semibold text-slate-900">{{ $record->product?->name ?? 'Product removed' }} · {{ $record->batch_reference ?? 'Batch' }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">Expiry: {{ $record->expiry_date?->format('M d, Y') ?? 'No expiry date' }}</p>
                                @elseif($type === 'pets')
                                    <h3 class="font-semibold text-slate-900">{{ $record->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">Customer: {{ $record->customer?->name ?? 'Customer removed' }}</p>
                                @elseif($type === 'grooming-prices')
                                    <h3 class="font-semibold text-slate-900">{{ $record->categoryLabel() }} grooming price</h3>
                                    <p class="mt-1 text-sm text-slate-500">₱{{ number_format((float) $record->price, 2) }}</p>
                                @elseif($type === 'reports')
                                    <h3 class="font-semibold text-slate-900">{{ $record->title }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ ucfirst($record->type) }} report · {{ $record->from_date?->format('M d, Y') ?? 'No start date' }} to {{ $record->to_date?->format('M d, Y') ?? 'No end date' }}</p>
                                @elseif($type === 'users')
                                    <h3 class="font-semibold text-slate-900">{{ $record->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $record->email }} · {{ ucfirst($record->role) }}</p>
                                @else
                                    <h3 class="font-semibold text-slate-900">{{ $record->name }}</h3>
                                    @if($type === 'products')
                                        <p class="mt-1 text-sm text-slate-500">SKU: {{ $record->sku }}</p>
                                    @elseif($type === 'customers')
                                        <p class="mt-1 text-sm text-slate-500">{{ $record->email ?: ($record->phone ?: 'No contact details') }}</p>
                                    @elseif($type === 'services')
                                        <p class="mt-1 text-sm text-slate-500">₱{{ number_format((float) $record->rate, 2) }} · {{ $record->size }}</p>
                                    @endif
                                @endif
                                <p class="mt-1 text-xs text-slate-400">Archived {{ $record->deleted_at?->format('M d, Y g:i A') }}</p>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                <form method="POST" action="{{ route('archive.restore', [$type, $record->id]) }}">
                                    @csrf
                                    <button type="submit" class="record-action-secondary rounded-md px-3 py-2 text-sm font-semibold">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('archive.destroy', ['type' => $type, 'id' => $record->id]) }}" onsubmit="return confirm('Permanently delete this record? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="record-action-danger rounded-md px-3 py-2 text-sm font-semibold">Permanently Delete</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="px-4 py-12 text-center text-sm text-slate-500">No archived {{ strtolower($modules[$type]['label']) }}.</p>
                    @endforelse
                </div>

                @if($records->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">{{ $records->links() }}</div>
                @endif
            </section>
        @endif
    </div>
</div>
@endsection