<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', config('app.name', "Neo's Pet Grooming"))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @stack('styles')

    <style>
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Keep dense data views inside the content column on small screens. */
        .table-responsive {
            max-width: 100%;
            overflow-x: hidden;
        }

        .table-responsive table,
        main table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
        }

        .table-responsive th,
        .table-responsive td,
        main th,
        main td {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* Smooth UI transitions */
        .nav-item {
            transition:
                background-color 0.15s ease,
                color 0.15s ease,
                transform 0.15s ease;
        }

        .nav-item:hover {
            transform: translateX(2px);
        }

        /* Better scrollbar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 999px;
        }

        /* Print */
        @media print {
            aside,
            header,
            .no-print {
                display: none !important;
            }

            main {
                padding: 0 !important;
            }
        }
    </style>

</head>


<body class="app-body bg-slate-100 text-slate-800 antialiased">

<div
    x-data="{ sidebarOpen: false }"
    class="min-h-screen"
>


    {{-- MOBILE OVERLAY --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden"
    ></div>


    {{-- SIDEBAR --}}
    <aside
        class="app-sidebar
        
            fixed inset-y-0 left-0 z-50
            w-72
            bg-slate-950
            text-white
            flex flex-col
            transform transition-transform duration-200
            -translate-x-full
            md:translate-x-0
        "
        :class="{ 'translate-x-0': sidebarOpen }"
    >

        {{-- BRAND --}}
        <div class="px-5 py-5 border-b border-slate-800">

            <div class="grid grid-cols-[1fr_auto_1fr] items-center">

                <a
                    href="{{ route(auth()->user()->landingRoute()) }}"
                    class="sidebar-logo-plate col-start-2 inline-flex h-[4.75rem] w-40 items-center justify-center rounded-xl bg-white px-2 py-1.5 shadow-sm"
                >
                    <img src="{{ asset('neos-pet-grooming-logo-transparent.png') }}" alt="Neo's Pet Grooming" class="h-14 w-32 object-contain">
                </a>


                {{-- MOBILE CLOSE --}}
                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="
                        md:hidden
                        col-start-3 justify-self-end
                        px-2 py-1
                        rounded-lg
                        text-slate-400
                        hover:text-white
                        hover:bg-slate-800
                        text-xs font-semibold
                    "
                >
                    Close
                </button>

            </div>

        </div>


        {{-- NAVIGATION --}}
        <nav
            x-ref="sidebarNavigation"
            x-init="$nextTick(() => { $el.scrollTop = Number(sessionStorage.getItem('app-sidebar-scroll') || 0); })"
            @click.capture="if ($event.target.closest('a')) sessionStorage.setItem('app-sidebar-scroll', String($el.scrollTop))"
            class="flex-1 overflow-y-auto sidebar-scroll px-3 py-5"
        >


            {{-- MAIN --}}
            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                Main
            </div>


            @if(in_array(auth()->user()->role, ['admin', 'manager', 'groomer', 'owner', 'frontdesk']))
                {{-- DASHBOARD --}}
                <a
                    href="{{ route('dashboard') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('dashboard')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span>Dashboard</span>
                </a>
            @endif


            {{-- REQUESTED NAVIGATION --}}
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'frontdesk']))
                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Sales{{ auth()->user()->isFrontDesk() ? ' & Products' : ' & Inventory' }}
                </div>

                @foreach([
                    ['pos.index', 'pos.*', 'pos', 'POS'],
                    ['products.index', 'products.*', 'products', 'Products'],
                ] as [$route, $active, $icon, $label])
                    <a href="{{ route($route) }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs($active) ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>{{ $label }}</span>
                    </a>
                @endforeach

                @if(auth()->user()->canManageInventory())
                    <a href="{{ route('inventory.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('inventory.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Inventory</span>
                    </a>
                @endif

                @if(auth()->user()->canManageInventory())
                    <a href="{{ route('categories.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('categories.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Categories</span>
                    </a>
                @endif

                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Service Management
                </div>

                @foreach([
                    ['grooming.index', 'grooming.*', 'grooming', 'Grooming'],
                ] as [$route, $active, $icon, $label])
                    <a href="{{ route($route) }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs($active) ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>{{ $label }}</span>
                    </a>
                @endforeach

                @if(auth()->user()->canManageInventory())
                    <a href="{{ route('services.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('services.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Services</span>
                    </a>
                @endif

                @if(auth()->user()->canAccessCustomers())
                    <a href="{{ route('customers.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('customers.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Customers</span>
                    </a>
                @endif
            @endif

            @if(auth()->user()->isCustomer())
                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Customer Access
                </div>

                <a href="{{ route('pos.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('pos.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>POS</span>
                </a>

                <a href="{{ route('grooming.create') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('grooming.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>Grooming</span>
                </a>
            @endif

            <div class="hidden">
            {{-- LEGACY OPERATIONS LINKS --}}
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'frontdesk']))

                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Operations
                </div>


                {{-- POS --}}
                <a
                    href="{{ route('pos.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('pos.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>POS</span>

                    @if(request()->routeIs('pos.*'))
                        <span class="ml-auto w-2 h-2 rounded-full bg-slate-900"></span>
                    @endif
                </a>


                {{-- CUSTOMERS --}}
                <a
                    href="{{ route('customers.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('customers.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>Customers</span>
                </a>


                {{-- GROOMING --}}
                <a
                    href="{{ route('grooming.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('grooming.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>Grooming</span>
                </a>


                {{-- PRODUCTS --}}
                <a
                    href="{{ route('products.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('products.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>Products</span>
                </a>


                {{-- SERVICES --}}
                <a
                    href="{{ route('services.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('services.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>Services</span>
                </a>


                {{-- INVENTORY --}}
                <a
                    href="{{ route('inventory.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('inventory.*')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span class="hidden"></span>
                    <span>Inventory</span>
                </a>

            @endif
            </div>


            {{-- MANAGEMENT --}}
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'owner']))

                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Management
                </div>

                <a href="{{ route('archive.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('archive.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span>Archive</span>
                </a>

                {{-- SETTINGS --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOwner())
                    <a
                        href="{{ route('settings.edit') }}"
                        @click="sidebarOpen = false"
                        class="
                            nav-item
                            flex items-center gap-3
                            px-3.5 py-3
                            mb-1
                            rounded-xl
                            text-sm font-medium
                            {{ request()->routeIs('settings.*')
                                ? 'bg-white text-slate-900 shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        "
                    >
                        <span>Settings</span>
                    </a>
                @endif

            @endif


            {{-- REPORTS --}}
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'owner', 'frontdesk']))

                <div class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Reports
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOwner())
                    <a href="{{ route('activity-logs.index') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('activity-logs.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span>Activity Logs</span>
                    </a>
                @endif

                @if(auth()->user()->isFrontDesk())
                    <a href="{{ route('reports.sales') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('reports.sales') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><span>POS Sales Report</span></a>
                    <a href="{{ route('reports.grooming') }}" @click="sidebarOpen = false" class="nav-item flex items-center gap-3 px-3.5 py-3 mb-1 rounded-xl text-sm font-medium {{ request()->routeIs('reports.grooming') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><span>Grooming Report</span></a>
                @else
                {{-- REPORT OVERVIEW --}}
                <a
                    href="{{ route('reports.index') }}"
                    @click="sidebarOpen = false"
                    class="
                        nav-item
                        flex items-center gap-3
                        px-3.5 py-3
                        mb-1
                        rounded-xl
                        text-sm font-medium
                        {{ request()->routeIs('reports.index')
                            ? 'bg-white text-slate-900 shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    "
                >
                    <span>Reports Overview</span>

                    @if(request()->routeIs('reports.index'))
                        <span class="ml-auto w-2 h-2 rounded-full bg-slate-900"></span>
                    @endif
                </a>
                @endif


            @endif

        </nav>


        {{-- USER AREA --}}
        <div class="border-t border-slate-800 p-4">

            <div class="flex items-center gap-3 mb-3">

                <div
                    class="
                        w-10 h-10
                        rounded-full
                        bg-slate-800
                        flex items-center justify-center
                        font-bold
                        text-sm
                    "
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">

                    <div class="text-sm font-semibold truncate">
                        {{ auth()->user()->name }}
                    </div>

                    <div class="text-xs text-slate-400 capitalize">
                        {{ auth()->user()->role }}
                    </div>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf

                <button
                    type="submit"
                    class="
                        w-full
                        flex items-center justify-center gap-2
                        px-3 py-2.5
                        rounded-xl
                        bg-slate-800
                        hover:bg-red-900/40
                        hover:text-red-300
                        text-sm
                        font-medium
                        transition
                    "
                >
                    <span>Logout</span>
                </button>

            </form>

        </div>

    </aside>


    {{-- MAIN AREA --}}
    <div class="md:ml-72 min-w-0 min-h-screen flex flex-col">


        {{-- TOP BAR --}}
        <header
            class="
                sticky top-0 z-30
                bg-white/95
                backdrop-blur
                border-b border-slate-200
            "
        >

            <div
                class="
                    h-16
                    px-4 sm:px-6
                    flex items-center justify-between
                "
            >

                <div class="flex items-center gap-3">

                    {{-- MOBILE MENU --}}
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="
                            md:hidden
                            w-10 h-10
                            rounded-xl
                            border border-slate-200
                            bg-white
                            hover:bg-slate-50
                            flex items-center justify-center
                            text-lg
                        "
                    >
                        Menu
                    </button>


                    <div>

                        <h1 class="text-lg sm:text-xl font-bold text-slate-900">
                            @yield('title', 'Dashboard')
                        </h1>

                        <p class="hidden sm:block text-xs text-slate-500 mt-0.5">
                            {{ now()->format('l, F d, Y') }}
                        </p>

                    </div>

                </div>


                {{-- USER --}}
                <div class="flex items-center gap-3">

                    <div class="hidden sm:block text-right">

                        <div class="text-sm font-semibold text-slate-800">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="text-xs text-slate-500 capitalize">
                            {{ auth()->user()->role }}
                        </div>

                    </div>

                    <div
                        class="
                            w-10 h-10
                            rounded-full
                            bg-slate-900
                            text-white
                            flex items-center justify-center
                            font-bold
                        "
                    >
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                </div>

            </div>

        </header>


        {{-- PAGE CONTENT --}}
        <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">


            {{-- ERROR MESSAGE --}}
            @if(session('error'))

                <div
                    class="
                        mb-5
                        flex items-start gap-3
                        rounded-xl
                        border border-red-200
                        bg-red-50
                        px-4 py-3
                        text-red-800
                        shadow-sm
                    "
                >

                    <span class="text-lg">
                        !
                    </span>

                    <div class="text-sm font-medium">
                        {{ session('error') }}
                    </div>

                </div>

            @endif


            {{-- VALIDATION ERRORS --}}
            @if($errors->any())

                <div
                    class="
                        mb-5
                        rounded-xl
                        border border-red-200
                        bg-red-50
                        px-4 py-4
                        text-red-800
                        shadow-sm
                    "
                >

                    <div class="font-semibold text-sm mb-2">
                        Please correct the following:
                    </div>

                    <ul class="list-disc ml-5 space-y-1 text-sm">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- PAGE CONTENT --}}
            @yield('content')

        </main>

    </div>

