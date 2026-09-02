@extends('layouts.app')
@section('title', 'Balance Transactions List')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('balances.create') }}" class="btn btn-primary">
                        {{ __('Balance In') }} <i class="feather icon-plus"></i>
                    </a>

                    <a href="{{ route('balances-out.create') }}" class="btn btn-danger">
                        {{ __('Balance Out') }} <i class="feather icon-minus"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Balance Transactions List') }}</li>
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
                                    <h4 class="card-title">Balance Transactions List</h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{ route('balances.index') }}" method="get"
                                                class="row my-1 gx-3 gy-2 align-items-center">
                                                <div class="col-auto">
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


                                                <div class="col-auto">
                                                    <div class="float-right">
                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <select class="form-control" name="type" id="type">
                                                                <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All</option>
                                                                <option value="balance_add" {{ $type == 'balance_add' ? 'selected' : '' }}>Balance In</option>
                                                                <option value="balance_out" {{ $type == 'balance_out' ? 'selected' : '' }}>Balance Out</option>
                                                            </select>
                                                        </fieldset>

                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="startDay" type="date" name="gross_profit_start_day" class="form-control" placeholder="{{__('Start Day')}}" value="{{  $startDate ?? '' }}">
                                                        </fieldset>

                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="endDay" type="date" name="gross_profit_end_day" class="form-control" placeholder="{{__('End Day')}}" value="{{  $endDate ?? '' }}">
                                                        </fieldset>

                                                        <button type="submit"
                                                            class="btn btn-primary">{{ __('Search') }}</button>
                                                        <a href="{{ route('balances.index') }}"
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
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th class="text-right">Amount</th>
                                                    <th>Payment Method</th>
                                                    <th style="width: 20%;">Bank Info</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($datas as $data)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            {{ $data->type === 'balance_add' ? 'Balance In' : ($data->type === 'balance_out' ? 'Balance Out' : '') }}
                                                        </td>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->transaction_date)->format('d M Y') }}</td>
                                                        <td class="text-right">{{ number_format($data->paid_amount, 2) }}</td>
                                                        <td>
                                                            {{
                                                                match($data->payment_method) {
                                                                    'bank_transfer' => 'Bank Transfer',
                                                                    'cash' => 'Cash',
                                                                    'cheque' => 'Cheque',
                                                                    'due' => 'Due',
                                                                    default => '',
                                                                }
                                                            }}
                                                        </td>
                                                       @php
                                                            $bankDetail = '';
                                                            if ($data->bank) {
                                                                $bankDetail = 'Name: ' . e($data->bank->bank_name) .
                                                                            '<br>Account No: ' . e($data->bank->account_no) .
                                                                            '<br>Account Name: ' . e($data->bank->account_name);
                                                            }
                                                        @endphp

                                                        <td>{!! $bankDetail !!}</td>
                                                        <td>
                                                            <!-- @if(\App\Helpers\CommonHelper::userHasAccess()) -->
                                                                @if ($data->type == 'balance_add')
                                                                    <a href="{{ route('balances.edit', $data->id) }}"class="btn btn-md btn-primary" title="Edit">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                @elseif($data->type == 'balance_out')
                                                                    <a href="{{ route('balances-out.edit', $data->id) }}"class="btn btn-md btn-primary" title="Edit">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                @endif


                                                                <button type="button" class="btn btn-md btn-danger"
                                                                    title="Delete"
                                                                    @if ($data->type == 'balance_add')
                                                                        onclick="deleteData('{{ route('balances.delete', $data->id) }}')">
                                                                    @elseif($data->type == 'balance_out')
                                                                        onclick="deleteData('{{ route('balances-out.delete', $data->id) }}')">
                                                                    @endif
                                                                    <i class="feather icon-trash"></i>
                                                                </button>
                                                            <!-- @endif -->

                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No data found</td>
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
