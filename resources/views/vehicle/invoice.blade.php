<!DOCTYPE html>
<html>

<head>
    <title>{{$vehicle->invoice_no}}</title>
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
            <p>TRN: {{ $vehicle->trn ?? '' }}</p>
            <p>Invoice No: {{ $vehicle->invoice_no }}</p>
            <p>Date: {{ ($vehicle->date)->format('d M Y') }}</p>
        </div>
        <div class="center">
            <h1>Purchase Invoice</h1>
        </div>
        <div class="">
            <p>Supplier: {{ $vehicle->supplier?->name ?? 'Walking Supplier' }}</p>
            <p>Supplier Mob: {{ $vehicle->supplier?->phone }}</p>
        </div>
    </div>

    @php
    $total_purchase_price = $vehicle->total_purchase_price ?? 0;
    $total = $vehicle->total_vehicle_price ?? 0;
    $paid = $vehicle->purchaseTransaction?->paid_amount ?? 0;
    $due = $total_purchase_price - $paid;
    @endphp

    <h2 style="margin-bottom: 5px;">Vehicle Info:</h2>
    <div class="vehicle-info">
        <div class="col6">
            <div>
                <p>Vehicle UID</p>
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
                <p>:</p>
            </div>
            <div>
                <p>{{ $vehicle->uid }}</p>
                <p>{{ $vehicle->name }}</p>
                <p>{{ $vehicle->model }}</p>
                <p>{{ $vehicle->engine_no }}</p>
                <p>{{ $vehicle->chasis_no }}</p>
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
                <p>{{ $total_purchase_price }}</p>
                <p>{{ $total }}</p>
                <p>{{ $paid }}</p>
                <p>{{ $due }}</p>
            </div>
        </div>
    </div>

    <h3>Parts Details:</h3>

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Parts UID</th>
                <th>Parts Name</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @if (sizeof($vehicle->details) > 0)

            @foreach ($vehicle->details as $part)
            <tr>
                @php
                $unitPrice = $part->purchaseLines->first()->unit_price ?? 0;
                $qty = $part->parts_quantity ?? 0;
                $amount = $unitPrice * $qty;
                @endphp
                <td>{{ $loop->iteration }}</td>
                <td>{{ $part->uid ?? 'N/A' }}</td>
                <td>{{ $part->parts_name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ $unitPrice }}</td>
                <td style="text-align: right;">{{ $qty }}</td>
                <td style="text-align: right;">{{ $amount }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right; font-weight: 700;">Total:</td>
                <td style="text-align: right; font-weight: 700;">{{ $total }}</td>
            </tr>
            @endif
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
