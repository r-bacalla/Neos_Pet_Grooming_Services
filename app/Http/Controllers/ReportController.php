<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        $generatedReports = GeneratedReport::latest()->paginate(10);

        return view('reports.index', compact('generatedReports'));
    }

    public function generate(Request $request, string $type)
    {
        abort_unless(in_array($type, ['sales', 'products', 'grooming', 'inventory'], true), 404);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $title = ucfirst($type) . ' Report';
        $rows = $this->reportRows($type, $from, $to);

        $filePath = 'reports/' . Str::uuid() . '.pdf';
        $contents = Pdf::loadView('reports.pdf', [
            'title' => $title,
            'from' => $from,
            'to' => $to,
            'sections' => [[
                'title' => $title,
                'headers' => $rows['headers'],
                'data' => $rows['data'],
            ]],
        ])->setPaper('a4', 'landscape')->output();
        Storage::disk('local')->put($filePath, $contents);

        $report = GeneratedReport::create([
            'user_id' => auth()->id(),
            'type' => $type,
            'title' => $title,
            'from_date' => $from,
            'to_date' => $to,
            'file_path' => $filePath,
        ]);

        return redirect()
            ->route('reports.index')
            ->with('success', $title . ' was saved and is ready to download.');
    }

    public function download(GeneratedReport $generatedReport)
    {
        abort_unless(Storage::disk('local')->exists($generatedReport->file_path), 404);

        $extension = str_ends_with($generatedReport->file_path, '.tar.gz')
            ? 'tar.gz'
            : pathinfo($generatedReport->file_path, PATHINFO_EXTENSION);
        $filename = Str::slug($generatedReport->title) . '-' . $generatedReport->created_at->format('Y-m-d') . '.' . $extension;

        if ($extension === 'csv' && $generatedReport->type === 'sales') {
            $contents = $this->salesCsvWithTextDates(
                Storage::disk('local')->get($generatedReport->file_path)
            );

            return response($contents, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return Storage::disk('local')->download(
            $generatedReport->file_path,
            $filename
        );
    }

    public function generateComplete(Request $request)
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'];
        $to = $validated['to'];
        $sections = [];
        foreach (['sales', 'products', 'grooming', 'inventory'] as $type) {
            $rows = $this->reportRows($type, $from, $to);
            $sections[] = [
                'title' => ucfirst($type) . ' Report',
                'headers' => $rows['headers'],
                'data' => $rows['data'],
            ];
        }

        $filePath = 'reports/' . Str::uuid() . '.pdf';
        $contents = Pdf::loadView('reports.pdf', [
            'title' => 'Complete Business Reports',
            'from' => $from,
            'to' => $to,
            'sections' => $sections,
        ])->setPaper('a4', 'landscape')->output();
        Storage::disk('local')->put($filePath, $contents);

        GeneratedReport::create([
            'user_id' => auth()->id(),
            'type' => 'complete',
            'title' => 'Complete Business Reports',
            'from_date' => $from,
            'to_date' => $to,
            'file_path' => $filePath,
        ]);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Complete business report was saved as a PDF.');
    }

    private function salesCsvWithTextDates(string $contents): string
    {
        $input = fopen('php://temp', 'r+');
        $output = fopen('php://temp', 'r+');
        fwrite($input, $contents);
        rewind($input);

        $headers = fgetcsv($input, null, ',', '"', '');
        if ($headers === false) {
            fclose($input);
            fclose($output);

            return $contents;
        }

        fputcsv($output, $headers, ',', '"', '');
        while (($row = fgetcsv($input, null, ',', '"', '')) !== false) {
            if (isset($row[0]) && preg_match('/^\d{4}-\d{2}-\d{2}(?:\s|$)/', $row[0])) {
                $row[0] = "'" . $row[0];
            }

            fputcsv($output, $row, ',', '"', '');
        }

        rewind($output);
        $formattedContents = stream_get_contents($output);
        fclose($input);
        fclose($output);

        return $formattedContents;
    }

    public function destroy(GeneratedReport $generatedReport)
    {
        $generatedReport->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', 'The saved report moved to Archive.');
    }

    public function destroyAll()
    {
        $reports = GeneratedReport::all();

        $count = $reports->count();
        GeneratedReport::query()->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', $count
                ? "Moved {$count} saved reports to Archive."
                : 'There were no saved reports to archive.');
    }

    private function reportRows(string $type, string $from, string $to): array
    {
        if ($type === 'sales') {
            $sales = Sale::with('customer')
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->where('status', 'completed')
                ->latest()
                ->get();

            return [
                'headers' => ['Date', 'Customer', 'Payment Method', 'Subtotal', 'Discount', 'VAT', 'Total'],
                'data' => $sales->map(fn ($sale) => [
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->customer?->name ?? 'Walk-in Customer',
                    $sale->payment_method,
                    $sale->subtotal,
                    $sale->discount,
                    $sale->vat_amount,
                    $sale->total,
                ])->all(),
            ];
        }

        if ($type === 'products' || $type === 'grooming') {
            $items = SaleItem::query()
                ->where('item_type', $type === 'products' ? 'product' : 'service')
                ->whereHas('sale', function ($query) use ($from, $to) {
                    $query->where('status', 'completed')
                        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                })
                ->select('item_name')
                ->selectRaw('SUM(quantity) as quantity')
                ->selectRaw('SUM(line_total) as total')
                ->groupBy('item_name')
                ->orderByDesc('quantity')
                ->get();

            return [
                'headers' => ['Item', 'Quantity', 'Total'],
                'data' => $items->map(fn ($item) => [$item->item_name, $item->quantity, $item->total])->all(),
            ];
        }

        $movements = StockMovement::with(['product', 'user'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->get();

        return [
            'headers' => ['Date', 'Product', 'Type', 'Quantity', 'User', 'Notes'],
            'data' => $movements->map(fn ($movement) => [
                $movement->created_at->toDateTimeString(),
                $movement->product?->name,
                $movement->type,
                $movement->quantity,
                $movement->user?->name,
                $movement->reason,
            ])->all(),
        ];
    }
    
    public function sales(Request $request)
    {
        $from = $request->get(
            'from',
            now()->startOfMonth()->toDateString()
        );

        $to = $request->get(
            'to',
            now()->toDateString()
        );

        $sales = Sale::with([
            'customer',
            'cashier',
            'items',
        ])
            ->whereBetween(
                'created_at',
                [
                    $from . ' 00:00:00',
                    $to . ' 23:59:59',
                ]
            )
            ->where(
                'status',
                'completed'
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = Sale::query()
            ->whereBetween(
                'created_at',
                [
                    $from . ' 00:00:00',
                    $to . ' 23:59:59',
                ]
            )
            ->where(
                'status',
                'completed'
            )
            ->selectRaw(
                'COUNT(*) as transactions'
            )
            ->selectRaw(
                'COALESCE(SUM(subtotal),0) as subtotal'
            )
            ->selectRaw(
                'COALESCE(SUM(discount),0) as discount'
            )
            ->selectRaw(
                'COALESCE(SUM(vat_amount),0) as vat'
            )
            ->selectRaw(
                'COALESCE(SUM(total),0) as total'
            )
            ->first();

        $paymentMethods = Sale::query()
            ->whereBetween(
                'created_at',
                [
                    $from . ' 00:00:00',
                    $to . ' 23:59:59',
                ]
            )
            ->where(
                'status',
                'completed'
            )
            ->select(
                'payment_method'
            )
            ->selectRaw(
                'COUNT(*) as transactions'
            )
            ->selectRaw(
                'SUM(total) as total'
            )
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $productSales = SaleItem::query()
            ->where('item_type', 'product')
            ->whereHas('sale', function ($query) use ($from, $to) {
                $query
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->select('item_name')
            ->selectRaw('SUM(quantity) as quantity')
            ->selectRaw('SUM(line_total) as total')
            ->groupBy('item_name')
            ->orderByDesc('quantity')
            ->get();

        $soldProductNames = $productSales->pluck('item_name')
            ->map(fn ($name) => mb_strtolower(trim((string) $name)));
        $unsoldProducts = Product::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['name'])
            ->reject(fn ($product) => $soldProductNames->contains(mb_strtolower(trim($product->name))))
            ->pluck('name')
            ->values();

        return view(
            'reports.sales',
            compact(
                'sales',
                'summary',
                'paymentMethods',
                'productSales',
                'unsoldProducts',
                'from',
                'to'
            )
        );
    }

    public function products(Request $request)
    {
        $from = $request->get(
            'from',
            now()->startOfMonth()->toDateString()
        );

        $to = $request->get(
            'to',
            now()->toDateString()
        );

        $itemsQuery = SaleItem::query()
            ->where(
                'item_type',
                'product'
            )
            ->whereHas('sale', function ($query) use (
                $from,
                $to
            ) {
                $query
                    ->where(
                        'status',
                        'completed'
                    )
                    ->whereBetween(
                        'created_at',
                        [
                            $from . ' 00:00:00',
                            $to . ' 23:59:59',
                        ]
                    );
            })
            ->select(
                'item_name'
            )
            ->selectRaw(
                'SUM(quantity) as quantity'
            )
            ->selectRaw(
                'SUM(line_total) as total'
            )
            ->groupBy('item_name')
            ->orderByDesc('quantity');

        $items = (clone $itemsQuery)->get();
        $paginatedItems = (clone $itemsQuery)->paginate(10)->withQueryString();

        $importedProducts = StockMovement::query()
            ->where('type', 'stock_in')
            ->whereBetween(
                'created_at',
                [
                    $from . ' 00:00:00',
                    $to . ' 23:59:59',
                ]
            )
            ->with('product')
            ->select('product_id')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->get();

        $dailySales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.item_type', 'product')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('DATE(sales.created_at) as day')
            ->selectRaw('SUM(sale_items.quantity) as quantity')
            ->groupBy(DB::raw('DATE(sales.created_at)'))
            ->pluck('quantity', 'day')
            ->map(fn ($value) => (float) $value)
            ->all();

        $dailyImports = StockMovement::query()
            ->where('type', 'stock_in')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('quantity', 'day')
            ->map(fn ($value) => (float) $value)
            ->all();

        $chartStart = \Carbon\Carbon::parse($from);
        $chartEnd = \Carbon\Carbon::parse($to);
        $chartDays = [];
        $cursor = $chartStart->copy();
        while ($cursor->lte($chartEnd)) {
            $chartDays[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $salesChartData = array_map(function ($day) use ($dailySales) {
            return [
                'label' => \Carbon\Carbon::parse($day)->format('M d'),
                'value' => (float) ($dailySales[$day] ?? 0),
            ];
        }, $chartDays);

        $importChartData = array_map(function ($day) use ($dailyImports) {
            return [
                'label' => \Carbon\Carbon::parse($day)->format('M d'),
                'value' => (float) ($dailyImports[$day] ?? 0),
            ];
        }, $chartDays);

        $trendValues = array_merge(
            array_column($salesChartData, 'value'),
            array_column($importChartData, 'value')
        );
        $trendMin = min($trendValues ?: [0]);
        $trendMax = max($trendValues ?: [1]);

        $buildTrendPoints = function (array $points) use ($trendMin, $trendMax) {
            $count = count($points);
            $width = 420;
            $height = 140;
            $paddingLeft = 30;
            $paddingRight = 30;
            $paddingTop = 18;
            $paddingBottom = 28;
            $innerWidth = $width - $paddingLeft - $paddingRight;
            $innerHeight = $height - $paddingTop - $paddingBottom;

            return array_map(function ($point, $index) use ($count, $width, $height, $paddingLeft, $paddingTop, $paddingRight, $paddingBottom, $innerWidth, $innerHeight, $trendMin, $trendMax) {
                $value = (float) ($point['value'] ?? 0);
                $x = $paddingLeft + ($count > 1 ? ($index * $innerWidth / ($count - 1)) : ($innerWidth / 2));
                $range = max($trendMax - $trendMin, 1);
                $scaled = ($value - $trendMin) / $range;
                $y = $height - $paddingBottom - ($scaled * $innerHeight);

                return [
                    'x' => round($x),
                    'y' => round($y),
                    'value' => $value,
                    'label' => $point['label'] ?? '',
                ];
            }, $points, array_keys($points));
        };

        $soldTotal = (float) $items->sum('quantity');
        $importedTotal = (float) $importedProducts->sum('quantity');
        $salesVsImportsTotal = max($soldTotal + $importedTotal, 1);
        $soldShare = $salesVsImportsTotal > 0 ? ($soldTotal / $salesVsImportsTotal) * 100 : 0;
        $importedShare = 100 - $soldShare;

        $salesTrend = $buildTrendPoints($salesChartData);
        $importTrend = $buildTrendPoints($importChartData);

        return view(
            'reports.products',
            compact(
                'items',
                'paginatedItems',
                'from',
                'to',
                'importedProducts',
                'soldTotal',
                'importedTotal',
                'soldShare',
                'importedShare',
                'salesTrend',
                'importTrend',
                'salesChartData',
                'importChartData'
            )
        );
    }

    public function grooming(Request $request)
    {
        $from = $request->get(
            'from',
            now()->startOfMonth()->toDateString()
        );

        $to = $request->get(
            'to',
            now()->toDateString()
        );

        $itemsQuery = SaleItem::query()
            ->where(
                'item_type',
                'service'
            )
            ->whereHas('sale', function ($query) use (
                $from,
                $to
            ) {
                $query
                    ->where(
                        'status',
                        'completed'
                    )
                    ->whereBetween(
                        'created_at',
                        [
                            $from . ' 00:00:00',
                            $to . ' 23:59:59',
                        ]
                    );
            })
            ->select(
                'item_name'
            )
            ->selectRaw(
                'SUM(quantity) as quantity'
            )
            ->selectRaw(
                'SUM(line_total) as total'
            )
            ->groupBy('item_name')
            ->orderByDesc('quantity');

        $items = (clone $itemsQuery)->get();
        $paginatedItems = (clone $itemsQuery)->paginate(10)->withQueryString();

        return view(
            'reports.grooming',
            compact(
                'items',
                'paginatedItems',
                'from',
                'to'
            )
        );
    }

    public function inventory()
    {
        $movements = StockMovement::with([
            'product',
            'user',
            'sale',
        ])
            ->latest()
            ->paginate(10);

        $movementSummary = StockMovement::query()
            ->select('type')
            ->selectRaw('COUNT(*) as movements')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy('type')
            ->orderByDesc('movements')
            ->get();

        return view(
            'reports.inventory',
            compact('movements', 'movementSummary')
        );
    }
}