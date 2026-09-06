@extends('layouts.app')
@section('title', 'Purchase List')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('purchase.create')
               <a href="{{ route('purchase.create') }}" class="btn btn-primary">
                    {{ __('Add Purchase') }} <i class="feather icon-plus"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Purchase List') }}</li>
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
                                <h4 class="card-title">{{ __('Purchase List') }}</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('purchase.index') }}" method="get"
                                            class="row mb-2">

                                            {{-- Per Page Dropdown --}}
                                            <div class="col-1">
                                                <select name="per_page" class="form-select form-select-md" onchange="this.form.submit()">
                                                    <option value="10" {{ session('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="25" {{ session('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                    <option value="50" {{ session('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ session('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                </select>
                                            </div>

                                            {{-- Date Range --}}
                                            <div class="col-xl-3 col-md-6">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input class="form-control" type="date" name="from_date" value="{{ request('from_date') }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <input class="form-control" type="date" name="to_date" value="{{ request('to_date') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Search Box --}}
                                            <div class="col-lg-4 col-md-5 col-sm-8 col-12">
                                                <input type="search"
                                                    name="search"
                                                    class="form-control form-control-md"
                                                    placeholder="{{ __('Search by invoice number') }}"
                                                    value="{{ request('search') }}">
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-md btn-primary">
                                                    <i class="fa fa-search"></i> {{ __('Search') }}
                                                </button>
                                                <a href="{{ route('purchase.index') }}" class="btn btn-md btn-secondary">
                                                    <i class="fa fa-undo"></i> {{ __('Reset') }}
                                                </a>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Invoice No</th>
                                                <th>Purchase Date</th>
                                                <th>Supplier</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right">Due</th>
                                                <th>Attachments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $key => $sp)
                                            @php
                                                $total = $sp?->transactions?->sum('final_amount') ?? 0;
                                                $due = $sp?->transactions?->sum('final_amount') - $sp?->transactions?->sum('paid_amount');
                                                $paidAmount = $sp?->transactions?->sum('paid_amount');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <div class="dropdown-menu arrow">

                                                            @can('purchase.invoice')
                                                            <a href="{{ route('purchase.invoice', $sp->invoice_no) }}"
                                                                target="_blank"
                                                                class="dropdown-item" title="Invoice">
                                                                <i class="feather icon-file"></i> Show Invoice
                                                            </a>
                                                            @endcan

                                                            @can('sales.pay-due')
                                                                @if($total != $paidAmount)
                                                                <a href="{{ route('purchase.pay-due', $sp->id) }}" class="dropdown-item" title="Pay Due">
                                                                    <i class="feather icon-dollar-sign"></i> Due Pay
                                                                </a>
                                                                @endif
                                                            @endcan

                                                            @can('purchase.returnPurchase')
                                                            <a href="{{ route('purchase.returnPurchase', $sp->invoice_no) }}"
                                                               class="dropdown-item" title="Return">
                                                                <i class="feather icon-minus-circle"></i> Return / Exchange Parts
                                                            </a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $sp->invoice_no }}</td>
                                                <td class="text-nowrap">{{\Carbon\Carbon::parse($sp->purchase_date)->format('d M Y') }}</td>
                                                <td>{{ $sp->supplier?->name ?? 'Walking Supplier' }}</td>
                                                <td class="text-right">{{ number_format($total, 2) }}</td>
                                                <td class="text-right">{{ number_format($due, 2) }}</td>
                                                <td>
                                                    @if($sp->attachment)
                                                        @php
                                                            $attachments = json_decode($sp->attachment, true);
                                                        @endphp
                                                        @foreach($attachments as $attachment)
                                                            <a href="{{ asset('public/storage/purchase/' . $attachment) }}" target="_blank" class="btn btn-sm btn-info mb-1">
                                                                <i class="fa fa-file"></i> View
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    {{ $datas->links('pagination::bootstrap-5') }}
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

    });
</script>
@endsection
