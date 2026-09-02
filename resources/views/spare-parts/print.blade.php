<!DOCTYPE html>
<html>

<head>
    <title>Spare Parts List</title>
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
                <th>UID</th>
                <th>Parts Name</th>
                <th>Car</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $sp)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $sp->uid }}</td>
                <td>{{ $sp->parts_name }}</td>
                <td>
                    @if ($sp->vehicle)
                    {{ $sp->vehicle?->name ." (". $sp->vehicle?->uid .")" }}||{{ $sp->vehicle?->model }}
                    @else
                    {{ $sp->car_name }}||{{ $sp->car_model }}
                    @endif
                </td>
                <td>{{ $sp->parts_quantity }}</td>
            </tr>
            @endforeach
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
