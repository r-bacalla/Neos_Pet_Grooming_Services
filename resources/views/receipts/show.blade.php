<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <title>Receipt {{ $sale->invoice_number }}</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #eee;
        }

        .receipt {
            width: 80mm;
            margin: auto;
            background: white;
            padding: 12px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        td {
            padding: 3px 0;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .total {
            font-size: 16px;
            font-weight: bold;
        }

        .print-button {
            margin: 20px auto;
            display: block;
            padding: 10px 20px;
            border: none;
            background: #111827;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {

            body {
                background: white;
                padding: 0;
            }

            .receipt {
                width: 80mm;
                padding: 0;
            }

            .print-button {
                display: none;
            }

        }

    </style>

</head>

<body>

<button
    onclick="window.print()"
    class="print-button">
    Print Receipt
</button>


<div class="receipt">

    <div class="center">

        <div class="bold">
            NEO'S PET GROOMING
        </div>

        <div>
            Pet Grooming & Supplies
        </div>

        <div>
            Official Receipt
        </div>

    </div>


    <div class="line"></div>


    <div style="font-size: 12px;">

        Invoice:
        <strong>{{ $sale->invoice_number }}</strong>
        <br>

        Date:
        {{ $sale->created_at->format('M d, Y h:i A') }}

        <br>

        Customer:
        {{ $sale->customer->name ?? 'Walk-in' }}

    </div>


    <div class="line"></div>


    <table>

        @foreach($sale->items as $item)

            <tr>

                <td>
                    {{ $item->item_name }}
                    <br>

                    {{ $item->quantity }}
                    x
                    ₱{{ number_format($item->unit_price, 2) }}
                </td>

                <td class="right">
                    ₱{{ number_format($item->line_total, 2) }}
                </td>

            </tr>

        @endforeach

    </table>


    <div class="line"></div>


    <table>

        <tr>
            <td>Subtotal</td>
            <td class="right">
                ₱{{ number_format($sale->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td>Discount</td>
            <td class="right">
                ₱{{ number_format($sale->discount, 2) }}
            </td>
        </tr>

        <tr class="total">
            <td>Total</td>
            <td class="right">
                ₱{{ number_format($sale->total, 2) }}
            </td>
        </tr>

    </table>


    <div class="line"></div>


    <table>

        <tr>
            <td>Payment</td>
            <td class="right">
                {{ strtoupper($sale->payment_method) }}
            </td>
        </tr>

        <tr>
            <td>Amount Paid</td>
            <td class="right">
                ₱{{ number_format($sale->amount_paid, 2) }}
            </td>
        </tr>

        <tr>
            <td>Change</td>
            <td class="right">
                ₱{{ number_format($sale->change_amount, 2) }}
            </td>
        </tr>

    </table>


    <div class="line"></div>


    <div class="center" style="font-size: 12px;">

        Thank you for choosing<br>
        <strong>Neo's Pet Grooming!</strong>

    </div>

</div>

</body>
</html>