<!DOCTYPE html>
<html>
<head>
    <title>Print Spare Part Barcode</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .barcode-box {
            border: 1px solid #333;
            padding: 10px;
            display: inline-block;
        }
        .barcode-box h4,
        .barcode-box p {
            margin: 0px;
        }
        img.barcode {
            margin: 10px auto;
            display: block;
            width: 300px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="barcode-box">
        <h4>{{ $sparePart->parts_name }} ({{ $sparePart->car_name }})</h4>

        @if(file_exists(public_path('storage/' . $sparePart->barcode)))
            <img class="barcode" src="{{ asset('public/storage/' . $sparePart->barcode) }}" alt="Barcode for {{ $sparePart->uid }}">
        @else
            <p style="color: red;">⚠️ Barcode image not found.</p>
        @endif

        <p>{{ $sparePart->uid }}</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
