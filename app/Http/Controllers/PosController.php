<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GroomingAppointment;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $selectedAppointment = $request->filled('grooming_appointment')
            ? GroomingAppointment::with(['customer.activeVoucher', 'pet'])
                ->whereIn('status', ['pending', 'approved', 'received', 'in_progress', 'ready', 'completed'])
                ->whereNull('sale_id')
                ->find($request->integer('grooming_appointment'))
            : null;

        $selectedAppointments = collect();
        if ($selectedAppointment) {
            $selectedAppointments = $selectedAppointment->visit_group_id
                ? GroomingAppointment::with(['customer', 'pet'])
                    ->where('visit_group_id', $selectedAppointment->visit_group_id)
                    ->orderBy('id')
                    ->get()
                : collect([$selectedAppointment]);
        }

        $autoPayment = $request->boolean('auto_payment');
        $selectedVoucherPercent = (float) optional($selectedAppointment?->customer?->activeVoucher)->discount_percent;

        $products = Product::query()
            ->with('stockBatches')
            ->where('active', true)
            ->where('available_stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $product->salePricingBreakdown()->isNotEmpty())
            ->values();

        $services = Service::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->with('pets')
            ->orderBy('name')
            ->get();

        $appointments = GroomingAppointment::with([
            'customer',
            'pet',
            'service',
        ])
            ->whereIn('status', [
                'scheduled',
                'checked_in',
                'in_progress',
                'ready',
            ])
            ->orderBy('scheduled_start')
            ->get();

        return view('pos.index', compact(
            'products',
            'services',
            'customers',
            'appointments',
            'selectedAppointment',
            'selectedAppointments',
            'autoPayment',
            'selectedVoucherPercent'
        ));
    }

    public function products(Request $request)
    {
        $search = trim($request->get('search', ''));

        $products = Product::query()
            ->where('active', true)
            ->where('available_stock', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($products);
    }

    public function services(Request $request)
    {
        $search = trim($request->get('search', ''));

        $services = Service::query()
            ->where('active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($services);
    }
}