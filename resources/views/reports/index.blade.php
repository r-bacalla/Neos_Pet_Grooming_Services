@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div x-data="{
    selectedReport: null,
    isMaximized: false,
    openReport(report) {
        this.selectedReport = report;
        this.isMaximized = false;
    },
    closeReport() {
        this.selectedReport = null;
        this.isMaximized = false;
    },
    toggleMaximize() {
        this.isMaximized = !this.isMaximized;
    }
}" class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        <header class="mb-6 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:p-5">
            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Reports</h1>
            <p class="mt-1 max-w-2xl text-sm text-emerald-100/80">Review sales, products, grooming, and inventory performance from one overview page.</p>
        </header>

        @php
            $reportCards = [
                [
                    'name' => 'Sales Report',
                    'tag' => 'Revenue',
                    'icon' => 'sales',
                    'route' => route('reports.sales'),
                    'description' => 'Review transaction totals, payment mix, and completed sales for the selected period.',
                    'highlights' => ['Revenue summary', 'Payment methods', 'Daily sales trend']
                ],
                [
                    'name' => 'Product Report',
                    'tag' => 'Products',
                    'icon' => 'products',
                    'route' => route('reports.products'),
                    'description' => 'Track product units sold, top-selling items, and overall product performance.',
                    'highlights' => ['Best sellers', 'Unit volume', 'Product mix']
                ],
                [
                    'name' => 'Grooming Report',
                    'tag' => 'Grooming',
                    'icon' => 'grooming',
                    'route' => route('reports.grooming'),
                    'description' => 'Monitor grooming service revenue, top services, and appointment performance.',
                    'highlights' => ['Service revenue', 'Popular services', 'Visit summary']
                ],
                [
                    'name' => 'Inventory Report',
                    'tag' => 'Inventory',
                    'icon' => 'inventory',
                    'route' => route('reports.inventory'),
                    'description' => 'Follow stock movement, inventory activity, and quantity changes across products.',
                    'highlights' => ['Movement history', 'Stock activity', 'Inventory totals']
                ],
            ];
        @endphp

        <div class="inventory-content-frame mb-6 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Overview</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Choose a report</h2>
                </div>

                <form method="POST" action="{{ route('reports.generate-complete') }}" class="grid gap-2 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    @csrf
                    <label class="text-xs font-semibold text-slate-600">From
                        <input type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}" required class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                    </label>
                    <label class="text-xs font-semibold text-slate-600">To
                        <input type="date" name="to" value="{{ now()->toDateString() }}" required class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                    </label>
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Download all</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="inventory-content-frame grid grid-cols-1 gap-5 rounded-xl bg-emerald-950 p-2 md:grid-cols-2 xl:grid-cols-4">
            @foreach($reportCards as $report)
                <button type="button" @click="openReport({
                    name: @js($report['name']),
                    tag: @js($report['tag']),
                    route: @js($report['route']),
                    description: @js($report['description']),
                    highlights: @js($report['highlights'])
                })" class="group flex h-full min-h-[220px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition duration-200 hover:-translate-y-1 hover:border-slate-300 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">{{ $report['name'] }}</h2>
                    <p class="mt-2 flex-1 text-sm leading-6 text-slate-500">{{ $report['description'] }}</p>

                    <div class="mt-5 inline-flex items-center justify-between rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">
                        <span>Open</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6"/></svg>
                    </div>
                </button>
            @endforeach
        </div>

        <section class="inventory-content-frame mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="saved-reports-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="saved-reports-title" class="text-base font-bold text-slate-900">Saved Reports</h2>
                    <p class="mt-1 text-xs text-slate-500">Generated files, newest first.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('archive.index', ['type' => 'reports']) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Archived Reports</a>
                    @if($generatedReports->isNotEmpty())
                        <form method="POST" action="{{ route('reports.destroy-all') }}" onsubmit="return confirm('Move all saved reports to Archive? Their files will be kept and can be restored later.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Delete All</button>
                        </form>
                    @endif
                </div>
            </div>

            @forelse($generatedReports as $report)
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $report->title }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $report->from_date ? $report->from_date->format('M j, Y') . ' - ' . $report->to_date?->format('M j, Y') : 'All inventory movements' }}
                            &middot; Saved {{ $report->created_at->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('reports.download', $report) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                            {{ str_ends_with($report->file_path, '.pdf') ? 'Download PDF' : ($report->type === 'complete' ? 'Download archive' : 'Download CSV') }}
                        </a>
                        <form method="POST" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Move this saved report to Archive? Its file will be kept and can be restored later.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="record-action-danger rounded-lg px-3 py-2 text-xs font-semibold">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500 sm:px-6">No saved reports yet.</p>
            @endforelse

            @if($generatedReports->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    {{ $generatedReports->links() }}
                </div>
            @endif
        </section>

        <div x-show="selectedReport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/40" @click="closeReport()"></div>

            <div
                x-show="selectedReport"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :class="isMaximized ? 'w-[95vw] h-[88vh]' : 'w-full max-w-lg max-h-[80vh]'"
                class="relative z-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
            >
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400" x-text="selectedReport?.tag || 'Report'"></p>
                        <h3 class="text-base font-bold text-slate-900" x-text="selectedReport?.name || ''"></h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleMaximize()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" x-text="isMaximized ? 'Minimize' : 'Maximize'"></button>
                        <button type="button" @click="closeReport()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Close</button>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <p class="text-sm leading-6 text-slate-600" x-text="selectedReport?.description || ''"></p>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Included in this report</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700">
                            <template x-for="highlight in selectedReport?.highlights || []" :key="highlight">
                                <li class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
                                    <span x-text="highlight"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="closeReport()" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                        <a :href="selectedReport?.route || '#'" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Open report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection