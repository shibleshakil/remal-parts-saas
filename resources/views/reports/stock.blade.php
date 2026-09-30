@extends('layouts.app')
@section('title', 'Stock Report')
@section('css')
<style>
    table.dataTable tfoot th,
    table.dataTable tfoot td {
        padding: 8px 10px;
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
                    <li class="breadcrumb-item active">{{ __('Stock Report') }}</li>
                </ol>
            </div>
        </div>
    </div>
    <div id="stock-card" class="d-flex flex-wrap justify-content-between mt-3">
        <div class="card flex-fill" style="min-width: 180px;">
            <div class="card-content">
                <div class="card-body">
                    <div class="media d-flex">
                        <div class="media-body text-left">
                            <h3 class="danger font-weight-bold" id="parts-stock-quantity"></h3>
                            <span>Parts Stock Quantity</span>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-social-dropbox danger font-large-1 float-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card flex-fill" style="min-width: 180px;">
            <div class="card-content">
                <div class="card-body">
                    <div class="media d-flex">
                        <div class="media-body text-left">
                            <h3 class="success font-weight-bold" id="parts-stock-balance"></h3>
                            <span>Parts Stock Balance</span>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-credit-card success font-large-1 float-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card flex-fill" style="min-width: 180px;">
            <div class="card-content">
                <div class="card-body">
                    <div class="media d-flex">
                        <div class="media-body text-left">
                            <h3 id="vehicle-stock-quantity" class="info font-weight-bold"></h3>
                            <span>Vehicle Stock Quantity</span>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-social-dropbox info font-large-1 float-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card flex-fill" style="min-width: 180px;">
            <div class="card-content">
                <div class="card-body">
                    <div class="media d-flex">
                        <div class="media-body text-left">
                            <h3 id="vehicle-stock-balance" class="warning font-weight-bold"></h3>
                            <span>Vehicle Stock Balance</span>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-credit-card warning font-large-1 float-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card flex-fill" style="min-width: 180px;">
            <div class="card-content">
                <div class="card-body">
                    <div class="media d-flex">
                        <div class="media-body text-left">
                            <h3 id="total-stock-balance" class="primary font-weight-bold"></h3>
                            <span>Total Stock Balance</span>
                        </div>
                        <div class="align-self-center">
                            <i class="icon-credit-card primary font-large-1 float-right"></i>
                        </div>
                    </div>
                </div>
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
                                <h4 class="card-title">Stock Report</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('report.stock') }}" method="get"
                                            class="row my-1 align-items-center">

                                            <div class="col-12">
                                                <div class="d-flex justify-content-between report-filter">
                                                    <fieldset class="form-group w-25">
                                                        <Select name="type" class="form-control" onchange="this.form.submit()">
                                                            <option value="parts" {{ $type == 'parts' ? 'selected' : '' }}>{{__('Parts')}}</option>
                                                            <option value="vehicle" {{ $type == 'vehicle' ? 'selected' : '' }}>{{__('Vehicles')}}</option>
                                                        </Select>
                                                    </fieldset>
                                                    <fieldset class="form-group">
                                                        <input type="search" name="uid" class="form-control" placeholder="UID" value="{{ request('uid') }}">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <input type="search" name="parts_name" class="form-control" placeholder="Parts Name" value="{{ request('parts_name') }}">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <input type="search" name="car_name" class="form-control" placeholder="Vahicle Name" value="{{ request('car_name') }}">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <input type="search" name="car_model" class="form-control" placeholder="Model" value="{{ request('car_model') }}">
                                                    </fieldset>
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                    </div>

                                                    <div class="form-group">
                                                        <a href="{{ route('report.stock') }}" class="btn btn-secondary">{{__('Reset')}}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                                <div class="">
                                    @if($type == 'parts')
                                    <table id="stockTable" class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%;">SL</th>
                                                <th style="width: 50%;">Parts Name -- UID</th>
                                                <th style="width: 20%;">Car -- Model</th>
                                                <th style="width: 10%;">Quantity</th>
                                                <th style="width: 10%;">Current Stock Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $totalAmount = 0;
                                            $totalQuantity = 0;
                                            @endphp
                                            @foreach ($datas as $key => $sp)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $sp->parts_name }} -- {{$sp->uid}}</td>
                                                <td>{{ $sp->car_name }} -- {{ $sp->car_model }}</td>
                                                <td class="text-right"><span style="font-weight: bolder;">{{ $sp->total_quantity }}</span></td>
                                                <td class="text-right"><span style="font-weight: bolder;">{{ number_format($sp->total_price, 2) }}</span></td>
                                            </tr>
                                            @php
                                            $totalAmount += $sp->total_price;
                                            $totalQuantity += $sp->total_quantity;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-right">Total Current Stock Quantity And Balance:</th>
                                                <th class="text-right">{{ $totalQuantity }}</th>
                                                <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                            </tr>
                                    </table>
                                    @else
                                        @php
                                            // Group all parts by car (name + model)
                                            $grouped = collect($datas)->groupBy(function ($item) {
                                                return $item->car_name . '|' . $item->car_model;
                                            });

                                            $grandTotalAmount = 0;
                                            $grandTotalQuantity = 0;
                                            $sl = 1;
                                        @endphp

                                        <table class="table table-striped table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th>SL</th>
                                                    <th>Car Name</th>
                                                    <th>Model</th>
                                                    <th class="text-right">Stock Balance</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach ($grouped as $key => $parts)
                                                    @php
                                                        [$carName, $carModel] = explode('|', $key);
                                                        $modalId = Str::slug($carName . '-' . $carModel);

                                                        $vehicleTotalAmt = $parts->sum('total_price');
                                                        // Per-vehicle totals
                                                        $vehicleTotalQty = $parts->sum('total_quantity');
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $sl++ }}</td>
                                                        <td>{{ $carName }}</td>
                                                        <td>{{ $carModel }}</td>
                                                        <td class="text-right">{{number_format($vehicleTotalAmt, 2)}}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                data-toggle="modal" data-target="#partsModal{{ $modalId }}">
                                                                <i class="fa fa-eye"></i> All Parts Info ({{ $parts->count() }} )
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-success"
                                                                data-toggle="modal" data-target="#availablePartsModal{{ $modalId }}">
                                                                <i class="fa fa-eye"></i> Available Parts Quantity ({{ $parts->where('total_quantity', '>', 0)->sum('total_quantity') }})
                                                            </button>

                                                            <!-- All parts info modal  -->
                                                            <div class="modal fade" id="partsModal{{ $modalId }}" tabindex="-1" role="dialog"
                                                                aria-labelledby="partsModalLabel{{ $modalId }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-light">
                                                                            <h5 class="modal-title" id="partsModalLabel{{ $modalId }}">
                                                                                <strong>{{ $carName }}</strong> — Model: {{ $carModel }}
                                                                            </h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>

                                                                        <div class="modal-body">
                                                                            @if ($parts->count())
                                                                                <div class="">
                                                                                    <table class="table table-sm table-bordered mb-0 datatable2" data-vehicle-name="{{ $carName }} ({{ $carModel }}) Parts List">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th style="width: 60%;">Parts Name -- UID</th>
                                                                                                <th class="text-right">Quantity</th>
                                                                                                <th class="text-right">Current Stock Balance</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @foreach ($parts as $sp)
                                                                                                <tr>
                                                                                                    <td style="width: 60%;">{{ $sp->parts_name }} -- {{ $sp->uid }}</td>
                                                                                                    <td class="text-right"><b>{{ $sp->total_quantity }}</b></td>
                                                                                                    <td class="text-right"><b>{{ number_format($sp->total_price, 2) }}</b></td>
                                                                                                </tr>
                                                                                            @endforeach
                                                                                        </tbody>
                                                                                        <tfoot>
                                                                                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                                                                <th class="text-right">Total:</th>
                                                                                                <th class="text-right">{{ $vehicleTotalQty }}</th>
                                                                                                <th class="text-right">{{ number_format($vehicleTotalAmt, 2) }}</th>
                                                                                            </tr>
                                                                                        </tfoot>
                                                                                    </table>
                                                                                </div>
                                                                            @else
                                                                                <p class="text-muted">No parts available for this vehicle.</p>
                                                                            @endif
                                                                        </div>

                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                                Close
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Available parts info modal -->
                                                            <div class="modal fade" id="availablePartsModal{{ $modalId }}" tabindex="-1" role="dialog"
                                                                aria-labelledby="availablePartsModalLabel{{ $modalId }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-light">
                                                                            <h5 class="modal-title" id="availablePartsModalLabel{{ $modalId }}">
                                                                                <strong>{{ $carName }}</strong> — Model: {{ $carModel }}
                                                                            </h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>

                                                                        <div class="modal-body">
                                                                            @if ($parts->count())
                                                                                <div class="">
                                                                                    <table class="table table-sm table-bordered mb-0 datatable2" data-vehicle-name="{{ $carName }} ({{ $carModel }}) Parts List">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th style="width: 60%;">Parts Name -- UID</th>
                                                                                                <th class="text-right">Quantity</th>
                                                                                                <th class="text-right">Current Stock Balance</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @foreach ($parts as $sp)
                                                                                                @if($sp->total_quantity > 0) {{-- Show only parts with available stock --}}
                                                                                                <tr>
                                                                                                    <td style="width: 60%;">{{ $sp->parts_name }} -- {{ $sp->uid }}</td>
                                                                                                    <td class="text-right"><b>{{ $sp->total_quantity }}</b></td>
                                                                                                    <td class="text-right"><b>{{ number_format($sp->total_price, 2) }}</b></td>
                                                                                                </tr>
                                                                                                @endif
                                                                                            @endforeach
                                                                                        </tbody>
                                                                                        <tfoot>
                                                                                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                                                                <th class="text-right">Total:</th>
                                                                                                <th class="text-right">{{ $vehicleTotalQty }}</th>
                                                                                                <th class="text-right">{{ number_format($vehicleTotalAmt, 2) }}</th>
                                                                                            </tr>
                                                                                        </tfoot>
                                                                                    </table>
                                                                                </div>
                                                                            @else
                                                                                <p class="text-muted">No parts available for this vehicle.</p>
                                                                            @endif
                                                                        </div>

                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                                Close
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    {{-- Add to global totals --}}
                                                    @foreach ($parts as $sp)
                                                        @php
                                                            $grandTotalAmount += $sp->total_price;
                                                            $grandTotalQuantity += $sp->total_quantity;
                                                        @endphp
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                 <tr>
                                                    <th style="text-align:right;">Total Current Stock Quantity:</th>
                                                    <th style="text-align:right;">{{ $grandTotalQuantity }}</th>
                                                    <th style="text-align:right;">Total Current Stock Balance:</th>
                                                    <th style="text-align:right;">{{ $grandTotalAmount }}</th>
                                                </tr>
                                        </tfoot>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#stockTable').DataTable({
            dom: 'Bfrtip', // show buttons
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [{
                    extend: 'excelHtml5',
                    title: 'Stock Report',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Stock Report',
                    footer: false, // disable default repeated footer
                    customize: function (doc) {

                        var totalQty = $('#stockTable tfoot th').eq(1).text();
                        var totalAmount = $('#stockTable tfoot th').eq(2).text();

                        doc.content.push({
                            table: {
                                widths: ['*', '*', '*', 'auto', 'auto'], // 5 columns
                                body: [
                                    [
                                        { text: 'Total Current Stock Quantity And Balance:', colSpan: 3, alignment: 'left', bold: true },
                                        {}, // required empty cells for colspan
                                        {},
                                        { text: totalQty, alignment: 'left', bold: true },
                                        { text: totalAmount, alignment: 'left', bold: true }
                                    ]
                                ]
                            },
                            margin: [0, 20, 0, 0]
                        });
                    }
                },
                {
                    extend: 'print',
                    title: 'Stock Report',
                    footer: true,
                    customize: function (win) {
                        var $body = $(win.document.body);

                        var footerHtml = $('#stockTable tfoot').html();

                        $body.find('tfoot').remove();

                        $body.append(`
                            <table class="table" style="width:100%;">
                                ${footerHtml}
                            </table>
                        `);
                    }
                }
            ]
        });
    });


    $('.datatable2').each(function() {
        var vehicleName = $(this).data('vehicle-name') || 'Vehicle Parts List';
        var table = $(this);
        // Check if this table is already initialized
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                dom: 'Bfrtip',
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                buttons: [{
                        extend: 'excelHtml5',
                        title: vehicleName,
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                            footer: true
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: vehicleName,
                         customize: function (doc) {

                            var table = $('.datatable2').first();
                            var row = [];

                            table.find('tfoot tr').first().find('th,td').each(function () {
                                row.push($(this).text());
                            });

                            doc.content.push({
                                table: {
                                    widths: '*',
                                    body: [row]
                                },
                                margin: [0, 10, 0, 0]
                            });
                        }
                    },
                    {
                        extend: 'print',
                        title: vehicleName,
                         footer: true,
                        customize: function (win) {
                            var $body = $(win.document.body);

                            // ✅ use stored table reference
                            var footerHtml = table.find('tfoot').html();

                            $body.find('tfoot').remove();

                            $body.append(`
                                <table class="table" style="width:100%;">
                                    ${footerHtml}
                                </table>
                            `);
                        }
                    }
                ]
            });
        }
    });


    $.ajax({
        url: `{{ route('stockSummary') }}`,
        type: 'GET',
        dataType: 'json',
        cache: false,
        beforeSend: function() {
            // Show loader before sending the request
            blockStatsCard();
        },
        success: function(resp) {
            console.log(resp);

            // try common response shapes safely:
            var total = 0;
            if (resp) {
                if (resp.status === 'success' && resp.data) {
                    totalStockBalance = parseFloat(resp.data[0].parts_stock_balance ?? 0) + parseFloat(resp.data[0].vehicle_stock_balance ?? 0);
                    $('#vehicle-stock-balance').text(parseFloat(resp.data[0].vehicle_stock_balance ?? 0).toFixed(2));
                    $('#vehicle-stock-quantity').text(parseFloat(resp.data[0].vehicle_stock_quantity ?? 0).toFixed(2));
                    $('#parts-stock-balance').text(parseFloat(resp.data[0].parts_stock_balance ?? 0).toFixed(2));
                    $('#parts-stock-quantity').text(parseFloat(resp.data[0].parts_stock_quantity ?? 0).toFixed(2));
                    $('#total-stock-balance').text(parseFloat(totalStockBalance).toFixed(2));
                } else {
                    console.warn('response structure unexpected, check network tab.');
                }
            }

        },
        error: function(xhr, status, err) {
            // window.location.reload();
            console.error(status, err, xhr.responseText);
            alert('An error occurred while fetching stock summary. Please refresh the page.');
            window.location.reload();
        },
        complete: function() {
            // always hide loader and show body
            unblockStatsCard();
        }
    });

    function blockStatsCard() {
        // Select all cards under #stock-card
        var $stockCards = $("#stock-card .card");

        // Loop through each card and block it individually
        $stockCards.each(function() {
            $(this).block({
                message: '<div class="feather icon-refresh-cw icon-spin font-medium-2"></div>',
                overlayCSS: {
                    backgroundColor: '#fff',
                    opacity: 0.8,
                    cursor: 'wait'
                },
                css: {
                    border: 0,
                    padding: 0,
                    backgroundColor: 'transparent'
                }
            });
        });
    }


    function unblockStatsCard() {
        $("#stock-card .card").each(function() {
            $(this).unblock();
        });
    }
</script>
@endsection
