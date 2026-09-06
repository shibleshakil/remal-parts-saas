@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('supplier.create')
                <a href="{{ route('supplier.create') }}" class="btn btn-primary">
                    {{ __('Add Supplier') }} <i class="feather icon-plus"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Supplier') }}</li>
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
                                <h4 class="card-title">Supplier List</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('supplier.index') }}" method="get"
                                            class="row my-1 gx-3 gy-2 align-items-center">
                                            <div class="col-xl-5 col-sm-3 col-auto">
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

                                            <div class="col-xxl-5 col-xl-4 col-md-4 col-sm-5 col-auto">
                                                <input type="search" name="search" class="form-control"
                                                    placeholder="{{ __('Search') }} (Name, Phone, Email, Address)"
                                                    value="{{ request('search') }}">
                                            </div>

                                            <div class="col-auto">
                                                <div class="float-right">
                                                    <button type="submit"
                                                        class="btn btn-primary">{{ __('Search') }}</button>
                                                    <a href="{{ route('supplier.index') }}"
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
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Total Purchase</th>
                                                <th>Total Paid</th>
                                                <th>Total Due</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($datas as $index => $data)
                                            <tr>
                                                <td>{{ $datas->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Action
                                                        </button>
                                                        <div class="dropdown-menu arrow">

                                                            @can('supplier.edit')
                                                            <a href="{{ route('supplier.edit', $data->id) }}"
                                                                class="dropdown-item" title="Edit">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            @endcan

                                                            @can('supplier.payForm')
                                                                @if( $data->due_amount > 0 )
                                                                <a href="{{ route('supplier.payForm', $data->id) }}"
                                                                    class="dropdown-item" title="Pay">
                                                                    <i class="feather icon-dollar-sign"></i> Pay
                                                                </a>
                                                                @endif
                                                            @endcan


                                                            @can('supplier.delete')
                                                            <button type="button" class="dropdown-item" title="Delete"
                                                                onclick="deleteData('{{ route('supplier.delete', $data->id) }}')">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </button>
                                                            @endcan

                                                            @can('supplier.transactions')
                                                            <a href="{{ route('supplier.transactions', $data->id) }}" class="dropdown-item" title="Transections">
                                                                <i class="feather icon-list"></i>
                                                                Transections
                                                            </a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $data->name }}</td>
                                                <td class="text-nowrap">{{ $data->phone }}</td>
                                                <td>{{ $data->total_purchase_amount }}</td>
                                                <td>{{ $data->total_paid }}</td>
                                                <td class="text-danger">{{ $data->due_amount }}</td>
                                                <td>{{ $data->email }}</td>
                                                <td>{{ $data->address }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No data found</td>
                                            </tr>
                                            @endforelse
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
