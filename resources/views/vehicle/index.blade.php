@extends('layouts.app')
@section('title', 'Vehicle')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('vehicle.create') }}" class="btn btn-primary">
                        {{ __('Add Vehicle') }} <i class="feather icon-plus"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Vehicle') }}</li>
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
                                    <h4 class="card-title">Vehicle List</h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{ route('vehicle.index') }}" method="get"
                                                class="row my-1 gx-3 gy-2 align-items-center">
                                                <div class="col-1">
                                                    <select name="per_page" class="form-select"
                                                        onchange="this.form.submit()">
                                                        <option value="10"
                                                            {{ session('per_page', 10) == 10 ? 'selected' : '' }}>10
                                                        </option>
                                                        <option value="25"
                                                            {{ session('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                        <option value="50"
                                                            {{ session('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                        <option value="100"
                                                            {{ session('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                    </select>
                                                </div>
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
                                                <div class="col-xxl-3 col-xl-4 col-md-4 col-sm-5 col-auto">
                                                    <input type="search" name="search" class="form-control"
                                                        placeholder="{{ __('Search') }} (UID, Name, Vehicle No, Parts Namee.)"
                                                        value="{{ request('search') }}">
                                                </div>
                                                <div class="col-auto">
                                                    <div class="float-right">
                                                        <button type="submit"
                                                            class="btn btn-primary">{{ __('Search') }}</button>
                                                        <a href="{{ route('vehicle.index') }}"
                                                            class="btn btn-secondary">{{ __('Reset') }}</a>
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
                                                    <th>UID</th>
                                                    <th>Description</th>
                                                    <th>Vehicle No.</th>
                                                    <th>Date</th>
                                                    <th class="text-right">Purchase Price</th>
                                                    <th class="text-right">Total Sale Price</th>
                                                    <th class="text-right">Sale Items Purchase Price</th>
                                                    <th class="text-right">Net Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas as $index => $data)
                                                    @php
                                                        $purchase_price = (float) ($data->total_purchase_price ?? 0);
                                                        $actual_purchase_price =
                                                            (float) ($data->sale_items_purchase_price ?? 0);
                                                        $sale_price = (float) ($data->total_sale_amount ?? 0);
                                                        $balance = $actual_purchase_price - $purchase_price;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $datas->firstItem() + $index }}</td>
                                                        <td>
                                                            <div class="btn-group mr-1 mb-1">
                                                                <button type="button"
                                                                    class="btn btn-secondary dropdown-toggle"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    Action
                                                                </button>
                                                                <div class="dropdown-menu arrow">
                                                                    <button type="button" class="dropdown-item"
                                                                        data-toggle="modal"
                                                                        data-target="#partsDetails{{ $data->id }}">
                                                                        <i class="fa fa-eye"></i> Parts Info
                                                                    </button>
                                                                    <button type="button" class="dropdown-item"
                                                                        data-toggle="modal"
                                                                        data-target="#availablePartsDetails{{ $data->id }}">
                                                                        <i class="fa fa-th-list"></i> Available Parts Info
                                                                    </button>
                                                                    @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                                    <a href="{{ route('vehicle.edit', $data->id) }}"
                                                                        class="dropdown-item" title="Edit">
                                                                        <i class="fa fa-pencil"></i> Edit
                                                                    </a>
                                                                    @endif
                                                                    @if ($data->invoice_no)
                                                                        <a href="{{ route('vehicle.invoice', $data->invoice_no) }}"
                                                                            target="_blank" class="dropdown-item"
                                                                            title="Invoice Print">
                                                                            <i class="fa fa-file"></i> Invoice
                                                                        </a>
                                                                    @endif
                                                                    @if (!empty($data->barcode))
                                                                        <a href="{{ route('vehicle.print_barcode', $data->id) }}"
                                                                            class="dropdown-item" title="Print Barcode"
                                                                            target="_blank">
                                                                            <i class="fa fa-barcode"></i> Print Barcode
                                                                        </a>
                                                                    @endif
                                                                    @if (!empty($data->qr_code))
                                                                        <a href="{{ route('vehicle.print_qrcode', $data->id) }}"
                                                                            class="dropdown-item" title="Print QRcode"
                                                                            target="_blank">
                                                                            <i class="fa fa-qrcode"></i> Print QRcode
                                                                        </a>
                                                                    @endif
                                                                    @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                                    <button type="button" class="dropdown-item"
                                                                        title="Delete"
                                                                        onclick="deleteData('{{ route('vehicle.delete', $data->id) }}')">
                                                                        <i class="fa fa-trash"></i> Delete
                                                                    </button>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Parts Info Modal -->
                                                            <div class="modal fade text-left"
                                                                id="partsDetails{{ $data->id }}" role="dialog"
                                                                aria-labelledby="partsDetailslabel{{ $data->id }}"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title"
                                                                                id="partsDetailslabel{{ $data->id }}">
                                                                                <strong>{{ $data->name }}
                                                                                    ({{ $data->uid }}) : </strong> Parts
                                                                                Info
                                                                            </h4>
                                                                            <button type="button" class="close"
                                                                                data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="row">
                                                                                <div class="col-md-12 table-responsive">
                                                                                    @if ($data->details && $data->details->count())
                                                                                        <table
                                                                                            id="datatable2-{{ $data->id }}"
                                                                                            class="table table-sm table-bordered mb-0 datatable2"
                                                                                            data-vehicle-name="{{ $data->name }} ({{ $data->uid }})">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th>SL</th>
                                                                                                    <th>Name</th>
                                                                                                    <th>UID</th>
                                                                                                    <th class="text-right">
                                                                                                        Unit Price</th>
                                                                                                    <th class="text-right">
                                                                                                        Available</th>
                                                                                                    <th class="text-right">
                                                                                                        Sold</th>
                                                                                                    <th class="text-right">
                                                                                                        Total</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @foreach ($data->details as $part)
                                                                                                    <tr>
                                                                                                        <td>{{ $loop->iteration }}
                                                                                                        </td>
                                                                                                        <td>{{ $part->parts_name ?? 'N/A' }}
                                                                                                        </td>
                                                                                                        <td>{{ $part->uid ?? 'N/A' }}
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-right">
                                                                                                            {{ number_format($part->purchaseLines->first()->unit_price ?? 0, 2) }}
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-right">
                                                                                                            {{ $part->available_quantity ?? 0 }}
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-right">
                                                                                                            {{ $part->sold_quantity ?? 0 }}
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-right">
                                                                                                            {{ $part->total_quantity ?? 0 }}
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    @else
                                                                                        <span class="text-muted">No
                                                                                            parts</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button"
                                                                                class="btn grey btn-outline-secondary"
                                                                                data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Available Parts Info Modal  -->
                                                            <div class="modal fade text-left"
                                                                id="availablePartsDetails{{ $data->id }}" role="dialog"
                                                                aria-labelledby="availablePartsDetailslabel{{ $data->id }}"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title"
                                                                                id="availablePartsDetailslabel{{ $data->id }}">
                                                                                <strong>{{ $data->name }}
                                                                                    ({{ $data->uid }}) : </strong> Available Parts Info
                                                                            </h4>
                                                                            <button type="button" class="close"
                                                                                data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="row">
                                                                                <div class="col-md-12 table-responsive">
                                                                                    @php
                                                                                    $stockBalance = 0;
                                                                                    $index = 1;

                                                                                    $filteredDetails = $data->details->where('available_quantity', '!=', 0);
                                                                                    @endphp

                                                                                    @if ($filteredDetails && $filteredDetails->count())
                                                                                    <table id="datatable2-{{ $data->id }}"
                                                                                            class="table table-sm table-bordered mb-0 datatable2"
                                                                                            data-vehicle-name="{{ $data->name }} ({{ $data->uid }}) : Available Parts Info">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>SL</th>
                                                                                                <th>Name</th>
                                                                                                <th>UID</th>
                                                                                                <th class="text-right">Unit Price</th>
                                                                                                <th class="text-right">Available Qty</th>
                                                                                                <th class="text-right">Stock Balance</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @foreach ($filteredDetails as $part)
                                                                                            @php
                                                                                            $unit_price = $part->purchaseLines->first()->unit_price ?? 0;
                                                                                            $qty = $part->available_quantity ?? 0;
                                                                                            $sbalance = $unit_price * $qty;
                                                                                            $stockBalance += $sbalance;
                                                                                            @endphp
                                                                                            <tr>
                                                                                                <td>{{ $index++ }}</td>
                                                                                                <td>{{ $part->parts_name ?? 'N/A' }}</td>
                                                                                                <td>{{ $part->uid ?? 'N/A' }}</td>
                                                                                                <td class="text-right">
                                                                                                    {{ number_format($unit_price, 2) }}
                                                                                                </td>
                                                                                                <td class="text-right">
                                                                                                    {{ $qty }}
                                                                                                </td>
                                                                                                <td class="text-right">
                                                                                                    {{ number_format($sbalance, 2) }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            @endforeach
                                                                                        </tbody>
                                                                                        <tfoot>
                                                                                            <tr>
                                                                                                <td></td>
                                                                                                <td></td>
                                                                                                <td></td>
                                                                                                <td class="text-right">
                                                                                                    <strong>Total</strong>
                                                                                                </td>
                                                                                                <td class="text-right">
                                                                                                    <strong>{{ $filteredDetails->sum('available_quantity') }}</strong>
                                                                                                </td>
                                                                                                <td class="text-right">
                                                                                                    <strong>{{ number_format($stockBalance, 2) }}</strong>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tfoot>
                                                                                    </table>
                                                                                    @else
                                                                                    <span class="text-muted">No available parts</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button"
                                                                                class="btn grey btn-outline-secondary"
                                                                                data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $data->uid }}</td>
                                                        <td>{{ $data->name }} || {{ $data->model }}</td>
                                                        <td>{{ $data->chasis_no }}</td>
                                                        <td class="text-nowrap">
                                                            {{ \Carbon\Carbon::parse($data->date)->format('d M Y') }}</td>
                                                        <td class="text-right">{{ number_format($purchase_price, 2) }}
                                                        </td>
                                                        <td class="text-right">{{ number_format($sale_price, 2) }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($actual_purchase_price, 2) }}</td>
                                                        <td class="text-right {{ $balance < 0 ? 'text-danger' : '' }}">
                                                            {{ number_format($balance, 2) }}
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
            $('.datatable2').each(function() {
                var vehicleName = $(this).data('vehicle-name') || 'Vehicle Parts List';
                // Check if this table is already initialized
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        dom: 'Bfrtip',
                        paging: true,
                        searching: true,
                        info: true,
                        ordering: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                title: vehicleName,
                                footer: true
                            },
                            {
                                extend: 'pdfHtml5',
                                title: vehicleName,
                                footer: true,
                            },
                            {
                                extend: 'print',
                                title: vehicleName,
                                footer: true
                            }
                        ]
                    });
                }
            });

        });
    </script>
@endsection
