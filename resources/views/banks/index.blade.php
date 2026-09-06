@extends('layouts.app')
@section('title', 'Bank')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @can('banks.create')
                    <a href="{{ route('banks.create') }}" class="btn btn-primary">
                        {{ __('Add Bank') }} <i class="feather icon-plus"></i>
                    </a>
                    @endcan

                    @can('banks.trash')
                    <a href="{{route('banks.trash')}}" class="btn btn-warning">
                        {{ __('View Trashed') }}
                    </a>
                    @endcan
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Bank') }}</li>
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
                                    <h4 class="card-title">Bank List</h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{ route('banks.index') }}" method="get"
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
                                                        placeholder="{{ __('Search') }} (Bank Name, Account Name, Account No, Branch)"
                                                        value="{{ request('search') }}">
                                                </div>

                                                <div class="col-auto">
                                                    <div class="float-right">
                                                        <button type="submit"
                                                            class="btn btn-primary">{{ __('Search') }}</button>
                                                        <a href="{{ route('banks.index') }}"
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
                                                    <th>Bank Name</th>
                                                    <th>Account Name</th>
                                                    <th>Account No</th>
                                                    <th>Branch</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($datas as $data)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $data->bank_name }}</td>
                                                        <td>{{ $data->account_name }}</td>
                                                        <td>{{ $data->account_no }}</td>
                                                        <td>{{ $data->branch }}</td>
                                                        <td>
                                                            @can('banks.edit')
                                                            <a href="{{ route('banks.edit', $data->id) }}"
                                                                class="btn btn-md btn-primary" title="Edit">
                                                                <i class="feather icon-edit"></i>
                                                            </a>
                                                            @endcan

                                                            @can('banks.delete')
                                                            <button type="button" class="btn btn-md btn-danger"
                                                                title="Delete"
                                                                onclick="deleteData('{{ route('banks.delete', $data->id) }}')">
                                                                <i class="feather icon-trash"></i>
                                                            </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No data found</td>
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
