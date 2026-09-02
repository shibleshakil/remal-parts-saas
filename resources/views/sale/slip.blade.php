<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number ?? $sale->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #1e90ff;
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .header .company-info {
            text-align: right;
            font-size: 14px;
        }

        hr {
            border: 0;
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }

        .bill-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .bill-section h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .bill-section p {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #e6f0fa;
            color: #1e90ff;
            font-weight: 600;
        }

        td {
            color: #333;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .summary-box {
            width: 100%;
            max-width: 300px;
        }

        .summary-box h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .summary-box div {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .summary-box .bold {
            font-weight: 600;
            color: #1e90ff;
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            font-size: 14px;
            color: #666;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button button {
            background-color: #1e90ff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button button:hover {
            background-color: #1c86ee;
        }

        .btn {
            background: #0fa3f8;
            color: #fff;
            border: unset;
            border-radius: 5px;
            padding: 5px 10px;
            text-decoration: none;
        }

        .top-nav {
            background: #666;
            padding: 10px;
            display: flex;
            justify-content: space-between;
        }

        @media screen and (max-width: 600px) {
            .bill-section {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header .company-info {
                text-align: left;
                margin-top: 10px;
            }

            .summary-box {
                max-width: 100%;
            }
        }

        @media print {
            .top-nav {
                display: none;
            }

            body {
                padding: 0;
            }

            .container {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top-nav">
            <a class="btn btn-dark" href="{{ route('sales.index') }}">Back</a>

            <button class="btn" onclick="window.print()">Print Invoice</button>
        </div>

        <!-- Header -->
        <div class="header">
            <div>
                <h1>Invoice</h1>
                <p>Invoice #{{ $sale->invoice_number ?? $sale->id }}</p>
                <p>Issue Date: {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</p>
            </div>
            <div class="company-info">
                <h2>NPSE</h2>
                <p>Dubai</p>
                <p>npse@example.com</p>
                <p>+880-XXXX-XXXX</p>
            </div>
        </div>

        <hr>

        <!-- Bill From/To -->
        <div class="bill-section">
            <div>
                <h2>NPSE</h2>
                <p>Dubai</p>
                <p>npse@example.com</p>
                <p>+880-XXXX-XXXX</p>
            </div>
            <div>
                <h3>Bill To</h3>
                <p>
                    {{ $sale->customer->name ?? 'Walking Customer' }}<br>
                    {{ $sale->customer->address ?? 'N/A' }}<br>
                    {{ $sale->customer->email ?? 'N/A' }}<br>
                    {{ $sale->customer->phone ?? 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $item->item_type)) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-box">
                <h3>Summary</h3>
                <div>
                    <span>Subtotal:</span>
                    <span>{{ number_format($sale->total_cost, 2) }}</span>
                </div>
                <div>
                    <span>Discount:</span>
                    <span>-{{ number_format($sale->discount_type === 'percentage' ? ($sale->total_cost * $sale->discount_amount) / 100 : $sale->discount_amount, 2) }}</span>
                </div>
                <div class="bold">
                    <span>Total:</span>
                    <span>{{ number_format($sale->final_amount, 2) }}</span>
                </div>
                <div>
                    <span>Paid:</span>
                    <span>{{ number_format($sale->paid_amount ?? 0, 2) }}</span>
                </div>
                <div>
                    <span>Balance:</span>
                    <span>{{ number_format($sale->final_amount - ($sale->paid_amount ?? 0), 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thanks for your business.</p>
        </div>
    </div>

</body>

</html>
