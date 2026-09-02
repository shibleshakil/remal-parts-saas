@extends('layouts.app')
@section('title', 'Spare Parts')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('spare-parts.create') }}" class="btn btn-primary">
                    {{ __('Add Spare Parts') }} <i class="feather icon-plus"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Spare Parts') }}</li>
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
                                <h4 class="card-title">Spare Parts List</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('spare-parts.index') }}" method="get"
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
                                                    placeholder="{{ __('Search (UID, Parts Name, Car name/model)') }}"
                                                    value="{{ request('search') }}">
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-md btn-primary">
                                                    <i class="fa fa-search"></i> {{ __('Search') }}
                                                </button>
                                                <a href="{{ route('spare-parts.index') }}" class="btn btn-md btn-secondary">
                                                    <i class="fa fa-undo"></i> {{ __('Reset') }}
                                                </a>
                                                <a href="{{ route('spare-parts.export', request()->query()) }}" class="btn btn-md btn-success">
                                                    <i class="fa fa-file-excel"></i> {{ __('Export') }}
                                                </a>
                                                <a href="{{ route('spare-parts.print', request()->query()) }}" target="_blank" class="btn btn-md btn-info">
                                                    <i class="fa fa-print"></i> {{ __('Print') }}
                                                </a>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Actions</th>
                                                <th>UID</th>
                                                <th>Parts Name</th>
                                                <th>Car</th>
                                                <th>Quantity (Total)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $key => $sp)
                                            <tr>
                                                <td>{{ $datas->firstItem() + $key }}</td>
                                                <td>
                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <div class="dropdown-menu arrow">
                                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#purchaseDetails{{ $sp->id }}">
                                                                <i class="fa fa-eye"></i> Purchase Details
                                                            </button>
                                                            @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                            <a href="{{ route('spare-parts.edit', $sp->id) }}"
                                                                class="dropdown-item" title="Edit">
                                                                <i class="feather icon-edit-2"></i> Edit
                                                            </a>
                                                            @endif

                                                            @if (!empty($sp->barcode))
                                                            <a href="{{ route('spare-parts.print_barcode', $sp->id) }}"
                                                                class="dropdown-item" title="Print Barcode"
                                                                target="_blank">
                                                                <i class="fa fa-barcode"></i> Print Barcode
                                                            </a>
                                                            @endif

                                                            @if (!empty($sp->qr_code))
                                                            <a href="{{ route('spare-parts.print_qrcode', $sp->id) }}"
                                                                class="dropdown-item" title="Print QRcode"
                                                                target="_blank">
                                                                <i class="fa fa-qrcode"></i> Print QRcode
                                                            </a>
                                                            @endif
                                                            @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                            <button type="button" class="dropdown-item" title="Delete"
                                                                onclick="deleteData('{{ route('spare-parts.delete', $sp->id) }}')">
                                                                <i class="feather icon-trash"></i> Delete
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <!-- Modal -->
                                                    <div class="modal fade text-left" id="purchaseDetails{{ $sp->id }}" role="dialog" aria-labelledby="purchaseDetailslabel{{ $sp->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title" id="purchaseDetailslabel{{ $sp->id }}">
                                                                        <strong>{{ $sp->parts_name }} ({{ $sp->uid }}) : </strong> Purchase Details Info
                                                                    </h4>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12 table-responsive">
                                                                            @if ($sp->purchaseLines && $sp->purchaseLines->count())
                                                                            <table class="table table-sm table-bordered mb-0">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>SL</th>
                                                                                        <th>Quantity</th>
                                                                                        <th class="text-right">Unit Price</th>
                                                                                        <th class="text-right">Total Price</th>
                                                                                        <th>Date</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach ($sp->purchaseLines as $line)
                                                                                    <tr>
                                                                                        <td>{{ $loop->iteration }}</td>
                                                                                        <td>{{ $line->quantity }}</td>
                                                                                        <td class="text-right">{{ number_format($line->unit_price, 2) }}
                                                                                        </td>
                                                                                        <td class="text-right">{{ number_format($line->quantity * $line->unit_price, 2) }}
                                                                                        </td>
                                                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($line->purchase_date)->format('d M Y') }}
                                                                                        </td>
                                                                                    </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                            @else
                                                                            <span class="text-muted">No purchase records</span>
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
                                                <td>{{ $sp->uid }}</td>
                                                <td>{{ $sp->parts_name }}</td>
                                                <td>{{ $sp->car_name }}||{{ $sp->car_model }}</td>
                                                <td>{{ $sp->parts_quantity }}</td>
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
