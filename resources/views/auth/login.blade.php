<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Neo's Pet Grooming</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Welcome back</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to manage sales, grooming, inventory, and reports.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
            <input id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-slate-600 underline underline-offset-4 hover:text-slate-900" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <input id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex items-center gap-2 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" name="remember">
            Remember this device
        </label>

        <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Log in</button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">Need an account? <a href="{{ route('register') }}" class="font-semibold text-slate-900 underline underline-offset-4">Create a customer account</a></p>
</x-guest-layout>
