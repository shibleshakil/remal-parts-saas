<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remal Al Awafi Purchase Return Invoice</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('public/app-assets/images/ico/favicon.ico') }}">
    <!-- Using a basic, widely available font -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap');

        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            padding: 20px;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }

        .quotation-form {
            padding: 10px 15px;
            line-height: 1.4;
            color: #333;
        }

        /* --- Header Section --- */
        .header {
            display: flex;
            flex-direction: column;
            border-bottom: 2px solid #555;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .mb-2{
            margin-bottom:10px;
        }

        .ml-2{
            margin-left: 10px;
        }

        .company-name-row {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #C00000; /* Red color for the main name */
            padding-top: 5px;
            margin-bottom: 5px;
        }

        .company-name-row span {
            color: #333;
            font-size: 16px;
        }

        .company-name-row h1 {
            margin: 0;
            padding: 0;
            font-size: 18px;
        }

        .contact-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .contact-left {
            text-align: left;
            width: 45%;
        }

        .contact-left p, .contact-right p {
            margin: 0 0 2px 0;
        }

        .contact-right {
            text-align: right;
            direction: rtl; /* For Arabic content alignment */
            font-family: 'Noto Sans Arabic', sans-serif;
            width: 45%;
        }

        .contact-right .logo-text-ar {
            color: #C00000;
        }

        .contact-right .arabic-text {
            font-size: 11px;
        }

        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .logo-placeholder {
            font-size: 16px;
            font-weight: bold;
            color: #C00000;
        }

        .quotation-box {
            background-color: #003366; /* Dark Blue */
            color: white;
            padding: 5px 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qtn-box {
            text-align: center;
        }

        .qtn-box .qtn{
            background-color: #304A8B;
            color: white;
            padding: 2px 15px;
        }

        .qtn-ar{
            border-radius: 5px 5px 0px 0px;
        }

        .qtn-en{
            border-radius: 0px 0px 5px 5px;
        }

        .qtn-box hr {
            margin: 0;
            color: white;
        }

        .quotation-box .arabic {
            font-family: 'Noto Sans Arabic', sans-serif;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
        }

        /* --- Details Section --- */
        .details-section {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .details-row div {
            flex: 1;
        }

        .quotation-no {
            font-size: 14px;
            font-weight: bold;
        }

        .field {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .field .label {
            font-weight: normal;
            white-space: nowrap;
        }

        .value {
            border-bottom: 1px dashed #555;
            flex-grow: 1;
            padding-left: 5px;
            text-align: left;
        }

        .field .value {
            border-bottom: 1px dashed #555;
            flex-grow: 1;
            padding-left: 5px;
            text-align: left;
        }

        .field .arabic-value {
            border-bottom: 1px dashed #555;
            flex-grow: 1;
            padding-right: 10px;
            text-align: right;
        }

        .field .border-0{
            border:0px;
        }

        .field-right {
            direction: rtl;
            text-align: right;
            font-family: 'Noto Sans Arabic', sans-serif;
        }

        /* --- Table Section --- */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .item-table thead th {
            border: 1px solid #333;
            padding: 5px 2px;
            background-color: #f5f5f5;
            font-weight: bold;
            /* vertical-align: top; */
            height: 30px;
            box-sizing: border-box;
        }

        .item-table tbody td {
            border: 1px solid #999;
            height: 28px; /* Height for the lines */
            padding: 0 5px;
        }

        /* Column widths to match the form visually */
        .col-sno { width: 5%; text-align: center; }
        .col-description { width: 30%; text-align: center; }
        .col-details { width: 25%; text-align: right; direction: rtl; font-family: 'Noto Sans Arabic', sans-serif; }
        .col-qty { width: 7%; text-align: center; }
        .col-price { width: 13%; text-align: center; }
        .col-amount { width: 20%; text-align: right; }

        /* Specific header styles */
        .header-amount {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            padding: 0;
            border-left: 1px solid #333;
        }

        .header-currency {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #333;
            padding-top: 2px;
            margin-top: 2px;
        }

        .header-currency div {
            flex: 1;
            text-align: center;
        }

        .header-currency .fill-text {
            color: #000000ff; /* Faint color for Fils/فلس */
        }

        table td, table th{
            vertical-align: middle;
        }

        .arabic-header {
            font-family: 'Noto Sans Arabic', sans-serif;
            text-align: right;
            direction: rtl;
        }

        /* Faint background image simulation */
        .table-body-container {
            position: relative;
            overflow: hidden;
        }

        .table-body-container::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 200px;
            background-image: url('https://placehold.co/300x200/cccccc/ffffff?text=Car+Logo'); /* Placeholder for the faint car logo */
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1; /* Very faint */
            pointer-events: none;
            z-index: 0;
        }

        .item-table tbody {
            position: relative;
            z-index: 1;
        }

        /* --- Totals and Signatures --- */
        .footer-section {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
        }

        .total-box {
            display: flex;
            width: 30%; /* Align with the last two columns of the table */
            border: 1px solid #333;
        }

        .total-box .label {
            width: 60%;
            padding: 5px 10px;
            font-weight: bold;
            border-right: 1px solid #333;
        }

        .total-box .value {
            width: 40%;
            padding: 5px 10px;
            text-align: right;
            font-weight: bold;
            font-family: 'Noto Sans Arabic', sans-serif;
        }

        .total-box .arabic-label {
            direction: rtl;
            font-family: 'Noto Sans Arabic', sans-serif;
        }

        .signatures-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding-top: 10px;
        }

        .signature-item {
            width: 23%;
            text-align: center;
        }

        .signature-label {
            margin-top: 40px; /* Space for the signature line */
            border-top: 1px dashed #555;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        .arabic-signature {
            font-family: 'Noto Sans Arabic', sans-serif;
            direction: rtl;
        }

        @media print {
            .quotation-box {
                /* background-color: #003366 !important; */

                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .qtn-box .qtn{
                background-color: #003366;
                color: white;
                padding: 2px 15px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }


    </style>
</head>
<body>
{{--
<div class="quotation-form">
    <!-- Header Section -->
    <div class="header">
        <div class="company-name-row">
            <!-- Simplified Logo Area -->
            <h1 style="font-size: 20px;">
                <span style="color: rgba(255, 0, 0, 0.87); font-size: 30px">رمال العوافي لتجارة السيارات المستعملة وقطع غيار داد . مم شش و</span> <br>
                <span style="color: #304A8B; font-size: 30px">REMAL AL AWAFI</span> <br>
                <span style="color: rgba(255, 0, 0, 0.87);"> USED CARS & SPARE PARTS TR. LLC S.P</span>
            </h1>
        </div>

        <div class="contact-info">
            <div class="contact-left">
                <p>Mob.: +971 56 688 3612</p>
                <p>Mob.: +971 56 688 3610</p>
                <p>Plot No- 6437B</p>
                <p>Al Dhaid Street, Al Sajaa</p>
                <p>Ind. Area Sharjah-UAE</p>
            </div>

            <!-- <div class="quotation-box">
                <span class="arabic">فَاتُورة</span>
                <hr style="width: 100%;">
                <span>QUOTATION</span>
            </div> -->
            <div class="qtn-box">
                <div class="qtn-ar qtn">فاتورة</div>
                <hr>
                <div class="qtn-en qtn">INVOICE</div>
            </div>
            <div class="contact-right">
                <p class="arabic-text">متجرك: ٣٦١٣ ٨٨ ٦٨٨ ٩٧١+</p>
                <p class="arabic-text">متجرك: ٣٦١٠ ٨٨ ٦٨٨ ٩٧١+</p>
                <p class="arabic-text">بلات رقم ٦٤٣٧ B شارع الذيد</p>
                <p class="arabic-text">المنطقة الصناعية المجعة الشرقية</p>
            </div>
        </div>

    </div>

    <!-- Details Section -->
    <div class="details-section">
        <div>
            <div class="field">
                <span class="label">No: </span>
                <span class="value border-0" style="font-weight: 700;">{{ $invoiceNo }}</span>
            </div>
        </div>

        <div class="details-row">
            <div>
                 <div class="field">
                    <span class="label">Customer TRN: </span>
                    <span class="value border-0"></span>
                </div>
            </div>

            <div class="field-right" style="width: 50%;">
                <div class="field">
                    <span class="arabic-text label">التاريخ</span>
                    <span class="arabic-value" style="font-weight: 700;">{{ date('M d, Y', strtotime($date)) }}</span>
                </div>
            </div>

        </div>

        <div class="details-row">
            <div style="width: 100%;">
                <div class="field">
                    <span class="label">Mr./M/s.</span>
                    <span class="value" style="font-weight: 700;">{{ $customerName }}</span>
                    <span class="arabic-text label">السيد/السادة</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Main Table Section -->
    <div class="table-body-container">
        <h3>Returned Items</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-sno">الرقم<br>S.No.</th>
                    <th rowspan="2" class="col-description">Description التفاصيل</th>
                    <th rowspan="2" class="col-qty">العدد<br>Qty.</th>
                    <th rowspan="2" class="col-price">الثمن<br>Unit Price</th>
                    <th colspan="2" class="col-price">Amount المبلغ</th>
                </tr>
                <tr>
                    <th>Dhs. درهم</th>
                    <th width="5%">Fils فلس</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalReturnAmount = 0.00;
                @endphp

                @foreach ($returnedItems as $index => $item)
                  @php
                        $amount = ($item['unit_price'] * $item['quantity']) ?? 0.00;
                        $totalReturnAmount += $amount;
                        // Split amount into integer and decimal parts
                        [$integerPart, $decimalPart] = explode('.', number_format($amount,2));
                    @endphp
                    <tr>
                        <td class="col-sno">{{ $index + 1 }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item['item_name'])) }}</td>
                        <td class="col-qty">{{ $item['quantity'] }}</td>
                        <td class="col-price">{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="col-amount">{{ $integerPart }}</td>
                        <td class="col-amount">{{ $decimalPart }}</td>
                    </tr>
                @endforeach
                <tr>
                  @php
                        $final_amount = $totalReturnAmount;
                        // Split amount into integer and decimal parts
                        [$final_integerPart, $final_decimalPart] = explode('.', number_format($final_amount, 2));
                    @endphp
                    <td colspan="4">
                        <div class="field">
                            <span class="label">TOTAL DHS:</span>
                            <span class="arabic-value" style="font-weight: 700;">{{ $amountInWords }}</span>
                            <div class="arabic-label">المجموع</div>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">{{ $final_integerPart }}</td>
                    <td class="col-amount">{{$final_decimalPart}}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($exchangeItems))
        <hr>

        <h3>Exchanged Items</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-sno">الرقم<br>S.No.</th>
                    <th rowspan="2" class="col-description">Description التفاصيل</th>
                    <th rowspan="2" class="col-qty">العدد<br>Qty.</th>
                    <th rowspan="2" class="col-price">الثمن<br>Unit Price</th>
                    <th colspan="2" class="col-price">Amount المبلغ</th>
                </tr>
                <tr>
                    <th>Dhs. درهم</th>
                    <th width="5%">Fils فلس</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalExchangeAmount = 0.00;
                @endphp
                @foreach ($exchangeItems as $index => $item)
                    @php
                        $exchangeAmount = ($item->unit_price * $item->quantity) ?? 0.00;
                        $totalExchangeAmount += $exchangeAmount;
                        // Split amount into integer and decimal parts
                        [$integerPart, $decimalPart] = explode('.', number_format($exchangeAmount, 2));
                    @endphp
                    <tr>
                        <td class="col-sno">{{ $index + 1 }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item->item_name)) }}</td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-price">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="col-amount">{{ $integerPart }}</td>
                        <td class="col-amount">{{ $decimalPart }}</td>
                    </tr>
                @endforeach
                <tr>
                    @php
                        $final_exchange_amount = $totalExchangeAmount;
                        // Split amount into integer and decimal parts
                        [$final_ex_integerPart, $final_ex_decimalPart] = explode('.', number_format($final_exchange_amount, 2));
                    @endphp
                    <td colspan="4">
                        <div class="field">
                            <span class="label">TOTAL DHS:</span>
                            <span class="arabic-value" style="font-weight: 700;">{{ $amountInWords }}</span>
                            <div class="arabic-label">المجموع</div>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">{{ $final_ex_integerPart }}</td>
                    <td class="col-amount">{{$final_ex_decimalPart}}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="signatures-row">
            <div class="signature-item">
                <div class="signature-label">Receiver's Sign<span class="ml-2">توقيع المستلم</span></div>
            </div>
            <div class="signature-item">
                <div class="signature-label">Signature<span class="ml-2">توقيع</span></div>
            </div>

        </div>
    </div>
</div> --}}
<div class="quotation-form">
    <!-- Header Section -->
    <div class="header">
        <div class="company-name-row">
            <h1 style="font-size: 20px;">
                <span style="color: rgba(255, 0, 0, 0.87); font-size: 30px">رمال العوافي لتجارة السيارات المستعملة وقطع غيار داد . مم شش و</span> <br>
                <span style="color: #304A8B; font-size: 30px">REMAL AL AWAFI</span> <br>
                <span style="color: rgba(255, 0, 0, 0.87);"> USED CARS & SPARE PARTS TR. LLC S.P</span>
            </h1>
        </div>

        <div class="contact-info">
            <div class="contact-left">
                <p>Mob.: +971 56 688 3612</p>
                <p>Mob.: +971 56 688 3610</p>
                <p>Plot No- 6437B</p>
                <p>Al Dhaid Street, Al Sajaa</p>
                <p>Ind. Area Sharjah-UAE</p>
            </div>

            <div class="qtn-box">
                <div class="qtn-ar qtn">فاتورة المرتجع</div>
                <hr>
                <div class="qtn-en qtn">RETURN INVOICE</div>
            </div>

            <div class="contact-right">
                <p class="arabic-text">متجرك: ٣٦١٣ ٨٨ ٦٨٨ ٩٧١+</p>
                <p class="arabic-text">متجرك: ٣٦١٠ ٨٨ ٦٨٨ ٩٧١+</p>
                <p class="arabic-text">بلات رقم ٦٤٣٧ B شارع الذيد</p>
                <p class="arabic-text">المنطقة الصناعية المجعة الشرقية</p>
            </div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="details-section">
        <div>
            <div class="field">
                <span class="label">Invoice No: </span>
                <span class="value border-0" style="font-weight: 700;">{{ $invoiceNo }}</span>
            </div>
        </div>

        <div class="details-row">
            <div>
                 <div class="field">
                    <span class="label">Supplier / Account: </span>
                    <span class="value border-0">{{ $supplierName ?? 'Walking Supplier' }}</span>
                </div>
            </div>

            <div class="field-right" style="width: 50%;">
                <div class="field">
                    <span class="arabic-text label">التاريخ</span>
                    <span class="arabic-value" style="font-weight: 700;">{{ date('M d, Y', strtotime($date)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Section -->
    <div class="table-body-container">
        <h3>Returned Items (القطع المرتجعة)</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-sno">الرقم<br>S.No.</th>
                    <th rowspan="2" class="col-description">Description التفاصيل</th>
                    <th rowspan="2" class="col-qty">العدد<br>Qty.</th>
                    <th rowspan="2" class="col-price">الثمن<br>Unit Price</th>
                    <th colspan="2" class="col-price">Amount المبلغ</th>
                </tr>
                <tr>
                    <th>Dhs. درهم</th>
                    <th width="5%">Fils فلس</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalReturnAmount = 0.00;
                @endphp

                @foreach ($returnedItems as $index => $item)
                    @php
                        $amount = ($item['unit_price'] * $item['quantity']) ?? 0.00;
                        $totalReturnAmount += $amount;
                        [$integerPart, $decimalPart] = explode('.', number_format($amount, 2));
                    @endphp
                    <tr>
                        <td class="col-sno">{{ $index + 1 }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item['item_name'])) }}</td>
                        <td class="col-qty">{{ $item['quantity'] }}</td>
                        <td class="col-price">{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="col-amount" style="text-align: right;">{{ $integerPart }}</td>
                        <td class="col-amount">{{ $decimalPart }}</td>
                    </tr>
                @endforeach
                <tr>
                    @php
                        [$final_integerPart, $final_decimalPart] = explode('.', number_format($totalReturnAmount, 2));
                    @endphp
                    <td colspan="4">
                        <div class="field">
                            <span class="label">TOTAL RETURN:</span>
                            <div class="arabic-label" style="float: right;">إجمالي المرتجع</div>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">{{ $final_integerPart }}</td>
                    <td class="col-amount" style="font-weight: 700;">{{ $final_decimalPart }}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($exchangeItems) && count($exchangeItems) > 0)
        <br><hr><br>

        <h3>Exchanged Items (القطع المستبدلة الجديده)</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-sno">الرقم<br>S.No.</th>
                    <th rowspan="2" class="col-description">Description التفاصيل</th>
                    <th rowspan="2" class="col-qty">العدد<br>Qty.</th>
                    <th rowspan="2" class="col-price">الثمن<br>Unit Price</th>
                    <th colspan="2" class="col-price">Amount المبلغ</th>
                </tr>
                <tr>
                    <th>Dhs. درهم</th>
                    <th width="5%">Fils فلس</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalExchangeAmount = 0.00;
                @endphp
                @foreach ($exchangeItems as $index => $item)
                    @php
                        $exchangeAmount = ($item->unit_price * $item->quantity) ?? 0.00;
                        $totalExchangeAmount += $exchangeAmount;
                        [$exIntegerPart, $exDecimalPart] = explode('.', number_format($exchangeAmount, 2));
                    @endphp
                    <tr>
                        <td class="col-sno">{{ $index + 1 }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item->vehicleDetail->parts_name ?? 'Exchange Product')) }}</td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-price">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="col-amount" style="text-align: right;">{{ $exIntegerPart }}</td>
                        <td class="col-amount">{{ $exDecimalPart }}</td>
                    </tr>
                @endforeach
                <tr>
                    @php
                        [$final_ex_integerPart, $final_ex_decimalPart] = explode('.', number_format($totalExchangeAmount, 2));
                    @endphp
                    <td colspan="4">
                        <div class="field">
                            <span class="label">TOTAL EXCHANGE:</span>
                            <div class="arabic-label" style="float: right;">إجمالي الاستبدال</div>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">{{ $final_ex_integerPart }}</td>
                    <td class="col-amount" style="font-weight: 700;">{{ $final_ex_decimalPart }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <!-- Footer Section -->
    <div class="footer-section" style="margin-top: 40px;">
        <div class="signatures-row">
            <div class="signature-item">
                <div class="signature-label">Manager Signature<span class="ml-2">توقيع المدير</span></div>
            </div>
            <div class="signature-item">
                <div class="signature-label">Supplier Signature<span class="ml-2">توقيع المورد</span></div>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('load', () => {
        window.print();

        // Wait a bit for the print dialog to finish, then close
        window.onafterprint = () => {
            window.close();
        };
    });
</script>

</body>
</html>

