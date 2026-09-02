@extends('layouts.app')
@section('title', 'Profit Report')
@section('css')
<style>
    #reportLimit .select2 {
        min-width: 200px !important;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">

        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Reports') }}</li>
                    <li class="breadcrumb-item active">{{ __('Gross Profit') }}</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="content-body">
        <section id="configuration">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card rounded">
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <h4 class="card-title">Profit Report</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form id="reportLimit" action="{{ route('report.profit') }}" method="post" class="row my-1 gx-3 gy-2 align-items-center">
                                            @csrf
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <select id="reportDate" name="gross_profit_report_date" class="form-control">
                                                            <option value="today" {{ session('gross_profit_report_date', 'today') == 'today' ? 'selected' : '' }}>Today</option>
                                                            <option value="last_7_days" {{ session('gross_profit_report_date') == 'last_7_days'? 'selected' : '' }}>Last 7 Days</option>
                                                            <option value="last_30_days" {{ session('gross_profit_report_date') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                                            <option value="this_month" {{ session('gross_profit_report_date') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                                            <option value="last_month" {{ session('gross_profit_report_date') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                                            <option value="this_year" {{ session('gross_profit_report_date') == 'this_year' ? 'selected' : '' }}>This Year</option>
                                                            <option value="custom" {{ session('gross_profit_report_date') == 'custom' ? 'selected' : '' }}>Custom</option>
                                                            <option value="vehicle" {{ session('gross_profit_report_date') == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                                                            <option value="parts" {{ session('gross_profit_report_date') == 'parts' ? 'selected' : '' }}>Spare Parts</option>
                                                        </select>
                                                    </div>

                                                    <div id="customDate" class="col-auto d-none">
                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="startDay" type="date" name="gross_profit_start_day" class="form-control" placeholder="{{__('Start Day')}}" value="{{  session('gross_profit_start_day') ?? '' }}">
                                                        </fieldset>

                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="endDay" type="date" name="gross_profit_end_day" class="form-control" placeholder="{{__('End Day')}}" value="{{  session('gross_profit_end_day') ?? '' }}">
                                                        </fieldset>
                                                    </div>


                                                    <div id="vehicleFilter" class="col-auto d-none">
                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <select id="vehicleId" name="gross_profit_vehicle_id" class="form-control select2">
                                                                <option value="all"
                                                                    {{ session('gross_profit_vehicle_id') == 'all' || empty(session('gross_profit_vehicle_id')) ? 'selected' : '' }}>
                                                                    All
                                                                </option>
                                                                @foreach ($vehicles as $key => $vehicle)
                                                                <option value="{{ $vehicle->id }}" {{ session('gross_profit_vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}({{ $vehicle->uid }})</option>
                                                                @endforeach
                                                            </select>
                                                        </fieldset>
                                                    </div>


                                                    <div id="partsFilter" class="col-auto d-none">
                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <select id="partId" name="gross_profit_part_id" class="form-control select2">
                                                                <option value="all"
                                                                    {{ session('gross_profit_part_id') == 'all' || empty(session('gross_profit_part_id')) ? 'selected' : '' }}>
                                                                    All
                                                                </option>
                                                                @foreach ($parts as $key => $part)
                                                                <option value="{{ $part->id }}" {{ session('gross_profit_part_id') == $part->id ? 'selected' : '' }}>{{ $part->parts_name }}({{ $part->uid }})</option>
                                                                @endforeach
                                                            </select>
                                                        </fieldset>
                                                    </div>


                                                    <div class="col-auto float-right">
                                                        <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="{{ route('report.profit') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="data_reset" value="1">
                                            <button type="submit" class="btn btn-secondary">{{ __('Reset') }}</button>
                                        </form>
                                    </div>
                                </div>
                                <hr class="border-top">
                                <div class="row mt-3">
                                    <div class="col-12">

                                        <table id="profitTable" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50%;">Description</th>
                                                    <th style="width: 30%; text-align: right">Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        Scrap Sell Amount
                                                    </td>
                                                    <td class="text-right"><span>{{ number_format($scrapSell ?? 0, 2) }}</span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm view-sale-details" data-title="scrap" id="fetchReport">
                                                            <i class="fa fa-eye"></i>
                                                            {{ __('View Details') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        Parts Sell Amount
                                                    </td>
                                                    <td class="text-right"><span>{{ number_format($partsSell ?? 0, 2) }}</span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm view-sale-details" data-title="parts" id="fetchReport">
                                                            <i class="fa fa-eye"></i>
                                                            {{ __('View Details') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr style="font-weight: bold;">
                                                    <td>Total Sell Amount</td>
                                                    <td class="text-right"><span>{{ number_format($totalSell ?? 0, 2) }}</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    <td>Total Purchase Amount</td>
                                                    <td class="text-right"><span>{{ number_format($purchaseAmount ?? 0, 2) }}</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    @php
                                                    $profit = ($totalSell ?? 0) - ($purchaseAmount ?? 0);
                                                    @endphp
                                                    <td style="font-weight: bold;">Gross Profit</td>
                                                    <td class="text-right"><span style="font-weight: bold;">{{ number_format($profit, 2) }}</span></td>
                                                    <td></td>
                                                </tr>

                                                @if(session('gross_profit_report_date') != 'vehicle' && session('gross_profit_report_date') != 'parts' )
                                                <tr>
                                                    <td>Total Expense</td>
                                                    <td class="text-right"><span>{{ number_format($totalExpense ?? 0, 2) }}</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    @php
                                                    $net_profit = $profit - ($totalExpense ?? 0);
                                                    @endphp
                                                    <td style="font-weight: bold;">Net Profit</td>
                                                    <td class="text-right"><span style="font-weight: bold;">{{ number_format($net_profit, 2) }}</span></td>
                                                    <td></td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal for Sale Details -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog" aria-labelledby="saleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleDetailsModalLabel">Sale Details of Profit Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportResult">
                <!-- Sale details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#profitTable').DataTable({
            dom: 'Bfrtip', // show buttons
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [{
                    extend: 'excelHtml5',
                    title: 'Profit Report'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Profit Report'
                },
                {
                    extend: 'print',
                    title: 'Profit Report'
                }
            ]
        });

        const reportDate = document.querySelector('[name="gross_profit_report_date"]').value;
        let url = new URL(window.location.href);
        if (reportDate !== 'custom') {
            url.searchParams.delete('vehicle_id');
        }
        window.history.replaceState({}, '', url);
    });



    function openSaleDetailsModal(dataFor) {
        let reportDate = $('#reportDate').val();
        let vehicleId = $('#vehicleId').val();
        let partId = $('#partId').val();
        let startDay = $('#startDay').val();
        let endDay = $('#endDay').val();

        $.ajax({
            url: '{{ route("report.saleDetails") }}', // make sure this route points to your controller
            type: 'GET', // or POST if you prefer
            data: {
                report_date: reportDate,
                vehicle_id: vehicleId,
                part_id: partId,
                start_day: startDay,
                end_day: endDay,
                data_for: dataFor
            },
            success: function(response) {
                renderSalesTable(response);
                $('#saleDetailsModal').modal('show'); // show the modal
            },
            error: function(err) {
                console.error(err);
                $('#reportResult').html('<p>Unable to load sale details.</p>');
            }
        });
    }

    // Example: trigger modal when clicking a button
    $(document).on('click', '.view-sale-details', function() {
        let dataFor = $(this).data('title');

        openSaleDetailsModal(dataFor);
    });


    function renderSalesTable(sales) {
        if (!sales || sales.length === 0) {
            $('#reportResult').html('<p>No sales found.</p>');
            return;
        }

        let totalAmount = 0;
        let totalActualAmount = 0;

        let html = `
            <table border="1" cellpadding="5" cellspacing="0"
                style="width:100%; border-collapse: collapse;"
                id="profiteDetailsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Item Description</th>
                        <th style="text-align: right;">Selling Unit Price</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Total Sale Price</th>
                        <th style="text-align: right;">Purchase Unit Price</th>
                        <th style="text-align: right;">Total Purchase Price</th>
                    </tr>
                </thead>
                <tbody>
        `;

        sales.forEach((saleItem, index) => {
            let price = parseFloat(saleItem.unit_price) || 0;
            let rowTotal = saleItem.quantity * price;
            let itemPurchasePrice = parseFloat(saleItem.item_actual_price) || 0;
            let rowActualTotal = saleItem.quantity * itemPurchasePrice; // Use saleItem.item_actual_price if available, otherwise use saleItem.item_actual_price;

            totalAmount += rowTotal;
            totalActualAmount += rowActualTotal;

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${saleItem.sale.invoice_no}</td>
                    <td class="text-nowrap">
                      ${new Date(saleItem.sale.date).toLocaleDateString('en-GB', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                })}
                    </td>
                    <td>${saleItem.item_name}</td>
                    <td style="text-align: right;">${(price).toFixed(2)}</td>
                    <td style="text-align: center;">${saleItem.quantity}</td>
                    <td style="text-align: right;">${rowTotal.toFixed(2)}</td>
                    <td style="text-align: right;">${itemPurchasePrice.toFixed(2)}</td>
                    <td style="text-align: right;">${rowActualTotal.toFixed(2)}</td>
                </tr>
            `;
        });

        html += `
            </tbody>
            <tfoot>
                <tr style="font-weight:bold;">
                    <td colspan="6" style="text-align:right;">Total Selling Price:</td>
                    <td style="text-align: right;">${totalAmount.toFixed(2)}</td>
                    <td style="text-align:right;">Total Purchase Price</td>
                    <td style="text-align: right;">${totalActualAmount.toFixed(2)}</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td colspan="6" style="text-align:right;">Gross Profit:</td>
                    <td style="text-align: right;">${(totalAmount - totalActualAmount).toFixed(2)}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            </table>
        `;

        $('#reportResult').html(html);


        // profiteDetailsTable datatable
        $('#profiteDetailsTable').each(function () {

            const tableEl = $(this);

            if ($.fn.DataTable.isDataTable(this)) {
                return;
            }

            const vehicleName = 'Spare parts Sale report';

            // ----------------------------
            // Get Footer Rows
            // ----------------------------
            const getFooterRows = () => {

                return tableEl.find('tfoot tr').map(function () {

                    return `
                                <tr>
                                    ${$(this).html()}
                                </tr>
                            `;

                }).get();
            };

            // ----------------------------
            // Get Column Widths
            // ----------------------------
            const getColGroup = () => {

                let colGroup = '<colgroup>';

                tableEl.find('thead th').each(function () {

                    colGroup += `
                                    <col style="width:${$(this).outerWidth()}px">
                                `;

                });

                colGroup += '</colgroup>';

                return colGroup;
            };

            // ----------------------------
            // DataTable
            // ----------------------------
            $(this).DataTable({
                dom: 'Bfrtip',
                paging: false,
                searching: false,
                info: false,
                ordering: false,

                buttons: [

                    // ====================================
                    // EXCEL
                    // ====================================
                    {
                        extend: 'excelHtml5',
                        title: vehicleName,
                        footer: false,

                        exportOptions: {
                            columns: ':visible'
                        },

                        customize: function (xlsx) {

                            const sheet = xlsx.xl.worksheets['sheet1.xml'];
                            const footerRows = getFooterRows();

                            if (!footerRows.length) return;

                            let lastRow = $('row', sheet).last().attr('r') * 1 || 1;

                            footerRows.forEach((rowHtml, index) => {

                                let rowIndex = lastRow + index + 1;

                                let rowXml = `<row r="${rowIndex}">`;

                                $(rowHtml).find('td').each(function (colIndex) {

                                    const text = $(this).text().trim();
                                    const cellRef = String.fromCharCode(65 + colIndex) + rowIndex;

                                    rowXml += `
                                                <c t="inlineStr" r="${cellRef}">
                                                    <is>
                                                        <t>${text}</t>
                                                    </is>
                                                </c>
                                            `;
                                });

                                rowXml += `</row>`;

                                $('sheetData', sheet).append(rowXml);
                            });
                        }
                    },

                    // ====================================
                    // PDF
                    // ====================================
                    {
                        extend: 'pdfHtml5',
                        title: vehicleName,
                        footer: false,

                        customize: function (doc) {

                            const footerRows = getFooterRows();

                            if (!footerRows.length) {
                                return;
                            }

                            const tableBody = doc.content[1].table.body;

                            // Total columns from header
                            const totalColumns = tableBody[0].length;

                            footerRows.forEach((rowHtml) => {

                                const rowData = [];

                                $(rowHtml).find('td, th').each(function () {

                                    const text = $(this).text().trim();

                                    const colspan = parseInt($(this).attr('colspan')) || 1;

                                    rowData.push({
                                        text: text,
                                        bold: true,
                                        colSpan: colspan
                                    });

                                    // Fill empty cells for colspan
                                    for (let i = 1; i < colspan; i++) {
                                        rowData.push({});
                                    }
                                });

                                // Ensure exact column count
                                while (rowData.length < totalColumns) {
                                    rowData.push({});
                                }

                                tableBody.push(rowData);
                            });
                        }
                    },

                    // ====================================
                    // PRINT
                    // ====================================
                    {
                        extend: 'print',
                        title: vehicleName,
                        footer: true,

                        customize: function (win) {

                            const $body = $(win.document.body);

                            const footerRows = getFooterRows();

                            $body.find('tfoot').remove();

                            if (footerRows.length) {

                                $body.append(`
                                            <div style="margin-top:20px;">
                                                <table class="table" style="width:100%; border-collapse:collapse;">
                                                    ${getColGroup()}

                                                    <tbody>
                                                        ${footerRows.join('')}
                                                    </tbody>
                                                </table>
                                            </div>
                                        `);
                            }
                        }
                    }
                ]
            });

        });
    }


</script>
@endsection
