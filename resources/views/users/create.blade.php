<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900">Create account</h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="mb-6 text-sm text-slate-500">Save an account with the role that matches the person's responsibilities.</p>
            @include('users.form', ['formAction' => route('users.store'), 'formMethod' => 'POST', 'user' => null])
        </div>
    </div>
</x-app-layout>
