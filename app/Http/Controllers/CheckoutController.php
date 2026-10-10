<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\GroomingAppointment;
use App\Models\LoyaltyVoucher;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'voucher_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'payment_method' => [
                'required',
                'in:cash,card,gcash,bank_transfer',
            ],

            'payment_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'amount_paid' => [
                'required',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.item_type' => [
                'required',
                'in:product,service',
            ],

            'items.*.product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],

            'items.*.service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'grooming_appointment_id' => [
                'nullable',
                'integer',
                'exists:grooming_appointments,id',
            ],
        ]);

        $result = DB::transaction(function () use ($validated, $request) {

            $items = [];

            $subtotal = 0;

            /*
             * ==========================================
             * PROCESS CART ITEMS
             * ==========================================
             */

            foreach ($validated['items'] ?? [] as $cartItem) {

                $type = $cartItem['item_type'];
                $quantity = (int) $cartItem['quantity'];

                /*
                 * ------------------------------------------
                 * PRODUCT
                 * ------------------------------------------
                 */

                if ($type === 'product') {

                    if (empty($cartItem['product_id'])) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'A product ID is required for product items.'
                            ],
                        ]);
                    }

                    $product = Product::query()
                        ->lockForUpdate()
                        ->findOrFail($cartItem['product_id']);

                    if (!$product->active) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "{$product->name} is no longer available."
                            ],
                        ]);
                    }

                    $batches = $product->stockBatches()
                        ->where('available_quantity', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', today());
                        })
                        ->orderByRaw('expiry_date IS NULL')
                        ->orderBy('expiry_date')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $availableQuantity = (int) $batches->sum('available_quantity');
                    if ($availableQuantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "Not enough unexpired product stock for {$product->name}. Available: {$availableQuantity}."
                            ],
                        ]);
                    }

                    $originalUnitPrice = (float) $product->price;
                    $lineTotal = 0;
                    $lineDiscount = 0;
                    $remainingQuantity = $quantity;
                    $batchAllocations = [];

                    foreach ($batches as $batch) {
                        if ($remainingQuantity === 0) {
                            break;
                        }

                        $batchQuantity = min((int) $batch->available_quantity, $remainingQuantity);
                        $discountPercent = $product->batchDiscountPercent($batch);
                        $batchUnitPrice = round($originalUnitPrice * (1 - $discountPercent / 100), 2);

                        $lineTotal += $batchUnitPrice * $batchQuantity;
                        $lineDiscount += ($originalUnitPrice - $batchUnitPrice) * $batchQuantity;
                        $remainingQuantity -= $batchQuantity;
                        $batchAllocations[] = [
                            'batch_id' => $batch->id,
                            'quantity' => $batchQuantity,
                        ];
                    }

                    $lineTotal = round($lineTotal, 2);
                    $lineDiscount = round($lineDiscount, 2);
                    $unitPrice = round($lineTotal / $quantity, 2);

                    $items[] = [
                        'product_id' => $product->id,
                        'service_id' => null,
                        'item_type' => 'product',
                        'item_name' => $product->name,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'discount' => $lineDiscount,
                        'line_total' => $lineTotal,
                        'batch_allocations' => $batchAllocations,
                        'product' => $product,
                    ];

                    $subtotal += $lineTotal;
                }

                /*
                 * ------------------------------------------
                 * SERVICE
                 * ------------------------------------------
                 */

                if ($type === 'service') {

                    if (empty($cartItem['service_id'])) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'A service ID is required for service items.'
                            ],
                        ]);
                    }

                    $service = Service::query()
                        ->findOrFail($cartItem['service_id']);

                    if (!$service->active) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "{$service->name} is no longer available."
                            ],
                        ]);
                    }

                    $unitPrice = (float) $service->rate;

                    $lineTotal = round(
                        $unitPrice * $quantity,
                        2
                    );

                    $items[] = [
                        'product_id' => null,
                        'service_id' => $service->id,
                        'item_type' => 'service',
                        'item_name' => $service->name,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'discount' => 0,
                        'line_total' => $lineTotal,
                        'product' => null,
                    ];

                    $subtotal += $lineTotal;
                }
            }

            /*
             * ==========================================
             * GROOMING APPOINTMENT
             * ==========================================
             *
             * If a ready grooming appointment is selected,
             * automatically add its weight-based grooming
             * price to the sale.
             */

            $appointment = null;
            $groomingAppointments = collect();
            $groomingSubtotal = 0;

            if (!empty($validated['grooming_appointment_id'])) {

                $appointment = GroomingAppointment::query()
                    ->lockForUpdate()
                    ->with(['customer', 'pet'])
                    ->findOrFail(
                        $validated['grooming_appointment_id']
                    );

                $groomingAppointments = $appointment->visit_group_id
                    ? GroomingAppointment::query()
                        ->with(['customer', 'pet', 'service'])
                        ->where('visit_group_id', $appointment->visit_group_id)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                    : collect([$appointment]);

                foreach ($groomingAppointments as $groomingAppointment) {
                    if (!in_array($groomingAppointment->status, ['pending', 'approved', 'received', 'in_progress', 'ready', 'completed'], true) || $groomingAppointment->sale_id) {
                        throw ValidationException::withMessages([
                            'grooming_appointment_id' => [
                                'Every pet in this grooming visit must be active and unpaid.'
                            ],
                        ]);
                    }

                    if (!$groomingAppointment->grooming_price) {
                        throw ValidationException::withMessages([
                            'grooming_appointment_id' => [
                                'A grooming appointment in this visit has no calculated service price.'
                            ],
                        ]);
                    }

                    if (
                        !empty($validated['customer_id']) &&
                        (int) $validated['customer_id'] !== (int) $groomingAppointment->customer_id
                    ) {
                        throw ValidationException::withMessages([
                            'customer_id' => [
                                'The selected customer does not match the grooming visit.'
                            ],
                        ]);
                    }

                    $groomingPrice = (float) $groomingAppointment->grooming_price;
                    $items[] = [
                        'product_id' => null,
                        'service_id' => $groomingAppointment->service_id,
                        'item_type' => 'service',
                        'item_name' => 'Grooming - ' . ($groomingAppointment->pet->name ?? 'Pet'),
                        'unit_price' => $groomingPrice,
                        'quantity' => 1,
                        'discount' => 0,
                        'line_total' => $groomingPrice,
                        'product' => null,
                    ];

                    $subtotal += $groomingPrice;
                    $groomingSubtotal += $groomingPrice;
                }
            }

            if (empty($items) && !$appointment) {
                throw ValidationException::withMessages([
                    'items' => ['Add at least one product before completing payment.'],
                ]);
            }

            /*
             * ==========================================
             * LOYALTY VOUCHER
             * ==========================================
             */

            $loyaltyDiscount = 0;

            $voucher = null;

            if (!empty($validated['voucher_code'])) {

                $voucher = LoyaltyVoucher::query()
                    ->lockForUpdate()
                    ->where(
                        'code',
                        $validated['voucher_code']
                    )
                    ->first();

                if (!$voucher) {
                    throw ValidationException::withMessages([
                        'voucher_code' => [
                            'Voucher not found.'
                        ],
                    ]);
                }

                if (!$voucher->isValid()) {
                    throw ValidationException::withMessages([
                        'voucher_code' => [
                            'This voucher is expired or already used.'
                        ],
                    ]);
                }

                if (
                    !empty($validated['customer_id']) &&
                    (int) $voucher->customer_id !==
                    (int) $validated['customer_id']
                ) {
                    throw ValidationException::withMessages([
                        'voucher_code' => [
                            'This voucher belongs to another customer.'
                        ],
                    ]);
                }

                $loyaltyDiscount = round(
                    $subtotal *
                    ((float) $voucher->discount_percent / 100),
                    2
                );
            } elseif ($appointment) {
                $voucher = LoyaltyVoucher::query()
                    ->lockForUpdate()
                    ->where('customer_id', $appointment->customer_id)
                    ->where('status', 'active')
                    ->whereDate('expires_at', '>=', today())
                    ->orderBy('expires_at')
                    ->first();

                if ($voucher) {
                    $loyaltyDiscount = round(
                        $groomingSubtotal *
                        ((float) $voucher->discount_percent / 100),
                        2
                    );
                }
            }

            /*
             * ==========================================
             * TOTALS
             * ==========================================
             */

            $discount = min(
                $subtotal,
                $loyaltyDiscount
            );

            $taxableAmount = max(
                0,
                $subtotal - $discount
            );

            $vatAmount = 0;
            $total = round($taxableAmount, 2);

            $amountPaid = round(
                (float) $validated['amount_paid'],
                2
            );

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => [
                        'Payment is insufficient.'
                    ],
                ]);
            }

            $change = round(
                $amountPaid - $total,
                2
            );

            /*
             * ==========================================
             * CUSTOMER
             * ==========================================
             */

            $customerId = $validated['customer_id'] ?? null;

            if (!$customerId && $appointment) {
                $customerId = $appointment->customer_id;
            }

            /*
             * ==========================================
             * CREATE SALE
             * ==========================================
             */

            $sale = Sale::create([
                'invoice_number' =>
                    $this->generateInvoiceNumber(),

                'customer_id' => $customerId,

                'user_id' =>
                    $request->user()->id,

                'subtotal' => $subtotal,

                'discount' => $discount,

                'vat_rate' => 0,

                'vat_amount' =>
                    $vatAmount,

                'total' => $total,

                'amount_paid' =>
                    $amountPaid,

                'change_amount' =>
                    $change,

                'payment_method' =>
                    $validated['payment_method'],

                'payment_reference' =>
                    $validated['payment_reference'] ?? null,

                'status' => 'completed',

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            /*
             * ==========================================
             * SALE ITEMS + STOCK
             * ==========================================
             */

            foreach ($items as $item) {

                SaleItem::create([
                    'sale_id' =>
                        $sale->id,

                    'product_id' =>
                        $item['product_id'],

                    'service_id' =>
                        $item['service_id'],

                    'item_type' =>
                        $item['item_type'],

                    'item_name' =>
                        $item['item_name'],

                    'unit_price' =>
                        $item['unit_price'],

                    'quantity' =>
                        $item['quantity'],

                    'discount' =>
                        $item['discount'],

                    'line_total' =>
                        $item['line_total'],
                ]);

                /*
                 * Deduct product stock.
                 */

                if (
                    $item['item_type'] === 'product' &&
                    $item['product']
                ) {

                    $product = Product::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $item['product']->id
                        );

                    $before = (int) $product->stock;
                    $availableBefore = (int) $product->available_stock;
                    $movementAvailable = $availableBefore;

                    foreach ($item['batch_allocations'] ?? [] as $allocation) {
                        $batch = ProductStockBatch::query()
                            ->where('product_id', $product->id)
                            ->lockForUpdate()
                            ->findOrFail($allocation['batch_id']);

                        if ($batch->available_quantity < $allocation['quantity']) {
                            throw ValidationException::withMessages([
                                'items' => ["Not enough stock remains in batch {$batch->batch_reference} for {$product->name}."],
                            ]);
                        }

                        $batch->decrement('available_quantity', $allocation['quantity']);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'product_stock_batch_id' => $batch->id,
                            'user_id' => $request->user()->id,
                            'sale_id' => $sale->id,
                            'type' => 'sale',
                            'quantity' => -$allocation['quantity'],
                            'batch_reference' => $batch->batch_reference,
                            'stock_before' => $before,
                            'stock_after' => $before,
                            'available_before' => $movementAvailable,
                            'available_after' => $movementAvailable - $allocation['quantity'],
                            'reason' => 'POS sale ' . $sale->invoice_number . ' from batch ' . $batch->batch_reference,
                        ]);

                        $movementAvailable -= $allocation['quantity'];
                    }

                    $product->syncStockTotals();
                    $product->refresh();

                    AuditLog::create([
                        'user_id' => $request->user()->id,
                        'action' => 'product_stock_out',
                        'module' => 'Inventory',
                        'record_type' => 'Product',
                        'record_id' => $product->id,
                        'description' => 'Stock-out of ' . $item['quantity'] . ' ' . Str::plural('unit', $item['quantity']) . ' of ' . $product->name . ' through sale ' . $sale->invoice_number,
                        'old_values' => ['stock' => $before, 'available_stock' => $availableBefore],
                        'new_values' => [
                            'stock' => $product->stock,
                            'available_stock' => $product->available_stock,
                            'quantity' => $item['quantity'],
                            'sale_id' => $sale->id,
                        ],
                        'ip_address' => $request->ip(),
                    ]);
                }
            }

            /*
             * ==========================================
             * USE VOUCHER
             * ==========================================
             */

            if ($voucher) {

                $voucher->update([
                    'status' => 'used',

                    'used_sale_id' =>
                        $sale->id,

                    'used_at' =>
                        now(),
                ]);

                if ((int) $voucher->required_visits === 10) {
                    Customer::query()
                        ->whereKey($voucher->customer_id)
                        ->update(['loyalty_visits' => 0]);
                }
            }

            /*
             * ==========================================
             * LOYALTY VISIT
             * ==========================================
             */

            if ($sale->customer_id) {

                $customer = Customer::query()
                    ->lockForUpdate()
                    ->find($sale->customer_id);

                if ($customer) {

                    $customer->increment(
                        'loyalty_visits'
                    );

                    $customer->refresh();

                    /*
                     * Every 10 visits:
                     * 100% voucher.
                     */

                    if (
                        $customer->loyalty_visits >= 10 &&
                        $customer->loyalty_visits % 10 === 0
                    ) {

                        $this->createVoucher(
                            $customer,
                            100,
                            10
                        );
                    }

                    /*
                     * Every 5 visits:
                     * 20% voucher.
                     */

                    elseif (
                        $customer->loyalty_visits >= 5 &&
                        $customer->loyalty_visits % 5 === 0
                    ) {

                        $this->createVoucher(
                            $customer,
                            20,
                            5
                        );
                    }
                }
            }

            /*
             * ==========================================
             * COMPLETE GROOMING
             * ==========================================
             */

            foreach ($groomingAppointments as $groomingAppointment) {
                $groomingAppointment->update(in_array($groomingAppointment->status, ['ready', 'completed'], true)
                    ? [
                        'sale_id' => $sale->id,
                        'status' => 'completed',
                        'completed_at' => $groomingAppointment->completed_at ?? now(),
                    ]
                    : ['sale_id' => $sale->id]
                );
            }

            /*
             * ==========================================
             * AUDIT LOG
             * ==========================================
             */

            AuditLog::create([
                'user_id' =>
                    $request->user()->id,

                'action' =>
                    'sale_completed',

                'module' =>
                    'POS',

                'record_type' =>
                    'Sale',

                'record_id' =>
                    $sale->id,

                'description' =>
                    'Completed sale ' .
                    $sale->invoice_number,

                'new_values' => [
                    'total' =>
                        $sale->total,

                    'payment_method' =>
                        $sale->payment_method,
                ],

                'ip_address' =>
                    $request->ip(),
            ]);

            return $sale;
        });

        if (!empty($validated['grooming_appointment_id'])) {
            return redirect()
                ->route('receipts.done', $result)
                ->with('success', 'Grooming payment successful. The receipt is available in the grooming queue.');
        }

        return redirect()
            ->route('receipts.done', $result)
            ->with('success', 'Payment successful.');
    }

    private function generateInvoiceNumber(): string
    {
        do {

            $number =
                'NEO-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(6)
                );

        } while (
            Sale::where(
                'invoice_number',
                $number
            )->exists()
        );

        return $number;
    }

    private function createVoucher(
        Customer $customer,
        float $discount,
        int $requiredVisits
    ): LoyaltyVoucher {
        return LoyaltyVoucher::create([
            'customer_id' =>
                $customer->id,

            'code' =>
                'NEO-' .
                strtoupper(
                    Str::random(8)
                ),

            'discount_percent' =>
                $discount,

            'required_visits' =>
                $requiredVisits,

            'issued_date' =>
                now()->toDateString(),

            'expires_at' =>
                now()
                    ->addDays(90)
                    ->toDateString(),

            'status' =>
                'active',
        ]);
    }
}