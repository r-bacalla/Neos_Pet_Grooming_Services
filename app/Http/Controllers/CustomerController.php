<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(
            $request->get('search', '')
        );

        $customers = Customer::with([
            'pets',
            'loyaltyVouchers',
            'sales.items',
            'sales.cashier',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view(
            'customers.index',
            compact('customers')
        );
    }

    public function create()
    {
        return redirect()->route('customers.index', ['add' => 1]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Customer created successfully.'
            );
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'pets',
            'sales.items',
            'groomingAppointments.pet',
            'groomingAppointments.service',
            'groomingAppointments.groomer',
            'loyaltyVouchers',
        ]);

        return view(
            'customers.show',
            compact('customer')
        );
    }

    public function edit(Customer $customer)
    {
        return view(
            'customers.edit',
            compact('customer')
        );
    }

    public function update(
        Request $request,
        Customer $customer
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $customer->update($validated);

        if ($request->boolean('return_to_index')) {
            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer updated.');
        }

        return redirect()
            ->route(
                'customers.show',
                $customer
            )
            ->with(
                'success',
                'Customer updated.'
            );
    }

    public function destroy(Customer $customer)
    {
        DB::transaction(function () use ($customer) {
            $deletedAt = now();
            $customer->pets()->whereNull('deleted_at')->update(['deleted_at' => $deletedAt]);
            $customer->delete();
        });

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer and active pets moved to Archive.');
    }
}