</div>


<div x-data="appFeedback(@js(session('success')))" x-init="init()" @keydown.escape.window="cancelConfirmation()">
    <div x-show="successOpen" x-cloak x-transition.opacity role="status" aria-live="polite" class="fixed right-4 top-4 z-[100] w-[min(calc(100vw-2rem),26rem)] rounded-xl border border-emerald-200 bg-white p-4 shadow-xl sm:right-6 sm:top-6">
        <div class="flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-800">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-900">Success</p>
                <p class="mt-1 text-sm leading-5 text-slate-600" x-text="successMessage"></p>
            </div>
            <button type="button" @click="successOpen = false" aria-label="Dismiss success message" class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div x-show="confirmOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="presentation" @click.self="cancelConfirmation()">
        <section role="alertdialog" aria-modal="true" aria-labelledby="global-confirm-title" aria-describedby="global-confirm-message" class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start gap-3 px-5 py-5 sm:px-6">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3l-8.48-14.14a2 2 0 0 0-3.4 0Z"/></svg>
                </span>
                <div>
                    <h2 id="global-confirm-title" class="text-lg font-bold text-slate-900">Confirm this action</h2>
                    <p id="global-confirm-message" class="mt-1 text-sm leading-6 text-slate-600" x-text="confirmMessage"></p>
                </div>
            </div>
            <footer class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-6">
                <button type="button" @click="cancelConfirmation()" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Keep</button>
                <button type="button" @click="confirmAction()" class="min-h-10 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800" x-text="confirmButtonLabel"></button>
            </footer>
        </section>
    </div>
