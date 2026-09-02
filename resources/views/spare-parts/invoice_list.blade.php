@extends('layouts.app')
@section('title', 'Invoice List')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Spare Parts') }}</li>
                    <li class="breadcrumb-item active">{{ __('Invoice List') }}</li>
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
                                <h4 class="card-title">{{ __('Invoice List') }}</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('spare-parts.invoiceList') }}" method="get"
                                            class="row g-2 align-items-center justify-content-between mb-2">

                                            {{-- Per Page Dropdown --}}
                                            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                                <select name="per_page" class="form-select form-select-md" onchange="this.form.submit()">
                                                    <option value="10" {{ session('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="25" {{ session('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                    <option value="50" {{ session('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ session('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                </select>
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
                                            <div class="col-lg-6 col-md-12 col-sm-12 col-12 d-flex justify-content-md-end justify-content-center flex-wrap gap-1">
                                                <button type="submit" class="btn btn-md btn-primary">
                                                    <i class="fa fa-search"></i> {{ __('Search') }}
                                                </button>
                                                <a href="{{ route('spare-parts.index') }}" class="btn btn-md btn-secondary">
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
                                                <th></th>
                                                <th>Invoice No</th>
                                                <th>Purchase Date</th>
                                                <th>Parts Name</th>
                                                <th>Parts UID</th>
                                                <th>Supplier</th>
                                                <th class="text-right">Unit Price</th>
                                                <th class="text-right">Quantity</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $key => $sp)
                                            <tr>
                                                @php
                                                $unit_price = $sp->unit_price ?? 0;
                                                $qty = ( $sp->quantity + $sp->sold_quantity) ?? 0;
                                                $amount = $unit_price * $qty;
                                                @endphp
                                                <td>
                                                    <a href="{{ route('spare-parts.invoice', $sp->invoice_no) }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                </td>
                                                <td>{{ $sp->invoice_no }}</td>
                                                <td class="text-nowrap">{{ $sp->purchase_date }}</td>
                                                <td>{{ $sp->vehicleDetail?->parts_name }}</td>
                                                <td>{{ $sp->vehicleDetail?->uid }}</td>
                                                <td>{{ $sp->supplier?->name ?? 'Walking Supplier' }}</td>
                                                <td class="text-right">{{ number_format($unit_price, 2) }}</td>
                                                <td class="text-right">{{ $qty }}</td>
                                                <td class="text-right">{{ number_format($amount, 2) }}</td>
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
