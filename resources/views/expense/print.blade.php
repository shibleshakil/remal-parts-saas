<!DOCTYPE html>
<html>

<head>
    <title>Expense List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <h3>{{$title}}</h3>
    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Expense Type</th>
                <th style="text-align: right;">Amount</th>

            </tr>
        </thead>
        <tbody>
            @if (sizeof($datas) > 0)
            @foreach ($datas as $exp)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $exp->name }}</td>
                <td style="text-align: right;">{{number_format(($exp->total_amount ?? 0), 2)}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: right;"><strong>Total Amount:</strong></td>
                <td  style="text-align: right;"><strong>{{number_format($datas->sum('total_amount'), 2)}}</strong></td>
            </tr>
            @else
            <tr>
                <td colspan="3" style="text-align: center;">No Data Found</td>
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
