<form method="POST" action="{{ $formAction }}" class="space-y-5">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="role" class="block text-sm font-semibold text-slate-700">Account role</label>
        <select id="role" name="role" required class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm capitalize shadow-sm focus:border-slate-500 focus:ring-slate-200">
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->role ?? 'customer') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700">{{ $user ? 'New password' : 'Password' }}</label>
            <input id="password" name="password" type="password" {{ $user ? '' : 'required' }} autocomplete="new-password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }} autocomplete="new-password" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-200">
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
        <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Save account</button>
    </div>
</form>
