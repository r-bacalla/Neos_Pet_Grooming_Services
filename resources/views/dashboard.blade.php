@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen w-full overflow-x-hidden bg-slate-50">
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-5 sm:px-6 lg:px-8">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 px-6 py-7 text-white shadow-xl sm:px-8">
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-slate-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Business dashboard
                    </div>

                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Welcome back, {{ auth()->user()->name }}
                    </h2>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                        Everything you need to manage Neo's Pet Grooming, in one place.
                    </p>
                </div>

                @if(auth()->user()->canOperate())
                    <a
                        href="{{ route('pos.index') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-slate-950 sm:w-auto"
                    >
                        Open POS
                        <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
        </section>

        @if(auth()->user()->isOwner())
            <nav class="flex justify-end" aria-label="Owner dashboard view">
                <div class="inline-flex rounded-lg border border-slate-300 bg-white p-1 shadow-sm" role="group" aria-label="Dashboard mode">
                    <a href="{{ route('dashboard', ['view' => 'overview']) }}" aria-current="{{ $dashboardView === 'overview' ? 'page' : 'false' }}" class="rounded-md px-3 py-2 text-sm font-semibold transition {{ $dashboardView === 'overview' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Overview</a>
                    <a href="{{ route('dashboard', ['view' => 'reports']) }}" aria-current="{{ $dashboardView === 'reports' ? 'page' : 'false' }}" class="rounded-md px-3 py-2 text-sm font-semibold transition {{ $dashboardView === 'reports' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Reports</a>
                </div>
            </nav>
        @endif

        @if(auth()->user()->isOwner() && $dashboardView === 'reports')
            <section aria-labelledby="owner-reports-heading">
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Business reports</p>
                    <h3 id="owner-reports-heading" class="mt-1 text-lg font-bold text-slate-900">Choose a report</h3>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('dashboard', ['view' => 'reports', 'report' => 'sales', 'from' => $from, 'to' => $to]) }}" aria-current="{{ $selectedReport === 'sales' ? 'true' : 'false' }}" class="group flex min-h-32 flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700 {{ $selectedReport === 'sales' ? 'border-emerald-700 bg-emerald-50' : 'border-slate-200 bg-white hover:border-emerald-300' }}">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Revenue</span>
                        <span class="mt-4 flex items-center justify-between gap-3 font-bold text-slate-900">Sales Report <span aria-hidden="true" class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-700">→</span></span>
                    </a>
                    <a href="{{ route('dashboard', ['view' => 'reports', 'report' => 'products', 'from' => $from, 'to' => $to]) }}" aria-current="{{ $selectedReport === 'products' ? 'true' : 'false' }}" class="group flex min-h-32 flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-700 {{ $selectedReport === 'products' ? 'border-sky-700 bg-sky-50' : 'border-slate-200 bg-white hover:border-sky-300' }}">
                        <span class="text-xs font-semibold uppercase tracking-wider text-sky-700">Products</span>
                        <span class="mt-4 flex items-center justify-between gap-3 font-bold text-slate-900">Product Report <span aria-hidden="true" class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-sky-700">→</span></span>
                    </a>
                    <a href="{{ route('dashboard', ['view' => 'reports', 'report' => 'grooming', 'from' => $from, 'to' => $to]) }}" aria-current="{{ $selectedReport === 'grooming' ? 'true' : 'false' }}" class="group flex min-h-32 flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-700 {{ $selectedReport === 'grooming' ? 'border-rose-700 bg-rose-50' : 'border-slate-200 bg-white hover:border-rose-300' }}">
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-700">Services</span>
                        <span class="mt-4 flex items-center justify-between gap-3 font-bold text-slate-900">Grooming Report <span aria-hidden="true" class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-rose-700">→</span></span>
                    </a>
                    <a href="{{ route('dashboard', ['view' => 'reports', 'report' => 'inventory', 'from' => $from, 'to' => $to]) }}" aria-current="{{ $selectedReport === 'inventory' ? 'true' : 'false' }}" class="group flex min-h-32 flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-700 {{ $selectedReport === 'inventory' ? 'border-amber-700 bg-amber-50' : 'border-slate-200 bg-white hover:border-amber-300' }}">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Stock</span>
                        <span class="mt-4 flex items-center justify-between gap-3 font-bold text-slate-900">Inventory Report <span aria-hidden="true" class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-amber-700">→</span></span>
                    </a>
                </div>
            </section>

            @if($selectedReport)
                @php
                    $reportTitles = [
                        'sales' => 'Sales Report',
                        'products' => 'Product Report',
                        'grooming' => 'Grooming Report',
                        'inventory' => 'Inventory Report',
                    ];
                    $currencyTotals = ['Subtotal', 'Discounts', 'VAT', 'Total sales', 'Sales value'];
                @endphp

                <section id="owner-report-results" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-labelledby="owner-report-title">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <h3 id="owner-report-title" class="font-bold text-slate-900">{{ $reportTitles[$selectedReport] }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $selectedReport === 'inventory' ? 'All recorded stock movements' : \Carbon\Carbon::parse($from)->format('M j, Y') . ' to ' . \Carbon\Carbon::parse($to)->format('M j, Y') }}
                            </p>
                        </div>
                        <a href="{{ route('reports.' . $selectedReport, $selectedReport === 'inventory' ? [] : ['from' => $from, 'to' => $to]) }}" class="text-sm font-semibold text-emerald-800 underline underline-offset-2 hover:text-emerald-950">Open full report</a>
                    </div>

                    @if($selectedReport !== 'inventory')
                        <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 border-b border-slate-100 bg-slate-50 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                            <input type="hidden" name="report" value="{{ $selectedReport }}">
                            <input type="hidden" name="view" value="reports">
                            <label class="text-xs font-semibold text-slate-600">From<input type="date" name="from" value="{{ $from }}" required class="mt-1 block min-h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900"></label>
                            <label class="text-xs font-semibold text-slate-600">To<input type="date" name="to" value="{{ $to }}" required class="mt-1 block min-h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900"></label>
                            <button type="submit" class="min-h-10 rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Update report</button>
                        </form>
                    @endif

                    <div class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 xl:grid-cols-5">
                        @foreach($reportTotals as $label => $value)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-slate-900">{{ in_array($label, $currencyTotals, true) ? '₱' : '' }}{{ in_array($label, $currencyTotals, true) ? number_format((float) $value, 2) : number_format((float) $value, 0) }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                @if($selectedReport === 'sales')
                                    <tr><th class="px-4 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Customer</th><th class="px-4 py-3 font-semibold">Payment</th><th class="px-4 py-3 text-right font-semibold">Subtotal</th><th class="px-4 py-3 text-right font-semibold">Discount</th><th class="px-4 py-3 text-right font-semibold">VAT</th><th class="px-4 py-3 text-right font-semibold">Total</th></tr>
                                @elseif(in_array($selectedReport, ['products', 'grooming'], true))
                                    <tr><th class="px-4 py-3 font-semibold">Item</th><th class="px-4 py-3 text-right font-semibold">Quantity</th><th class="px-4 py-3 text-right font-semibold">Total</th></tr>
                                @else
                                    <tr><th class="px-4 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Product</th><th class="px-4 py-3 font-semibold">Movement</th><th class="px-4 py-3 text-right font-semibold">Quantity</th><th class="px-4 py-3 font-semibold">Recorded by</th><th class="px-4 py-3 font-semibold">Notes</th></tr>
                                @endif
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportRows as $row)
                                    @if($selectedReport === 'sales')
                                        <tr><td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row->created_at->format('M j, Y g:i A') }}</td><td class="px-4 py-3 font-medium text-slate-900">{{ $row->customer?->name ?? 'Walk-in Customer' }}</td><td class="px-4 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $row->payment_method) }}</td><td class="px-4 py-3 text-right tabular-nums text-slate-600">₱{{ number_format((float) $row->subtotal, 2) }}</td><td class="px-4 py-3 text-right tabular-nums text-slate-600">₱{{ number_format((float) $row->discount, 2) }}</td><td class="px-4 py-3 text-right tabular-nums text-slate-600">₱{{ number_format((float) $row->vat_amount, 2) }}</td><td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">₱{{ number_format((float) $row->total, 2) }}</td></tr>
                                    @elseif(in_array($selectedReport, ['products', 'grooming'], true))
                                        <tr><td class="px-4 py-3 font-medium text-slate-900">{{ $row->item_name }}</td><td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ number_format((float) $row->quantity) }}</td><td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">₱{{ number_format((float) $row->total, 2) }}</td></tr>
                                    @else
                                        <tr><td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $row->created_at->format('M j, Y g:i A') }}</td><td class="px-4 py-3 font-medium text-slate-900">{{ $row->product?->name ?? 'Product removed' }}</td><td class="px-4 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $row->type) }}</td><td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ number_format((float) $row->quantity) }}</td><td class="px-4 py-3 text-slate-600">{{ $row->user?->name ?? 'System' }}</td><td class="px-4 py-3 text-slate-600">{{ $row->reason ?? '—' }}</td></tr>
                                    @endif
                                @empty
                                    <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">No report rows for this selection.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reportRows->hasPages())
                        <div class="border-t border-slate-200 px-4 py-3">{{ $reportRows->links() }}</div>
                    @endif
                </section>
            @endif
        @else
        {{-- QUICK ACTIONS --}}
        <section>
            <div class="mb-3 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Quick access</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">What do you want to do?</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @if(auth()->user()->canOperate())
                    <a href="{{ route('pos.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Point of Sale</p>
                                <p class="mt-0.5 font-bold text-slate-900">Start a sale</p>
                            </div>
                            <span class="ml-auto text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-900">→</span>
                        </div>
                    </a>

                    <a href="{{ route('products.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Products</p>
                                <p class="mt-0.5 font-bold text-slate-900">Browse products</p>
                            </div>
                            <span class="ml-auto text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-900">→</span>
                        </div>
                    </a>

                    @if(auth()->user()->canAccessCustomers())
                    <a href="{{ route('customers.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Customers</p>
                                <p class="mt-0.5 font-bold text-slate-900">Manage customers</p>
                            </div>
                            <span class="ml-auto text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-900">→</span>
                        </div>
                    </a>
                    @endif

                    <a href="{{ route('grooming.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Grooming</p>
                                <p class="mt-0.5 font-bold text-slate-900">Manage appointments</p>
                            </div>
                            <span class="ml-auto text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-900">→</span>
                        </div>
                    </a>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOwner())
                    <a href="{{ route('reports.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Reports</p>
                                <p class="mt-0.5 font-bold text-slate-900">View business reports</p>
                            </div>
                            <span class="ml-auto text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-900">→</span>
                        </div>
                    </a>
                @endif
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- MODULES --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div>
                        <h3 class="font-bold text-slate-900">Business modules</h3>
                        <p class="mt-1 text-xs text-slate-500">Manage the core areas of your business.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                        {{ auth()->user()->role }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
                    @if(auth()->user()->canOperate())
                        <a href="{{ route('products.index') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-slate-900">Products</div>
                                <div class="mt-0.5 text-xs text-slate-500">Products & pricing</div>
                            </div>
                            <span class="ml-auto text-slate-300 group-hover:text-slate-900">→</span>
                        </a>

                        @if(auth()->user()->canManageInventory())
                        <a href="{{ route('inventory.index') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-slate-900">Inventory</div>
                                <div class="mt-0.5 text-xs text-slate-500">Stock management</div>
                            </div>
                            <span class="ml-auto text-slate-300 group-hover:text-slate-900">→</span>
                        </a>

                        <a href="{{ route('services.index') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-slate-900">Services</div>
                                <div class="mt-0.5 text-xs text-slate-500">Service offerings</div>
                            </div>
                            <span class="ml-auto text-slate-300 group-hover:text-slate-900">→</span>
                        </a>
                        @endif
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                        <a href="{{ route('settings.edit') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-slate-200 hover:bg-white hover:shadow-sm">
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-slate-900">Settings</div>
                                <div class="mt-0.5 text-xs text-slate-500">System settings</div>
                            </div>
                            <span class="ml-auto text-slate-300 group-hover:text-slate-900">→</span>
                        </a>
                    @endif
                </div>
            </section>

            {{-- ACCOUNT --}}
            <aside class="overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-lg font-bold text-slate-950 shadow-lg">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Signed in as</p>
                        <h3 class="mt-1 truncate font-bold">{{ auth()->user()->name }}</h3>
                        <p class="mt-0.5 truncate text-sm capitalize text-slate-400">{{ auth()->user()->role }}</p>
                    </div>
                </div>

                <div class="my-6 h-px bg-white/10"></div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Workspace</p>
                        <p class="mt-1 text-sm leading-6 text-slate-300">
                            You're signed in to Neo's Pet Grooming.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center gap-2 text-sm font-semibold">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Access active
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-400">
                            Your dashboard shows the modules available to your account.
                        </p>
                    </div>
                </div>
            </aside>
        </div>

        <section class="overflow-hidden rounded-2xl border border-amber-200 bg-amber-50" aria-labelledby="expiring-batches-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-200 px-5 py-4">
                <div>
                    <h3 id="expiring-batches-heading" class="font-bold text-amber-950">Products Near Expiry</h3>
                    <p class="mt-1 text-sm text-amber-800">In-stock batches expiring within {{ App\Models\Setting::expiryWarningDays() }} days.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">{{ $expiringBatches->count() }} {{ Str::plural('batch', $expiringBatches->count()) }}</span>
            </div>
            <div class="divide-y divide-amber-200">
                @forelse($expiringBatches as $batch)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $batch->product->name }}</p>
                            <p class="mt-0.5 text-xs text-slate-600">Batch {{ $batch->batch_reference ?? $batch->product->sku }} · Expires {{ $batch->expiry_date->format('M d, Y') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-amber-900">{{ $batch->inventory_quantity }} inventory · {{ $batch->available_quantity }} products</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-600">No batches are nearing expiry.</p>
                @endforelse
            </div>
        </section>
        @endif
    </div>
</div>
@endsection