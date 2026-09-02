@extends('layouts.app')
@section('title', 'Sale')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    {{ __('Add Sale') }} <i class="feather icon-plus"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Sale') }}</li>
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
                                <h4 class="card-title">Sale List</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('sales.index') }}" method="get"
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
                                                        <label for="search" class="visually-hidden">{{__('Search')}} (Invoice Number)</label>
                                                        <input type="search" id="search" name="search" class="form-control" placeholder="" value="{{ request('search') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-auto">
                                                <div class="float-right">
                                                    <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">{{__('Reset')}}</a>
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
                                                <th>Invoice No</th>
                                                <th>Customer Name</th>
                                                <th>Date</th>
                                                <th class="text-right">Total Sale Price</th>
                                                <!-- <th class="text-right">Discount Amount</th>
                                                    <th class="text-right">Final Price</th> -->
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Due Amount</th>
                                                <th>Payment Status</th>
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
                                                            @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                            <a href="{{ route('sales.edit', $data->id) }}"
                                                                class="dropdown-item" title="Edit">
                                                                <i class="feather icon-edit-2"></i> Edit
                                                            </a>
                                                            @endif

                                                            <a href="{{ route('sales.show', $data->id) }}"
                                                                target="_blank"
                                                                class="dropdown-item" title="Invoice">
                                                                <i class="feather icon-file"></i> Show Invoice
                                                            </a>

                                                            <a href="{{ route('sales.returnForm', $data->id) }}" class="dropdown-item" title="Return">
                                                                <i class="feather icon-minus-circle"></i> Return / Exchange Parts
                                                            </a>

                                                            @if($data->final_amount > $data->paid_amount)
                                                            <a href="{{ route('sales.pay-due', $data->id) }}" class="dropdown-item" title="Pay Due">
                                                                <i class="feather icon-dollar-sign"></i> Due Pay
                                                            </a>
                                                            @endif
                                                            @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                            <button type="button" class="dropdown-item" title="Delete"
                                                                onclick="deleteData('{{ route('sales.delete', $data->id) }}')">
                                                                <i class="feather icon-trash"></i> Delete
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <!-- Modal -->
                                                    <div class="modal fade text-left" id="partsDetails{{ $data->id }}" role="dialog" aria-labelledby="partsDetailslabel{{ $data->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title" id="partsDetailslabel{{ $data->id }}">
                                                                        <strong>Invoice: {{ $data->invoice_no }} </strong> Products Info
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
                                                <td>{{ $data->invoice_no}}</td>
                                                <td>{{ $data->customer->name ?? 'Walking Customer' }}</td>
                                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->date)->format('d M Y') }}</td>
                                                <!-- <td class="text-right">{{ number_format($data->total_cost, 2) }}</td>
                                                        <td class="text-right">{{ number_format($data->discount_amount, 2) }}
                                                            {{ $data->discount_type === 'percentage' ? '%' : '' }}</td> -->
                                                <td class="text-right">{{ number_format($data->final_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($data->paid_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($data->final_amount - $data->paid_amount, 2) }}</td>
                                                <td class="text-center">
                                                    @if ($data->final_amount > $data->paid_amount)
                                                    <span class="badge badge-danger">Due</span>
                                                    @elseif ($data->final_amount == $data->paid_amount)
                                                    <span class="badge badge-success">Paid</span>
                                                    @else
                                                    <span class="badge badge-success">Paid</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="5" class="text-right">
                                                    <strong>Total Sale Price:</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($totalFinalAmount, 2) }}</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($totalPaidAmount, 2) }}</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($totalFinalAmount - $totalPaidAmount, 2) }}</strong>
                                                </td>
                                                <td colspan="1"></td>
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