</div>

<script>
    function appFeedback(initialSuccessMessage) {
        return {
            successMessage: initialSuccessMessage || '',
            successOpen: Boolean(initialSuccessMessage),
            confirmOpen: false,
            confirmMessage: '',
            pendingForm: null,
            pendingSubmitter: null,
            get confirmButtonLabel() {
                const message = this.confirmMessage.toLowerCase();

                if (message.includes('permanently')) return 'Delete Permanently';
                if (message.includes('write off')) return 'Write Off Stock';
                if (message.includes('cancel')) return 'Cancel Visit';
                if (message.includes('archive') || message.includes('move') || message.includes('hide')) return 'Continue';

                return 'Confirm';
            },
            init() {
                if (this.successOpen) {
                    this.$nextTick(() => {
                        const message = this.successMessage.replace(/\s+/g, ' ').trim();
                        document.querySelectorAll('main .bg-emerald-50').forEach((notice) => {
                            if (notice.textContent.replace(/\s+/g, ' ').trim() === message) notice.remove();
                        });
                    });
                    window.setTimeout(() => this.successOpen = false, 6500);
                }

                document.addEventListener('submit', (event) => {
                    const form = event.target;
                    const handler = form instanceof HTMLFormElement ? form.getAttribute('onsubmit') : null;
                    const confirmation = handler?.match(/confirm\((['"])([\s\S]*?)\1\)/);

                    if (!form || !confirmation) return;

                    event.preventDefault();
                    event.stopImmediatePropagation();
                    this.pendingForm = form;
                    this.pendingSubmitter = event.submitter;
                    this.confirmMessage = confirmation[2];
                    this.confirmOpen = true;
                }, true);
            },
            cancelConfirmation() {
                this.confirmOpen = false;
                this.pendingForm = null;
                this.pendingSubmitter = null;
            },
            confirmAction() {
                const form = this.pendingForm;
                const submitter = this.pendingSubmitter;

                if (!form) return;

                const handler = form.getAttribute('onsubmit');
                form.removeAttribute('onsubmit');
                this.cancelConfirmation();

                if (submitter) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }

                if (handler) form.setAttribute('onsubmit', handler);
            }
        };
    }
</script>

{{-- PAGE SCRIPTS --}}
@stack('scripts')


</body>

</html>