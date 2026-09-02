@extends('layouts.app')
@section('title', 'Customer')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('customer.create') }}" class="btn btn-primary">
                        {{ __('Add Customer') }} <i class="feather icon-plus"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Customer') }}</li>
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
                                    <h4 class="card-title">Customer List</h4>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{ route('customer.index') }}" method="get"
                                            class="row my-1 gx-3 gy-2 align-items-center">
                                                <div class="col-xl-5 col-sm-3 col-auto">
                                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                                        <option value="10" {{ session('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                        <option value="25" {{ session('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                        <option value="50" {{ session('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                        <option value="100" {{ session('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                    </select>
                                                </div>


                                                <div class="col-xxl-5 col-xl-4 col-md-4 col-sm-5 col-auto">
                                                    <input type="search" name="search" class="form-control" placeholder="{{__('Search')}} (Name, Phone, Email, Address)" value="{{ request('search') }}">
                                                </div>

                                                <div class="col-auto">
                                                    <div class="float-right">
                                                        <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                                        <a href="{{ route('customer.index') }}" class="btn btn-secondary">{{__('Reset')}}</a>
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
                                                    <th>Email</th>
                                                    <th>Address</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($datas->count())
                                                    <?php $sl = 0; ?>
                                                    @foreach ($datas as $data)
                                                        <tr>
                                                            <td>{{ ++$sl }}</td>
                                                            <td>
                                                                <div class="btn-group mr-1 mb-1">
                                                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        Action
                                                                    </button>
                                                                    <div class="dropdown-menu arrow">
                                                                        {{-- Edit Customer --}}
                                                                        @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                                        <a href="{{ route('customer.edit', [$data->id]) }}"
                                                                            class="dropdown-item" title="Edit Customer">
                                                                            <i class="feather icon-edit-2"></i> Edit Customer
                                                                        </a>
                                                                        @endif
                                                                        {{-- Pay Due (Optional, links to payment page if any sale has dues) --}}
                                                                        @if ($data->total_due > 0)
                                                                            <a href="{{ route('customer.pay', [$data->sales->first()?->id ?? 0]) }}"
                                                                                class="dropdown-item" title="Pay Due">
                                                                                <i class="feather icon-dollar-sign"></i> Pay Due
                                                                            </a>
                                                                        @endif


                                                                        {{-- Delete Customer --}}
                                                                        @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                                        <button type="button" class="dropdown-item" title="Delete"
                                                                            onclick="deleteData('{{ route('customer.delete', [$data->id]) }}')">
                                                                            <i class="feather icon-trash"></i> Delete
                                                                        </button>
                                                                        @endif
                                                                        <a href="{{ route('customer.transactions', $data->id) }}"
                                                                            class="dropdown-item" title="Transaction">
                                                                            <i class="feather icon-list"></i> Transaction
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $data->name }}</td>
                                                            <td>{{ $data->phone }}</td>
                                                            <td>{{ $data->email }}</td>
                                                            <td>{{ $data->address }}</td>
                                                            <td
                                                                class="{{ $data->total_due > 0 ? 'text-danger' : 'text-success' }}">
                                                                {{ number_format($data->total_due, 2) }}
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                @endif
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
