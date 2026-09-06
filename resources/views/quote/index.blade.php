@extends('layouts.app')
@section('title', 'Quote')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('quotes.create')
                <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                    {{ __('Add Quote') }} <i class="feather icon-plus"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Quote') }}</li>
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
                                <h4 class="card-title">Quote List</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('quotes.index') }}" method="get"
                                            class="row my-1 gx-3 gy-2 align-items-center">
                                            <div class="col-auto">
                                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                                    <option value="10" {{ session('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="25" {{ session('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                    <option value="50" {{ session('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ session('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                </select>
                                            </div>


                                            <div class="col-auto">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-group mb-0">
                                                        <div class="d-flex">
                                                            <div class="form-group mr-2">
                                                                <label for="FromDate" class="visually-hidden">From Date</label>
                                                                <input type="date" id="FromDate" name="FromDate" class="form-control" value="{{ request('FromDate') }}">
                                                            </div>
                                                            <div class="form-group mr-2">
                                                                <label for="ToDate" class="visually-hidden">To Date</label>
                                                                <input type="date" id="ToDate" name="ToDate" class="form-control" value="{{ request('ToDate') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="search" class="visually-hidden">{{__('Search')}} (Quote Number)</label>
                                                        <input type="search" id="search" name="search" class="form-control" placeholder="" value="{{ request('search') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-auto">
                                                <div class="float-right">
                                                    <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                    <a href="{{ route('quotes.index') }}" class="btn btn-secondary">{{__('Reset')}}</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sl</th>
                                                <th>Action</th>
                                                <th>Status</th>
                                                <th>Quote No</th>
                                                <th>Customer Name</th>
                                                <th>Date</th>
                                                <th class="text-right">Total Quote Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $index => $data)
                                            <tr>
                                                <td>{{ $datas->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <div class="dropdown-menu arrow">
                                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#partsDetails{{ $data->id }}">
                                                                <i class="fa fa-eye"></i> Products
                                                            </button>

                                                            @if($data->status == 'pending')
                                                                @can('quotes.convertToSale')
                                                                <a href="{{ route('quotes.convertToSale', $data->id) }}"
                                                                    class="dropdown-item" title="Convert to Sale">
                                                                    <i class="feather icon-shopping-cart"></i> Convert to Sale
                                                                </a>
                                                                @endcan

                                                                @can('quotes.edit')
                                                                <a href="{{ route('quotes.edit', $data->id) }}"
                                                                    class="dropdown-item" title="Edit">
                                                                    <i class="feather icon-edit-2"></i> Edit
                                                                </a>
                                                                @endcan

                                                            @else
                                                                @if (isset($data->sale_id))
                                                                    @can('sales.show')
                                                                    <a href="{{ route('sales.show', $data->sale_id) }}"
                                                                        class="dropdown-item" title="Sale Invoice" target="_blank">
                                                                        <i class="feather icon-shopping-cart"></i> Sale Invoice
                                                                    </a>
                                                                    @endcan
                                                                @endif
                                                            @endif
                                                            @can('quotes.show')
                                                            <a href="{{ route('quotes.show', $data->id) }}"
                                                                target="_blank"
                                                                class="dropdown-item" title="Quotation">
                                                                <i class="feather icon-file"></i> Print Quotation
                                                            </a>
                                                            @endcan

                                                            @can('quotes.delete')
                                                            <button type="button" class="dropdown-item" title="Delete"
                                                                onclick="deleteData('{{ route('quotes.delete', $data->id) }}')">
                                                                <i class="feather icon-trash"></i> Delete
                                                            </button>
                                                            @endcan

                                                        </div>
                                                    </div>
                                                    <!-- Modal -->
                                                    <div class="modal fade text-left" id="partsDetails{{ $data->id }}" role="dialog" aria-labelledby="partsDetailslabel{{ $data->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title" id="partsDetailslabel{{ $data->id }}">
                                                                        <strong>Quote: {{ $data->quote_no }} </strong> Products Info
                                                                    </h4>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12 table-responsive">
                                                                            @if ($data->items && count($data->items))
                                                                            <table class="table table-sm table-bordered mb-0">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Name</th>
                                                                                        <th>Unit Price</th>
                                                                                        <th>Qty</th>
                                                                                        <th>Total</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach ($data->items as $prod)
                                                                                    <tr>
                                                                                        <td>{{ $prod->item_name ?? 'N/A' }}</td>
                                                                                        <td>{{ number_format($prod->unit_price, 2) }}
                                                                                        </td>
                                                                                        <td>{{ $prod->quantity }}</td>
                                                                                        <td>{{ number_format($prod->unit_price * $prod->quantity, 2) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                    @endforeach
                                                                                </tbody>
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
                                                <td>{{ $data->status }}</td>
                                                <td>{{ $data->quote_no}}</td>
                                                <td>{{ $data->customer->name ?? 'Walking Customer' }}</td>
                                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->date)->format('d M Y') }}</td>
                                                <!-- <td class="text-right">{{ number_format($data->total_cost, 2) }}</td>
                                                        <td class="text-right">{{ number_format($data->discount_amount, 2) }}
                                                            {{ $data->discount_type === 'percentage' ? '%' : '' }}</td> -->
                                                <td class="text-right">{{ number_format($data->final_amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="6" class="text-right">
                                                    <strong>Total Quote Price:</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($totalFinalAmount, 2) }}</strong>
                                                </td>
                                            </tr>
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
        // DataTable initialization (assuming it's set up in layouts.app or elsewhere)
    });
</script>
@endsection
