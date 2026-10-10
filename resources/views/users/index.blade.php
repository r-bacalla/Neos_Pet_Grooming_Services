<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Administration</p>
                <h2 class="text-xl font-bold text-slate-900">Accounts</h2>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Create account</a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-6xl">
            @if(session('success'))
                <div class="mb-5 border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                    <h1 class="text-lg font-bold text-slate-900">System accounts</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage saved account details and access roles.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <p class="break-words font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="break-all text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">{{ $user->role }}</span>
                                <a href="{{ route('users.edit', $user) }}" class="record-action-secondary rounded-lg px-3 py-2 text-xs font-semibold">Edit</a>
                                @if(auth()->id() !== $user->id)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Move this account to Archive? The account can be restored later.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="record-action-danger rounded-lg px-3 py-2 text-xs font-semibold">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500">No accounts found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
