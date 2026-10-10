<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Neo's Pet Grooming</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Create your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Customer accounts are saved securely and can be used to keep your details connected to pet services.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700">Full name</label>
            <input id="name" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
            <input id="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="password" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
                <input id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
        </div>

        <p class="border-l-4 border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">New public registrations are saved as customer accounts. Staff roles are assigned by an administrator or owner.</p>
        <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Create account</button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">Already registered? <a href="{{ route('login') }}" class="font-semibold text-slate-900 underline underline-offset-4">Log in</a></p>
</x-guest-layout>
