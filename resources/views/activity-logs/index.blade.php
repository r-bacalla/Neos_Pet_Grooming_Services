@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <header class="rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:p-5">
            <h1 class="text-2xl font-bold tracking-tight text-white">Activity Logs</h1>
            <p class="mt-1 text-sm text-emerald-100/80">{{ auth()->user()->isFrontDesk() ? 'POS and grooming activity, newest first.' : 'Sign-ins, product changes, and stock activity, newest first.' }}</p>
        </header>

        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid gap-4 lg:grid-cols-2">
            <div class="inventory-content-frame rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <label for="activity-search" class="mb-1.5 block text-xs font-semibold text-slate-600">Search actor or record</label>
                <input id="activity-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, product, or details" class="w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-3 py-3 text-sm font-medium text-slate-900 transition placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                <div class="mt-3">
                    <label for="activity-action" class="mb-1.5 block text-xs font-semibold text-slate-600">Action</label>
                    <select id="activity-action" name="action" class="w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-3 py-3 text-sm font-medium text-slate-900 transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        <option value="">All actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="inventory-content-frame grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="activity-from" class="mb-1.5 block text-xs font-semibold text-slate-600">From</label>
                        <input id="activity-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="min-h-12 min-w-0 w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-3 py-3 text-sm font-medium text-slate-900 transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="activity-to" class="mb-1.5 block text-xs font-semibold text-slate-600">To</label>
                        <input id="activity-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="min-h-12 min-w-0 w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-3 py-3 text-sm font-medium text-slate-900 transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="submit" class="inline-flex min-h-12 min-w-20 items-center justify-center rounded-xl bg-emerald-800 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-100">Filter</button>
                    <a href="{{ route('activity-logs.index') }}" class="inline-flex min-h-12 min-w-20 items-center justify-center rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-100">Clear</a>
                </div>
            </div>
        </form>

        <section aria-label="Activity log entries" class="inventory-content-frame overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">Recorded activity</h2>
                <p class="text-xs text-slate-500">{{ $logs->total() }} {{ Str::plural('event', $logs->total()) }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-3">Date and time</th>
                            <th class="px-5 py-3">Performed by</th>
                            <th class="px-5 py-3">Activity</th>
                            <th class="px-5 py-3">Record</th>
                            <th class="px-5 py-3">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $log->created_at->format('M j, Y g:i:s A') }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $log->user?->name ?? 'System / deleted user' }}</p>
                                    @if($log->ip_address)<p class="mt-1 text-xs text-slate-500">{{ $log->ip_address }}</p>@endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold capitalize text-slate-900">{{ str($log->action)->replace('_', ' ') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $log->module ?: 'General' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-800">{{ $log->record_type ?: 'Session' }}{{ $log->record_id ? ' #' . $log->record_id : '' }}</p>
                                    @if($log->description)<p class="mt-1 max-w-md text-xs leading-5 text-slate-500">{{ $log->description }}</p>@endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($log->old_values || $log->new_values)
                                        @php
                                            $oldValues = $log->old_values ?? [];
                                            $newValues = $log->new_values ?? [];
                                            $changedFields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                                            $formatAuditValue = function ($value) use (&$formatAuditValue) {
                                                if (is_bool($value)) {
                                                    return $value ? 'Yes' : 'No';
                                                }

                                                if ($value === null || $value === '') {
                                                    return '—';
                                                }

                                                if (is_array($value)) {
                                                    return collect($value)
                                                        ->map(fn ($item, $key) => (is_string($key) ? str($key)->replace('_', ' ')->title() . ': ' : '') . $formatAuditValue($item))
                                                        ->implode(', ');
                                                }

                                                return is_scalar($value) ? (string) $value : '—';
                                            };
                                        @endphp
                                        <details class="max-w-sm">
                                            <summary class="cursor-pointer text-xs font-semibold text-emerald-800">View changes</summary>
                                            <div class="mt-2 grid gap-2 text-xs">
                                                @foreach($changedFields as $field)
                                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                                        <p class="font-semibold text-slate-700">{{ str($field)->replace('_', ' ')->title() }}</p>
                                                        <div class="mt-1 grid grid-cols-[1fr_auto_1fr] items-start gap-2">
                                                            <div>
                                                                <p class="text-[10px] font-semibold uppercase text-slate-400">Before</p>
                                                                <p class="break-words text-slate-600">{{ array_key_exists($field, $oldValues) ? $formatAuditValue($oldValues[$field]) : 'Not set' }}</p>
                                                            </div>
                                                            <span class="pt-3 text-slate-400">&rarr;</span>
                                                            <div>
                                                                <p class="text-[10px] font-semibold uppercase text-slate-400">After</p>
                                                                <p class="break-words text-slate-800">{{ array_key_exists($field, $newValues) ? $formatAuditValue($newValues[$field]) : 'Not set' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No activity matches these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="border-t border-slate-200 px-5 py-3">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection