<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remal Al WAFI Quotation</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('public/app-assets/images/ico/favicon.ico') }}">
    <!-- Using a basic, widely available font -->

    <style>
        body {
            font-size: 16px;
        }

        .top-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            align-items: center;

        }

        .top-content .right {
            text-align: end;
        }

        .top-content .center {
            text-align: center;
            background: #05057c;
            color: white;
            padding: 5px 15px;
            align-self: baseline;
        }

        .top-content p,
        .top-content h2 {
            margin: 0;
        }

        .dotted {
            text-decoration: underline;
            text-decoration-style: dotted;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            margin-bottom: 2rem;
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

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table thead th {
            border: 1px solid #fff;
            padding: 5px 8px;
            background-color: #05057c;
            font-weight: bold;
            box-sizing: border-box;
            color: white;
        }

        table td {
            border: 1px solid #000;
            padding: 5px;
        }

        table td,
        table th {
            vertical-align: middle;
        }

        .col-price,
        .col-qty,
        .col-amount {
            text-align: right !important;
        }

        .item-table thead th {
            text-align: center !important;
        }

        .arabic-header {
            font-family: 'Noto Sans Arabic', sans-serif;
            text-align: right;
            direction: rtl;
        }

        @media print {
            body {

                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* @media print {
            header {
                position: fixed;
                background-repeat: no-repeat;
                top: 100;
                left: 0;
                right: 0;
                height: 150px;
            }

            footer {
                position: fixed;
                bottom: 180;
                left: 0;
                right: 0;
                height: 180px;
            }
        } */


        @page {
            size: A4;
            margin: 170px 1cm 200px 1cm;
        }
    </style>
</head>


<body>
    <div class="print-container">
        <!-- Header Section -->
        <!-- <header class="header-logo">
            <img src="{{ asset('public/assets/images/invoice_header.png') }}" alt="Logo" width="100%" height="150">
        </header> -->
        <!-- Main Table Section -->
        <div class="invoice-content">
            <div class="top-content">
                <div class="left">
                    <!-- <p>Mob: +971 4 555 5555</p>
                    <p>Mob: +971 4 555 5555</p>
                    <p>Plot No-6437B <br>
                        Al Dhaid Street, Al Sajaa <br>
                        Ind. Area Sharjah-UAE</p> -->
                    <p>No: <strong>{{ $sale->invoice_no ?? $sale->id }}</strong></p>
                    <p>Customer TRN: {{ $sale->customer->trn ?? '' }}</p>
                </div>
                <div class="center">
                    <h2>الفاتورة الضريبية</h2>
                    <h2>TAX INVOICE</h2>
                </div>
                <div class="right">
                    <!-- <p>+971 05 688 3612 :متحرك</p>
                    <p>+971 05 688 3612 :متحرك</p>
                    <p>بلات رقم 43 شرع الريد</p>
                    <p>النظة الصناعية المجعة الشارقة</p> -->
                    <p>Date: <strong class="dotted">{{ $sale->created_at->format('M d Y') }}</strong></p>
                </div>
            </div>

            <div class="details-row">
                <div style="width: 100%;">
                    <div class="field">
                        <span class="label">Mr./M/s.</span>
                        <span class="value" style="font-weight: 700;">{{ $sale->customer->name ?? 'Walking Customer' }}</span>
                        <span class="arabic-text label">السيد/السادة</span>
                    </div>
                </div>

            </div>

            <table class="item-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="5%" class="col-sno">الرقم<br>S.No.</th>
                        <th rowspan="2" width="55%" class="col-description">Description التفاصيل</th>
                        <th rowspan="2" width="10%" class="col-qty">العدد<br>Qty.</th>
                        <th rowspan="2" width="10%" class="col-price">الثمن<br>Unit Price</th>
                        <th colspan="2" width="20%" class="col-price">Amount المبلغ</th>
                    </tr>
                    <tr>
                        <th>Dhs. درهم</th>
                        <th>Fils فلس</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 12 empty rows to match the image -->
                    @foreach ($sale->items as $index => $item)
                    @php
                    $amount = ($item->unit_price * $item->quantity) ?? 0.00;
                    // Split amount into integer and decimal parts
                    [$integerPart, $decimalPart] = explode('.', number_format($amount, 2));
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

                    @php
                    $total_cost = $sale->total_cost ?? 0.00;
                    $discount = $sale->discount_amount ?? 0.00;
                    $discount_type = $sale->discount_type ?? 'flat';
                    $total_discount = $discount_type == 'percentage' ? ($total_cost * $discount / 100) : $discount;

                    $tax = $sale->tax_amount ?? 0.00;

                    $grand_total = $total_cost - $total_discount + $tax;
                    // Split amount into integer and decimal parts
                    [$sub_int, $sub_dec] = explode('.', number_format($total_cost, 2));
                    [$dis_int, $dis_dec] = explode('.', number_format($total_discount, 2));
                    [$grand_int, $grand_dec] = explode('.', number_format($grand_total, 2));

                    @endphp
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700;">
                            Sub Total:
                        </td>
                        <td style="text-align: right; font-weight: 700;">{{ $sub_int ?? 0.00 }}</td>
                        <td style="text-align: right; font-weight: 700;">{{$sub_dec}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700;">
                            Discount: ({{ $discount }} {{ $discount_type ?? 'flat' == 'percentage' ? '%' : 'DHS' }})
                        </td>
                        <td style="text-align: right; font-weight: 700;">{{ $dis_int ?? 0.00 }}</td>
                        <td style="text-align: right; font-weight: 700;">{{$dis_dec}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700;">
                            Tax (0%) :
                        </td>
                        <td style="text-align: right; font-weight: 700;">{{ $tax ?? 0.00 }}</td>
                        <td style="text-align: right; font-weight: 700;">00</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="field">
                                <span class="label">TOTAL DHS:</span>
                                <span class="arabic-value" style="font-weight: 700;">{{ $amountInWords }}</span>
                                <div class="arabic-label">المجموع</div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 700;">{{ $grand_int }}</td>
                        <td style="text-align: right; font-weight: 700;">{{$grand_dec}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Section -->
        <!-- <footer class="footer-logo">
            <img src="{{ asset('public/assets/images/invoice_footer.png') }}" alt="Logo" width="100%" height="180">
        </footer> -->
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
