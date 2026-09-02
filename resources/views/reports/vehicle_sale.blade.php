@extends('layouts.app')
@section('title', 'Vehicle Sales Report')
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
                    <li class="breadcrumb-item active">{{ __('Vehicle Sales Report') }}</li>
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
                                <h4 class="card-title">Vehicle Sales Report</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('report.vehicleSale') }}" method="post" class="row my-1 align-items-center">
                                            @csrf
                                            <div class="col-6">
                                                <div class="d-flex report-filter">

                                                    <div class="form-group w-50">
                                                        <select name="vehicle_id" class="form-control select2">
                                                            <option value="">All</option>
                                                            @foreach ($vehicles as $vehicle)
                                                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                                    {{ $vehicle->name }} - {{ $vehicle->uid }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                    </div>

                                                    <div class="form-group">
                                                        <a href="{{ route('report.vehicleSale') }}" class="btn btn-secondary">{{__('Reset')}}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="">
                                    <table id="vehicleSalesTable" class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Car - UID</th>
                                                <th>Model</th>
                                                <th class="text-right">Total Sale Amount</th>
                                                <th>Parts List</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($vehicleSales as $key => $vehicle)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $vehicle->name }} - {{$vehicle->uid}}</td>
                                                <td>{{ $vehicle->model }}</td>
                                                <td class="text-right"><span style="font-weight: bolder;">{{ number_format(array_sum($vehicle->saleDetails->pluck('cost')->toArray()), 2) }}</span></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#partsDetails{{ $vehicle->id }}">
                                                        <i class="fa fa-eye"></i> Details
                                                    </button>

                                                    <!-- Parts Details Modal -->
                                                    <div class="modal fade text-left" id="partsDetails{{ $vehicle->id }}" role="dialog" aria-labelledby="partsDetailslabel{{ $vehicle->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title" id="partsDetailslabel{{ $vehicle->id }}">
                                                                        <strong>{{ $vehicle->name }} - {{ $vehicle->uid }} </strong> Parts Info
                                                                    </h4>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12 table-responsive">
                                                                            @if ($vehicle->saleDetails && count($vehicle->saleDetails))
                                                                            <table class="table table-sm table-bordered mb-0 datatable2" data-vehicle-name="{{ $vehicle->name }} - {{ $vehicle->uid }} Parts List">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th style="width: 200px;">Name - UID</th>
                                                                                        <th>Invoice Number</th>
                                                                                        <th>Salling Date</th>
                                                                                        <th>Unit Price</th>
                                                                                        <th>Qty</th>
                                                                                        <th>Total</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach ($vehicle->saleDetails as $item)
                                                                                    <tr>
                                                                                        <td>{{ $item->partsDetail?->parts_name ?? $item->item_name }} {{ $item->partsDetail?->uid ? ' - ' : '' }} {{ $item->partsDetail->uid ?? '' }}</td>
                                                                                        <td>{{ $item->sale?->invoice_no }}</td>
                                                                                        <td class="text-nowrap">{{ optional($item->sale)->date ? (\Carbon\Carbon::parse($item->sale->date)->format('d M Y')) : '' }}</td>
                                                                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                                                                        <td>{{ $item->quantity }}</td>
                                                                                        <td>{{ number_format($item->cost, 2) }}</td>
                                                                                    </tr>
                                                                                    @endforeach
                                                                                </tbody>

                                                                                <tfoot>
                                                                                        <tr>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th></th>
                                                                                            <th class="text-right">Total:</th>
                                                                                            <th>{{ array_sum($vehicle->saleDetails->pluck('quantity')->toArray()) }}</th>
                                                                                            <th>
                                                                                                {{ number_format(array_sum($vehicle->saleDetails->pluck('cost')->toArray()), 2) }}
                                                                                            </th>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                            </table>
                                                                            @else
                                                                            <span class="text-muted">No products</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot id="vehicleSalesTableFooter">
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    <th class="text-right">Grand Total</th>
                                                    <th class="text-right">
                                                        {{ number_format($vehicleSales->reduce(function ($carry, $vehicle) {
                                                            return $carry + array_sum($vehicle->saleDetails->pluck('cost')->toArray());
                                                        }, 0), 2) }}
                                                    </th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                    </table>
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
        $('#vehicleSalesTable').DataTable({
            dom: 'Bfrtip', // show buttons
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Stock Report',
                    footer: true,
                    exportOptions: {
                        columns: ':visible',
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Stock Report',
                    exportOptions: {
                        columns: [0, 1, 2, 3] // only export these column indexes
                    },
                    footer: true,
                },
                {
                    extend: 'print',
                    title: 'Stock Report',
                    exportOptions: {
                        columns: [0, 1, 2, 3] // only export these column indexes
                    },
                    columnsfooter: true,
                    customize: function (win) {
                        var $body = $(win.document.body);

                        var footerHtml = $('#vehicleSalesTableFooter').html();

                        $body.find('tfoot').remove();

                        $body.append(`
                            <table class="table" style="width:100%;">
                                <tfoot style="font-weight: bold;">
                                    ${footerHtml}
                                </tfoot>
                            </table>
                        `);
                    }
                }
            ]
        });

        $('.datatable2').each(function() {
            var vehicleName = $(this).data('vehicle-name') || 'Vehicle Parts List';
            var tableEl = $(this);

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
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: vehicleName,
                            footer: true,
                        },
                        {
                            extend: 'print',
                            title: vehicleName,
                            footer: true,
                            customize: function (win) {

                                var $body = $(win.document.body);
                                var footerHtml = tableEl.find('tfoot tr').html();

                                // get column widths
                                var colWidths = [];
                                tableEl.find('thead th').each(function () {
                                    colWidths.push($(this).outerWidth());
                                });

                                var colGroup = '<colgroup>';
                                colWidths.forEach(function (w) {
                                    colGroup += `<col style="width:${w}px">`;
                                });
                                colGroup += '</colgroup>';

                                $body.find('tfoot').remove();

                                if (footerHtml) {
                                    $body.append(`
                                        <div>
                                            <table class="table" style="width:100%; border-collapse:collapse;">
                                                ${colGroup}
                                                <tr style="font-weight:bold;">
                                                    ${footerHtml}
                                                </tr>
                                            </table>
                                        </div>
                                    `);
                                }
                            }
                        }
                    ]
                });
            }
        });
    });
</script>
@endsection
