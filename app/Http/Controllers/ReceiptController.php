<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class ReceiptController extends Controller
{
    public function show(Sale $sale)
    {
        return view('pos.receipt', $this->receiptData($sale));
    }

    public function print(Sale $sale)
    {
        return view('pos.receipt', [
            ...$this->receiptData($sale),
            'autoPrint' => true,
        ]);
    }

    public function done(Sale $sale)
    {
        return view('pos.payment-done', $this->receiptData($sale));
    }

    private function receiptData(Sale $sale): array
    {
        $sale->load([
            'customer',
            'cashier',
            'items',
        ]);

        $isGroomingSale = $sale->groomingAppointments()->exists();
        $returnUrl = $isGroomingSale
            ? (auth()->user()->isCustomer() ? route('grooming.create') : route('grooming.index'))
            : route('pos.index');
        $returnLabel = $isGroomingSale
            ? 'Back to Grooming'
            : 'Back to POS';

        return compact('sale', 'returnUrl', 'returnLabel');
    }
}