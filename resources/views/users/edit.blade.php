<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900">Edit account</h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="mb-6 text-sm text-slate-500">Update the saved account details, role, or password.</p>
            @include('users.form', ['formAction' => route('users.update', $user), 'formMethod' => 'PUT', 'user' => $user])
        </div>
    </div>
</x-app-layout>
