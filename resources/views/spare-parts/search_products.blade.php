@extends('layouts.app')
@section('title', 'Product Search')

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
                    <li class="breadcrumb-item active">{{ __('Product Search') }}</li>
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
                                <h4 class="card-title">Products List</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('productSearch.index') }}" method="get"
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
                                                    placeholder="{{ __('Search By Product UID/Name)') }}"
                                                    value="{{ request('search') }}">
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="col-lg-6 col-md-12 col-sm-12 col-12 d-flex justify-content-md-end justify-content-center flex-wrap gap-1">
                                                <button type="submit" class="btn btn-md btn-primary">
                                                    <i class="fa fa-search"></i> {{ __('Search') }}
                                                </button>
                                                <a href="{{ route('productSearch.index') }}" class="btn btn-md btn-secondary">
                                                    <i class="fa fa-undo"></i> {{ __('Reset') }}
                                                </a>
                                                
                                                @can('productSearch.export')
                                                <a href="{{ route('productSearch.export', request()->query()) }}" class="btn btn-md btn-success">
                                                    <i class="fa fa-file-excel"></i> {{ __('Export') }}
                                                </a>
                                                @endcan

                                                @can('productSearch.print')
                                                <a href="{{ route('productSearch.print', request()->query()) }}" target="_blank" class="btn btn-md btn-info">
                                                    <i class="fa fa-print"></i> {{ __('Print') }}
                                                </a>
                                                @endcan
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
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
                                                <td>{{ $sp->uid }}</td>
                                                <td>{{ $sp->parts_name }}</td>
                                                <td>
                                                    @if ($sp->vehicle)
                                                    {{ $sp->vehicle?->name ." (". $sp->vehicle?->uid .")" }}||{{ $sp->vehicle?->model }}
                                                    @else
                                                    {{ $sp->car_name }}||{{ $sp->car_model }}
                                                    @endif
                                                </td>
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
