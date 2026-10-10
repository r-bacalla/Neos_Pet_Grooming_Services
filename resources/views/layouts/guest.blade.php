<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Neo's Pet Grooming</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="guest-body bg-gray-50 text-gray-900">

    <nav
        x-data="{
            open: false,
            salesInventory: false,
            serviceManagement: false,
            userMenu: false
        }"
        class="guest-nav bg-white border-b border-gray-200"
    >

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between h-16">

                {{-- LOGO --}}
                <div class="flex items-center">

                    <a
                        href="{{ auth()->check() ? route(auth()->user()->landingRoute()) : route('login') }}"
                        class="flex items-center gap-3"
                    >
                        <x-company-logo class="h-12 w-24 object-contain" />

                        <div class="hidden lg:block">
                            <div class="text-sm font-bold text-gray-900">
                                Neo's Pet Grooming
                            </div>

                            <div class="text-xs text-gray-500">
                                Management System
                            </div>
                        </div>
                    </a>

                </div>

                @auth
                {{-- DESKTOP NAVIGATION --}}
                <div class="hidden md:flex items-center gap-1">

                    {{-- DASHBOARD --}}
                    <a
                        href="{{ route('dashboard') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('dashboard')
                            ? 'bg-gray-100 text-gray-900'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        Dashboard
                    </a>

                    {{-- SALES & INVENTORY --}}
                    <div
                        class="relative"
                        @click.outside="salesInventory = false"
                    >

                        <button
                            type="button"
                            @click="
                                salesInventory = !salesInventory;
                                serviceManagement = false;
                                userMenu = false;
                            "
                            class="flex items-center gap-2 px-3 py-2 rounded-lg
                                   text-sm font-medium text-gray-600
                                   hover:bg-gray-100 hover:text-gray-900 transition"
                        >
                            Sales & Inventory

                        </button>
                                POS
                            </a>

                            <a
                                href="{{ route('products.index') }}"
                                class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Products
                            </a>

                            <a
                                href="{{ route('inventory.index') }}"
                                class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Inventory
                            </a>

                        </div>
                    </div>

                    {{-- SERVICE MANAGEMENT --}}
                    <div
                        class="relative"
                        @click.outside="serviceManagement = false"
                    >

                        <button
                            type="button"
                            @click="
                                serviceManagement = !serviceManagement;
                                salesInventory = false;
                                userMenu = false;
                            "
                            class="flex items-center gap-2 px-3 py-2 rounded-lg
                                   text-sm font-medium text-gray-600
                                   hover:bg-gray-100 hover:text-gray-900 transition"
                        >
                            Service Management

                            <svg
                                class="w-4 h-4 transition-transform"
                                :class="{ 'rotate-180': serviceManagement }"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 1.04l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div
                            x-show="serviceManagement"
                            x-cloak
                            x-transition
                            class="absolute left-0 z-50 mt-2 w-52 rounded-xl bg-white p-2 shadow-xl ring-1 ring-gray-200"
                        >

                            <a
                                href="{{ route('grooming.index') }}"
                                class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Grooming
                            </a>

                            <a
                                href="{{ route('services.index') }}"
                                class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Services
                            </a>

                        </div>
                    </div>

                    {{-- CUSTOMERS --}}
                    <a
                        href="{{ route('customers.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('customers.*')
                            ? 'bg-gray-100 text-gray-900'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        Customers
                    </a>

                    {{-- REPORTS --}}
                    <a
                        href="{{ route('reports.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('reports.*')
                            ? 'bg-gray-100 text-gray-900'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        Reports
                    </a>

                    {{-- SETTINGS --}}
                    <a
                        href="{{ route('settings.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('settings.*')
                            ? 'bg-gray-100 text-gray-900'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        Settings
                    </a>

                    @if(in_array(Auth::user()->role, ['admin', 'manager', 'frontdesk']))
                        <a
                            href="{{ route('users.index') }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs('users.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                        >
                            Accounts
                        </a>
                    @endif

                </div>

                {{-- USER MENU --}}
                <div
                    class="hidden md:block relative"
                    @click.outside="userMenu = false"
                >

                    <button
                        type="button"
                        @click="
                            userMenu = !userMenu;
                            salesInventory = false;
                            serviceManagement = false;
                        "
                        class="flex items-center gap-2 px-3 py-2 rounded-lg
                               text-sm font-medium text-gray-600
                               hover:bg-gray-100 hover:text-gray-900 transition"
                    >

                        <span
                            class="flex items-center justify-center w-8 h-8
                                   rounded-full bg-gray-900 text-white text-sm font-semibold"
                        >
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>

                        <span class="max-w-32 truncate">
                            {{ Auth::user()->name }}
                        </span>


                    </button>

                    <div
                        x-show="userMenu"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-56 rounded-xl
                               bg-white p-2 shadow-xl ring-1 ring-gray-200"
                    >

                        <div class="px-3 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ Auth::user()->name }}
                            </p>

                            <p class="text-xs text-gray-500 truncate">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        @if (Route::has('profile.edit'))
                            <a
                                href="{{ route('profile.edit') }}"
                                class="block rounded-lg px-3 py-2.5 mt-1 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Profile
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="w-full text-left rounded-lg px-3 py-2.5
                                       text-sm text-red-600 hover:bg-red-50"
                            >
                                Log Out
                            </button>
                        </form>

                    </div>

                </div>

                {{-- MOBILE BUTTON --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                >

                    <span x-show="!open">Menu</span>
                    <span x-show="open" x-cloak>Close</span>

                </button>

            </div>
        </div>

        {{-- MOBILE MENU --}}
        <div
            x-show="open"
            x-cloak
            x-transition
            class="md:hidden border-t border-gray-100 py-3"
        >

            <div class="space-y-1">

                <a
                    href="{{ route('dashboard') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm font-medium
                    {{ request()->routeIs('dashboard')
                        ? 'bg-gray-100 text-gray-900'
                        : 'text-gray-600 hover:bg-gray-100' }}"
                >
                    Dashboard
                </a>

                <div class="px-3 pt-3 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Sales & Inventory
                    </p>
                </div>

                <a
                    href="{{ route('pos.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    POS
                </a>

                <a
                    href="{{ route('products.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Products
                </a>

                <a
                    href="{{ route('inventory.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Inventory
                </a>

                <div class="px-3 pt-3 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Service Management
                    </p>
                </div>

                <a
                    href="{{ route('grooming.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Grooming
                </a>

                <a
                    href="{{ route('services.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Services
                </a>

                <a
                    href="{{ route('customers.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Customers
                </a>

                <a
                    href="{{ route('reports.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Reports
                </a>

                <a
                    href="{{ route('settings.index') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                >
                    Settings
                </a>

                @if(in_array(Auth::user()->role, ['admin', 'manager', 'frontdesk']))
                    <a
                        href="{{ route('users.index') }}"
                        class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                    >
                        Accounts
                    </a>
                @endif

            </div>

            {{-- MOBILE ACCOUNT --}}
            <div class="mt-4 pt-4 border-t border-gray-200">

                <div class="flex items-center gap-3 px-3 mb-3">

                    <span
                        class="flex items-center justify-center w-10 h-10
                               rounded-full bg-gray-900 text-white font-semibold"
                    >
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>

                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-gray-900 truncate">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-xs text-gray-500 truncate">
                            {{ Auth::user()->email }}
                        </p>
                    </div>

                </div>

                @if (Route::has('profile.edit'))
                    <a
                        href="{{ route('profile.edit') }}"
                        class="block px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100"
                    >
                        Profile
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="w-full text-left px-3 py-2.5 rounded-lg
                               text-sm text-red-600 hover:bg-red-50"
                    >
                        Log Out
                    </button>
                </form>

            </div>

        </div>

        @else
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('login') }}"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"
                >
                    Log in
                </a>

                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium bg-gray-900 text-white hover:bg-gray-700 transition"
                    >
                        Register
                    </a>
                @endif
            </div>
        @endauth

    </nav>

    <main class="guest-stage">
        <div class="guest-layout">
            @guest
                <aside class="guest-brand-panel">
                    <a href="{{ route('login') }}" class="guest-brand-lockup" aria-label="Neo's Pet Grooming home">
                        <x-company-logo class="h-14 w-28 object-contain" />
                        <span>
                            <strong>Neo's Pet Grooming</strong>
                            <small>CARE, GROOMING &amp; SERVICE</small>
                        </span>
                    </a>

                    <div class="guest-brand-copy">
                        <p class="guest-kicker">WELCOME TO NEO'S</p>
                        <h1>Good care starts here.</h1>
                        <p>Sign in to manage appointments, grooming services, and every detail of your pet's visit.</p>
                    </div>

                    <div class="guest-brand-footer">
                        <span>Thoughtful care for every companion.</span>
                    </div>
                </aside>
            @endguest

            <section class="guest-form-panel">
                {{ $slot }}
            </section>
        </div>
    </main>

</body>
</html>