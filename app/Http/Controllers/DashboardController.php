<?php

namespace App\Http\Controllers;

use App\Models\GroomingAppointment;
use App\Models\LoyaltyVoucher;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');

        $todayTransactions = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $monthlySales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total');

        $lowStockProducts = Product::where('active', true)
            ->whereColumn('available_stock', '<=', 'low_stock_level')
            ->orderBy('available_stock')
            ->get();

        $expiringBatches = ProductStockBatch::with('product')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays(Setting::expiryWarningDays()))
            ->where(function ($query) {
                $query->where('inventory_quantity', '>', 0)
                    ->orWhere('available_quantity', '>', 0);
            })
            ->orderBy('expiry_date')
            ->get();

        $expiredProducts = Product::where('active', true)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', today())
            ->get();

        $expiringVouchers = LoyaltyVoucher::where('status', 'active')
            ->whereDate('expires_at', '>=', today())
            ->whereDate('expires_at', '<=', today()->addDays(7))
            ->with('customer')
            ->orderBy('expires_at')
            ->get();

        $appointments = GroomingAppointment::with([
            'customer',
            'pet',
            'service',
            'groomer',
        ])
            ->whereDate('scheduled_start', $today)
            ->orderBy('scheduled_start')
            ->get();

        $recentSales = Sale::with([
            'customer',
            'cashier',
        ])
            ->latest()
            ->limit(10)
            ->get();

        abort_if(
            !$request->user()->isOwner() && ($request->query('view') !== null || $request->query('report') !== null),
            403
        );

        $dashboardView = $request->user()->isOwner()
            ? $request->query('view', 'reports')
            : 'overview';
        abort_if(!in_array($dashboardView, ['reports', 'overview'], true), 404);

        $selectedReport = $request->user()->isOwner() && $dashboardView === 'reports'
            ? $request->query('report')
            : null;
        abort_if($selectedReport !== null && !in_array($selectedReport, ['sales', 'products', 'grooming', 'inventory'], true), 404);

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $validatedDates = validator(compact('from', 'to'), [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ])->validate();
        $from = $validatedDates['from'];
        $to = $validatedDates['to'];

        $reportRows = collect();
        $reportTotals = [];

        if ($selectedReport === 'sales') {
            $salesQuery = Sale::query()
                ->where('status', 'completed')
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

            $summary = (clone $salesQuery)
                ->selectRaw('COUNT(*) as transactions')
                ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
                ->selectRaw('COALESCE(SUM(discount), 0) as discount')
                ->selectRaw('COALESCE(SUM(vat_amount), 0) as vat')
                ->selectRaw('COALESCE(SUM(total), 0) as total')
                ->first();

            $reportRows = (clone $salesQuery)
                ->with('customer')
                ->latest()
                ->paginate(10)
                ->withQueryString();
            $reportTotals = [
                'Completed transactions' => (int) $summary->transactions,
                'Subtotal' => (float) $summary->subtotal,
                'Discounts' => (float) $summary->discount,
                'VAT' => (float) $summary->vat,
                'Total sales' => (float) $summary->total,
            ];
        } elseif (in_array($selectedReport, ['products', 'grooming'], true)) {
            $itemType = $selectedReport === 'products' ? 'product' : 'service';
            $itemsQuery = SaleItem::query()
                ->where('item_type', $itemType)
                ->whereHas('sale', function ($query) use ($from, $to) {
                    $query->where('status', 'completed')
                        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                });

            $reportRows = (clone $itemsQuery)
                ->select('item_name')
                ->selectRaw('SUM(quantity) as quantity')
                ->selectRaw('SUM(line_total) as total')
                ->groupBy('item_name')
                ->orderByDesc('quantity')
                ->paginate(10)
                ->withQueryString();

            $summary = (clone $itemsQuery)
                ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
                ->selectRaw('COALESCE(SUM(line_total), 0) as total')
                ->first();
            $reportTotals = [
                'Units sold' => (float) $summary->quantity,
                'Sales value' => (float) $summary->total,
            ];
        } elseif ($selectedReport === 'inventory') {
            $reportRows = StockMovement::with(['product', 'user', 'sale'])
                ->latest()
                ->paginate(10)
                ->withQueryString();

            $summary = StockMovement::query()
                ->selectRaw('COUNT(*) as movements')
                ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
                ->first();
            $reportTotals = [
                'Total movements' => (int) $summary->movements,
                'Quantity recorded' => (float) $summary->quantity,
            ];
        }

        return view('dashboard', compact(
            'todaySales',
            'todayTransactions',
            'monthlySales',
            'lowStockProducts',
            'expiringBatches',
            'expiredProducts',
            'expiringVouchers',
            'appointments',
            'recentSales',
            'dashboardView',
            'selectedReport',
            'from',
            'to',
            'reportRows',
            'reportTotals'
        ));
    }
}