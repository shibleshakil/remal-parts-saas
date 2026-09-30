@extends('layouts.app')
@section('title', 'Return Sale')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="col-md-6 col-12 mb-1">

        </div>
        <div class="col-md-6 col-12">
            <div class="breadcrumb-wrapper float-right">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Setup') }}</li>
                    <li class="breadcrumb-item">{{ __('Sale') }}</li>
                    <li class="breadcrumb-item active">{{ __('Return Parts') }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="return-form">
            <div class="row card">
                <div class="col-12 card-body">
                    <h4 class="card-title">{{ __('Return Parts') }}</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <form action="{{ route('purchase.parts-return.index') }}" method="get"
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
                                    <fieldset class="form-group mb-0 d-inline">
                                        <input type="date" name="from" class="form-control" placeholder="{{__('Start Day')}}"
                                        value="{{  request('from') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                    </fieldset>

                                    <fieldset class="form-group mb-0 d-inline">
                                        <input type="date" name="to" class="form-control" placeholder="{{__('End Day')}}"
                                        value="{{  request('to') ?? \Carbon\Carbon::now()->startOfDay()->format('Y-m-d') }}">
                                    </fieldset>
                                </div>

                                <div class="col-auto">
                                    <div class="d-flex">
                                        <input type="search" name="invoice_no" class="form-control mr-1" placeholder="{{__('Invoice No')}}" value="{{ request('invoice_no') }}">
                                        <input type="search" name="parts_name" class="form-control" placeholder="{{__('Parts Name')}}" value="{{ request('parts_name') }}">
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <div class="float-right">
                                        <button type="submit" class="btn btn-primary">{{__('Search')}}</button>
                                        <a href="{{ route('purchase.parts-return.index') }}" class="btn btn-secondary">{{__('Reset')}}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="parts-return-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Parts Name') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-right">{{ __('Quantity') }}</th>
                                    <th class="text-right">{{ __('Total Refund') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($partsReturnLists as $idex => $partsReturn)
                                <tr>
                                    <td>{{ $partsReturnLists->firstItem() + $idex }}</td>
                                    <td>{{ $partsReturn->invoice_no?? '' }}</td>
                                    <td>{{ $partsReturn->item_name }}</td>
                                    <td>{{ $partsReturn->created_at->format('d M Y') }}</td>
                                    <td class="text-right">{{ $partsReturn->quantity }}</td>
                                    <td class="text-right">{{ number_format($partsReturn->quantity * $partsReturn->unit_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if ($partsReturnLists->isNotEmpty())
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="4" class="text-right">Total</td>
                                    <td class="text-right">{{ number_format($totalQuantity ?? 0, 2) }}</td>
                                    <td class="text-right">{{ number_format($totalReturnAmount ?? 0, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    <div>
                        {{ $partsReturnLists->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('script')
<script>

</script>
@endsection
