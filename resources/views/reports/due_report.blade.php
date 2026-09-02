@extends('layouts.app')
@section('title', 'Due Report')
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
            <!-- <h3 class="content-header-title">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        {{ __('Add Sale') }} <i class="feather icon-plus"></i>
                    </a>
                </h3> -->
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Reports') }}</li>
                    <li class="breadcrumb-item active">{{ __('Due Report') }}</li>
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
                                <h4 class="card-title">Due Report</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('report.dueReport') }}" method="get"
                                            class="row my-1 align-items-center">

                                            <div class="col-12">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <fieldset class="form-group">
                                                        <input type="date" name="date" class="form-control" placeholder="Date" value="{{ request('date') }}">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <input type="search" name="invoice" class="form-control" placeholder="Invoice" value="{{ request('invoice') }}">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <select class="form-control select2" name="customer_id">
                                                            <option value="">Select Customer</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </fieldset>

                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                    </div>

                                                    <div class="form-group">
                                                        <a href="{{ route('report.dueReport') }}" class="btn btn-secondary">{{__('Reset')}}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="">
                                    <table id="dueTable" class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Invoice</th>
                                                <th>Customer</th>
                                                <th>Sale Date</th>
                                                <th class="text-right">Total Amount</th>
                                                <th class="text-right">Due Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $totalAmount = 0;
                                            $totalDue = 0;
                                            @endphp
                                            @foreach ($dueSales as $key => $sale)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $sale->invoice_no }}</td>
                                                <td>{{ $sale->customer?->name?? 'Walking Customer' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y')}}</td>
                                                <td class="text-right"><span style="font-weight: bolder;">{{ number_format($sale->final_amount, 2) }}</span></td>
                                                <td class="text-right"><span style="font-weight: bolder;">{{ number_format($sale->final_amount - $sale->paid_amount, 2) }}</span></td>
                                            </tr>
                                            @php
                                            $totalAmount += $sale->final_amount;
                                            $totalDue += $sale->final_amount - $sale->paid_amount;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="">
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th class="text-right">Total:</th>
                                                <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                                <th class="text-right">{{ number_format($totalDue, 2) }}</th>
                                            </tr>
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
        $('#dueTable').DataTable({
            dom: 'Bfrtip', // show buttons
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [{
                    extend: 'excelHtml5',
                    title: 'Due Report',
                    footer: true,
                    exportOptions: {
                        columns: ':visible',
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Due Report',
                    footer: true,
                },
                {
                    extend: 'print',
                    title: 'Due Report',
                    footer: true,
                    customize: function (win) {
                        var $body = $(win.document.body);

                        var footerHtml = $('#dueTable tfoot').html();

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
                        title: vehicleName
                    },
                    {
                        extend: 'pdfHtml5',
                        title: vehicleName
                    },
                    {
                        extend: 'print',
                        title: vehicleName
                    }
                ]
            });
        }
    });
</script>
@endsection
