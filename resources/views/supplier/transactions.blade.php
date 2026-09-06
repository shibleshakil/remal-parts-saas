@extends('layouts.app')
@section('title', 'Supplier Transaction List')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('supplier.index')
                <a href="{{ route ('supplier.index')}}" class="btn btn-primary">
                    {{ __('Supplier List') }} <i class="feather icon-eye"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Supplier') }}</li>
                    <li class="breadcrumb-item active">{{ __('Supplier Transactions') }}</li>
                </ol>
            </div>
        </div>
    </div>


    <div class="content-body">
        <!-- basic form table -->
        <section id="basic-form">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card rounded">
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <h4 class="text-center card-title">{{$supplier->name }}'s Transaction's</h4>
                                    <div class="row mb-2">
                                        <div class="col-md-12 d-flex gap-1">
                                            <form id="" action="{{ route('supplier.transactions',$supplier->id) }}" method="GET" class="row  gx-3 gy-2 align-items-center">
                                                <div class="col-12">
                                                    <div class="row">
                                                        <div id="" class="col-auto">
                                                            <fieldset class="form-group mb-0 d-inline">
                                                                <input id="startDay" type="date" name="from" class="form-control" placeholder="{{__('Start Day')}}"
                                                                value="{{  request('from') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                                            </fieldset>

                                                            <fieldset class="form-group mb-0 d-inline">
                                                                <input id="endDay" type="date" name="to" class="form-control" placeholder="{{__('End Day')}}"
                                                                value="{{  request('to') ?? \Carbon\Carbon::now()->startOfDay()->format('Y-m-d') }}">
                                                            </fieldset>
                                                        </div>

                                                        <div class="col-auto float-right">
                                                            <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <form action="{{ route('supplier.transactions',$supplier->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="data_reset" value="1">
                                                <button type="submit" class="btn btn-secondary">{{ __('Reset') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                <div class="">
                                    <table
                                        id='supplierTable'
                                        data-title="{{$supplier->name }}'s Transaction"
                                        data-from="{{ request('from') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')}}"
                                        data-to="{{ request('to') ?? date('Y-m-d') }}"
                                        class="table table-striped table-bordered" >
                                        <thead>
                                            <tr class="text-center">
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Type</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right">Paid</th>
                                                <th class="text-right">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $balance = $preDue->due ?? 0;
                                            @endphp
                                            <tr>
                                                <td></td>
                                                <td class="text-center">Opening Balance</td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right">{{ number_format(($balance), 2) }}</td>
                                            </tr>
                                            @foreach($data as $transaction)
                                            @php
                                                $balance + $transaction->final_amount - $transaction->paid_amount;
                                            @endphp
                                            <tr>
                                                <td class="text-nowrap text-center">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y')}}</td>
                                                <td class="text-center">{{ $transaction->type }}</td>
                                                <td class="text-right">{{ number_format($transaction->final_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($transaction->paid_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format(($balance), 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/ basic form table -->
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {

        const table = $('#supplierTable');

        const title = table.data('title');
        const fromDate = table.data('from');
        const toDate = table.data('to');

        const fullTitle = `${title} </br> <span style="font-size:20px;">From: ${fromDate} </br> To: ${toDate}</span> `;

        $('#supplierTable').DataTable({
            dom: 'Bfrtip',
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: fullTitle
                },
                {
                    extend: 'pdfHtml5',
                    title: fullTitle
                },
                {
                    extend: 'print',
                    title: fullTitle
                }
            ]
        });
    });
</script>
@endsection

