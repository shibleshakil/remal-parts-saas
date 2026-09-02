<!DOCTYPE html>
<html>

<head>
    <title>{{$invoice->invoice_no}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 2rem;
        }

        p,
        h1,
        h2,
        h3,
        h4,
        h5 {
            margin: 0;
            line-height: normal;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
            padding: 5px 10px;
            background: #05057c;
            color: white;
            /* align-self: center; */
        }

        .right {
            text-align: right;
        }

        .vehicle-info {
            display: flex;
            margin-bottom: 2rem;
            width: 100%;
        }

        .col6 {
            width: 50%;
            padding: 0 10px;
            display: flex;
            gap: 10px;
        }

        .col12 {
            width: 50%;
            /* padding: 0 10px; */
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 1rem;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        @media print {
            body {

                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="left">
            <p>TRN: {{ $invoice->trn ?? '' }}</p>
            <p>Invoice No: {{ $invoice->invoice_no }}</p>
            <p>Date: {{ ($invoice->purchase_date)->format('d M Y') }}</p>
        </div>
        <div class="center">
            <h1>Purchase Invoice</h1>
        </div>
        <div class="">
            <p>Supplier: {{ $invoice->supplier?->name ?? 'Walking Supplier' }}</p>
            <p>Supplier Mob: {{ $invoice->supplier?->phone ?? '' }}</p>
        </div>
    </div>

    <h2 style="margin-bottom: 5px;">Vehicle Info:</h2>
    <div class="vehicle-info">
        <div class="col12">
            <div>
                <p>Vehicle Name</p>
                <p>Vehicle Model</p>
            </div>
            <div>
                <p>:</p>
                <p>:</p>
            </div>
            <div>
                <p>{{ $invoice->vehicleDetail?->car_name }}</p>
                <p>{{ $invoice->vehicleDetail?->car_model }}</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>UID</th>
                <th>Parts Name</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @php
                $unitPrice = $invoice->unit_price ?? 0;
                $qty = ($invoice->quantity + $invoice->sold_quantity) ?? 0;
                $amount = $unitPrice * $qty;
                $paid = $invoice->transaction?->paid_amount ?? 0;
                $due = $amount - $paid;
                @endphp
                <td>1</td>
                <td>{{ $invoice->vehicleDetail?->uid ?? 'N/A' }}</td>
                <td>{{ $invoice->vehicleDetail?->parts_name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($unitPrice, 2) }}</td>
                <td style="text-align: right;">{{ $qty }}</td>
                <td style="text-align: right;">{{ number_format($amount, 2) }}</td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right; font-weight: 700;">Total:</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($amount, 2) }}</td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right; font-weight: 700;">Paid:</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($paid, 2) }}</td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right; font-weight: 700;">Due:</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($due, 2) }}</td>
            </tr>

        </tbody>
    </table>

    <script>
        // Automatically trigger print
        window.onload = function() {
            window.print();

            // After print, close tab
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>

</html>
