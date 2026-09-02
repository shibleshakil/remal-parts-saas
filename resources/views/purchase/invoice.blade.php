<!DOCTYPE html>
<html>

<head>
    <title>{{ $invoice->first()->invoice_no ?? 'Invoice' }}</title>
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
        .vehicle-info {
            display: flex;
            margin-bottom: 2rem;
            width: 100%;
        }

        .col6 {
            width: 50%;
            /* padding: 0 10px; */
            display: flex;
            gap: 10px;
        }
        .col12 {
            width: 50%;
            /* padding: 0 10px; */
            display: flex;
            gap: 10px;
        }
        .center {
            text-align: center;
            padding: 5px 10px;
            background: #05057c;
            color: white;
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
    @php
        $first = $invoice->first();
        $paid = $first?->transactions?->sum('paid_amount') ?? 0;
        $totalAmount = $first->transactions?->sum('final_amount') ?? 0;
    @endphp

    <div class="header">
        <div class="left">
            <p>Invoice No: {{ $first->invoice_no }}</p>
            <p>Date: {{ $first->purchase_date?->format('d M Y') }}</p>
        </div>
        <div class="center">
            <h1>Purchase Invoice</h1>
        </div>
        <div class="right">
            <p>Supplier: {{ $first->supplier?->name ?? 'Walking Supplier' }}</p>
            <p>Supplier Mob: {{ $first->supplier?->phone ?? 0 }}</p>
        </div>
    </div>

    @if ($first?->vehicleDetail?->vehicle)
    @php
        $paid = $first?->vehicleDetail?->vehicle->purchaseTransaction?->paid_amount ?? 0;
        $total_purchase_price = $first?->vehicleDetail?->vehicle->total_purchase_price ?? 0;
        $total_vehicle_price = $first?->vehicleDetail?->vehicle->total_vehicle_price ?? 0;
        $due = $total_purchase_price - $paid;
    @endphp
    <h2 style="margin-bottom: 5px;">Vehicle Info:</h2>
    <div class="vehicle-info">
        <div class="col6">
            <div>
                <p>Vehicle Name</p>
                <p>Vehicle Model</p>
                <p>Vehicle Engine No</p>
                <p>Vehicle Chasis No</p>
            </div>
            <div>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>
            <div>
                <p>{{ $first?->vehicleDetail?->vehicle?->name }} - {{ $first?->vehicleDetail?->vehicle?->uid }}</p>
                <p>{{ $first?->vehicleDetail?->vehicle?->model }}</p>
                <p>{{ $first?->vehicleDetail?->vehicle?->engine_no }}</p>
                <p>{{ $first?->vehicleDetail?->vehicle?->chasis_no }}</p>
            </div>
        </div>
        <div class="col6" style="justify-content: flex-end">
            <div>
                <p>Vehicle Purchase Price</p>
                <p>Vehicle Total Price</p>
                <p>Vehicle Paid Amount</p>
                <p>Vehicle Due Amount</p>
            </div>
            <div>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>
            <div style="text-align: right;">
                <p>{{ number_format($total_purchase_price, 2) }}</p>
                <p>{{ number_format($total_vehicle_price, 2) }}</p>
                <p>{{ number_format($paid, 2) }}</p>
                <p>{{ number_format($due, 2) }}</p>
            </div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Parts Name</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice as $index => $item)
                @php
                    $unitPrice = $item->unit_price ?? 0;
                    $qty = ($item->quantity + $item->sold_quantity + $item->purchase_return_quantity) ?? 0;
                    $amount = $unitPrice * $qty;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->vehicleDetail?->parts_name ?? 'N/A' }} - {{ $item->vehicleDetail?->uid ?? '' }}{{ $first?->vehicleDetail?->vehicle?->name ? '' : ' | ' . ($item->vehicleDetail?->car_name ?? '') . ' - ' . ($item->vehicleDetail?->car_model ?? '') }}</td>
                    <td style="text-align: right;">{{ number_format($unitPrice, 2) }}</td>
                    <td style="text-align: right;">{{ $qty }}</td>
                    <td style="text-align: right;">{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="4" style="text-align: right; font-weight: 700;">Total:</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($totalAmount, 2) }}</td>
            </tr>
            @if (!$first?->vehicleDetail?->vehicle)
            <tr>
                <td colspan="4" style="text-align: right; font-weight: 700;">Paid:</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($paid, 2) }}</td>
            </tr>

            <tr>
                <td colspan="4" style="text-align: right; font-weight: 700;">Due:</td>
                <td style="text-align: right; font-weight: 700;">
                    {{ number_format($totalAmount - $paid, 2) }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="signature-div" style="display: flex; justify-content: space-between; margin-top: 5rem;">
        <div class="supplier-signature">
            <p>__________________________</p>
            <p>Supplier Signature</p>
        </div>
        <div class="authorized-signature" style="text-align: right;">
            <p>__________________________</p>
            <p>Authorized Signature</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>

</html>

