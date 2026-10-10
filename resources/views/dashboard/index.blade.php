@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-slate-500">Today's Sales</div>
        <div class="text-3xl font-bold mt-2">
            ₱{{ number_format($todaySales ?? 0, 2) }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-slate-500">Today's Transactions</div>
        <div class="text-3xl font-bold mt-2">
            {{ $todayTransactions ?? 0 }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-slate-500">Monthly Sales</div>
        <div class="text-3xl font-bold mt-2">
            ₱{{ number_format($monthlySales ?? 0, 2) }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-slate-500">Appointments Today</div>
        <div class="text-3xl font-bold mt-2">
            {{ $todayAppointments->count() ?? 0 }}
        </div>
    </div>

</div>


<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- LOW STOCK --}}
    <div class="bg-white rounded-xl shadow-sm">

        <div class="p-5 border-b">
            <h2 class="font-bold">Low Stock Products</h2>
        </div>

        <div class="p-5">

            @forelse($lowStockProducts as $product)

                <div class="flex justify-between items-center py-3 border-b last:border-0">

                    <div>
                        <div class="font-medium">
                            {{ $product->name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            SKU: {{ $product->sku }}
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm">
                        {{ $product->available_stock }} available to sell
                    </span>

                </div>

            @empty

                <div class="text-slate-500 text-sm">
                    No low-stock products.
                </div>

            @endforelse

        </div>

    </div>


    {{-- EXPIRING --}}
    <div class="bg-white rounded-xl shadow-sm">

        <div class="p-5 border-b">
            <h2 class="font-bold">Products Near Expiry</h2>
        </div>

        <div class="p-5">

            @forelse($expiringProducts as $product)

                <div class="flex justify-between items-center py-3 border-b last:border-0">

                    <div>
                        <div class="font-medium">
                            {{ $product->name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            Expires:
                            {{ optional($product->expiry_date)->format('M d, Y') }}
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm">
                        Near expiry
                    </span>

                </div>

            @empty

                <div class="text-slate-500 text-sm">
                    No products near expiry.
                </div>

            @endforelse

        </div>

    </div>

</div>


{{-- APPOINTMENTS --}}
<div class="bg-white rounded-xl shadow-sm mt-6">

    <div class="p-5 border-b flex justify-between">
        <h2 class="font-bold">Today's Grooming Appointments</h2>

        <a href="{{ route('grooming.index') }}"
           class="text-sm text-blue-600 hover:underline">
            View all
        </a>
    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-slate-50">
            <tr>
                <th class="text-left px-5 py-3">Time</th>
                <th class="text-left px-5 py-3">Customer</th>
                <th class="text-left px-5 py-3">Pet</th>
                <th class="text-left px-5 py-3">Service</th>
                <th class="text-left px-5 py-3">Status</th>
            </tr>
            </thead>

            <tbody>

            @forelse($todayAppointments as $appointment)

                <tr class="border-t">

                    <td class="px-5 py-3">
                        {{ \Carbon\Carbon::parse($appointment->scheduled_start)->format('h:i A') }}
                    </td>

                    <td class="px-5 py-3">
                        {{ $appointment->customer->name ?? '-' }}
                    </td>

                    <td class="px-5 py-3">
                        {{ $appointment->pet->name ?? '-' }}
                    </td>

                    <td class="px-5 py-3">
                        {{ $appointment->service->name ?? '-' }}
                    </td>

                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                        No appointments today.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection