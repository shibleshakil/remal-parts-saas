<!DOCTYPE html>
<html>
<head>
    <title>Print Vehicle Barcode</title>
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
        .barcode-box p,
        .barcode-box h4 {
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
        <h4>{{ $vehicle->name }} ({{ $vehicle->model }})</h4>

        @if(file_exists(public_path('storage/' . $vehicle->barcode)))
            <img class="barcode" src="{{ asset('public/storage/' . $vehicle->barcode) }}" alt="Barcode for {{ $vehicle->uid }}">
        @else
            <p style="color: red;">⚠️ Barcode image not found.</p>
        @endif

        <p>{{ $vehicle->uid }}</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
