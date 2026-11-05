@extends('layouts.app')
@section('title', 'Balance Statement')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('balance.create')
                <a href="{{ route('balances.create') }}" class="btn btn-primary">
                    {{ __('Balance In') }} <i class="feather icon-plus"></i>
                </a>
                @endcan

                @can('balance-out.create')
                <a href="{{ route('balances-out.create') }}" class="btn btn-danger">
                    {{ __('Balance Out') }} <i class="feather icon-minus"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Balance Statement') }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="content-body">
        <!-- basic form table -->
        <section id="basic-form">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card rounded">
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <h4 class="card-title">Balance Statement</h4>
                                <div class="row mb-2">
                                    <div class="col-md-12 d-flex gap-1">
                                        <form id="" action="{{ route('balance-statement.index') }}" method="GET" class="row  gx-3 gy-2 align-items-center">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div id="" class="col-auto">
                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="startDay" type="date" name="from" min="2025-12-01" class="form-control" placeholder="{{__('Start Day')}}"
                                                            value="{{  request('from') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                                        </fieldset>

                                                        <fieldset class="form-group mb-0 d-inline">
                                                            <input id="endDay" type="date" name="to" min="2025-12-01" class="form-control" placeholder="{{__('End Day')}}"
                                                            value="{{  request('to') ?? \Carbon\Carbon::now()->startOfDay()->format('Y-m-d') }}">
                                                        </fieldset>
                                                    </div>

                                                    <div class="col-auto float-right">
                                                        <button type="submit" class="btn btn-primary">{{__('Filter')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="{{ route('balance-statement.index') }}" method="GET" class="d-inline">
                                            <input type="hidden" name="data_reset" value="1">
                                            <button type="submit" class="btn btn-secondary">{{ __('Reset') }}</button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Mini Statement Summary --}}
                                @php
                                    $totalIn = 0.0;
                                    $totalOut = 0.0;
                                    foreach ($data as $t) {
                                        if (in_array($t->type, $balanceInTypes)) {
                                            $totalIn += (float) $t->paid_amount;
                                        } else {
                                            $totalOut += (float) $t->paid_amount;
                                        }
                                    }
                                    $currentBalance = $previousBalance + $totalIn - $totalOut;
                                    $fromLabel = request('from') ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
                                    $toLabel = request('to') ?? \Carbon\Carbon::now()->format('Y-m-d');
                                @endphp
                                <div class="d-flex gap-1 mb-2 mini-summery">
                                    <div class="mb-1 shadow">
                                        <div class="card h-100 mb-0 border-left-success">
                                            <div class="card-body py-1 px-2">
                                                <p class="h4 mb-0">Total In</p>
                                                <div class="font-weight-bold text-success h2 mb-0">{{ number_format($totalIn, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-1 shadow">
                                        <div class="card h-100 mb-0 border-left-danger">
                                            <div class="card-body py-1 px-2">
                                                <p class="h4 mb-0">Total Out</p>
                                                <div class="font-weight-bold text-danger h2 mb-0">{{ number_format($totalOut, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-1 shadow">
                                        <div class="card h-100 mb-0 border-left-secondary">
                                            <div class="card-body py-1 px-2" title="{{ $fromLabel }} to {{ $toLabel }}: Total In {{ number_format($totalIn, 2) }} - Total Out {{ number_format($totalOut, 2) }} = {{ number_format($totalIn - $totalOut, 2) }}">
                                                <p class="h4 mb-0">Balance</p>
                                                <div class="font-weight-bold text-secondary h2 mb-0">{{ number_format($totalIn - $totalOut, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-1 shadow">
                                        <div class="card h-100 mb-0 border-left-info">
                                            <div class="card-body py-1 px-2">
                                                <p class="h4 mb-0">Previous Balance</p>
                                                <div class="font-weight-bold text-info h2 mb-0">{{ number_format($previousBalance, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-1 shadow">
                                        <div class="card h-100 mb-0 border-left-primary">
                                            <div class="card-body py-1 px-2" title="Previous Balance {{ number_format($previousBalance, 2) }} (before {{ $fromLabel }}) + Total In {{ number_format($totalIn, 2) }} - Total Out {{ number_format($totalOut, 2) }} = {{ number_format($currentBalance, 2) }}">
                                                <p class="h4 mb-0">Current Balance</p>
                                                <div class="font-weight-bold text-primary h2 mb-0">{{ number_format($currentBalance, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="">
                                    <table
                                        id='balanceStatementTable'
                                        data-from="{{ request('from') ?? date('Y-m-01') }}"
                                        data-to="{{ request('to') ?? date('Y-m-d') }}"
                                        class="table table-striped table-bordered reportTable">
                                        <thead>
                                            <tr class="text-center">
                                                <th class="text-left">Date</th>
                                                <th class="text-left">Purpose</th>
                                                <th class="text-left">Client / Supplier</th>
                                                <th class="text-left">Payment Method</th>
                                                <th class="text-right">Balance In</th>
                                                <th class="text-right">Balance Out</th>
                                                <th class="text-right">Balance</th>
                                            </tr>
                                        </thead>
                                         <tbody>
                                            @php
                                                $runningBalance = $previousBalance;
                                            @endphp

                                            <tr>
                                                <td class="text-left">Previous Balance</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right">
                                                    <b>{{ number_format($runningBalance, 2) }}</b>
                                                </td>
                                            </tr>

                                            @foreach($data as $transaction)

                                            @php
                                                    $isBalanceIn = in_array($transaction->type, $balanceInTypes);

                                                    $debit = $isBalanceIn
                                                        ? $transaction->paid_amount
                                                        : 0;

                                                    $credit = $isBalanceIn
                                                        ? 0
                                                        : $transaction->paid_amount;

                                                    $runningBalance += $isBalanceIn
                                                        ? $transaction->paid_amount
                                                        : -$transaction->paid_amount;
                                            @endphp

                                            <tr>
                                                    <td class="text-nowrap">
                                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                                                    </td>
                                                    <td>
                                                        {{ $typeLabels[$transaction->type] ?? ucwords(str_replace('_', ' ', $transaction->type)) }} {{ $transaction->invoice_number }}
                                                    </td>

                                                    <td class="text-nowrap">
                                                        {{ $transaction->customer?->name ?? $transaction->supplier?->name ?? '—' }}
                                                    </td>



                                                    <td class="text-nowrap">
                                                        {{ $transaction->payment_method ? ucwords(str_replace('_', ' ', $transaction->payment_method)) : '—' }}
                                                    </td>

                                                    <td class="text-right">
                                                        {{ number_format($debit, 2) }}
                                                    </td>

                                                    <td class="text-right">
                                                        {{ number_format($credit, 2) }}
                                                    </td>

                                                    <td class="text-right">
                                                        <b>{{ number_format($runningBalance, 2) }}</b>
                                                    </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th class="text-right">Total</th>
                                                <th class="text-right">{{ number_format($totalIn, 2) }}</th>
                                                <th class="text-right">{{ number_format($totalOut, 2) }}</th>
                                                <th class="text-right">{{ number_format($currentBalance, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/ basic form table -->
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(document).ready(function () {
    const table = $('#balanceStatementTable');
    const fromDate = table.data('from');
    const toDate = table.data('to');

    const fileTitle = 'Balance Statement'; // SAFE file name
    const displayTitle = `Balance Statement From: ${fromDate} To: ${toDate}`;

    $('#balanceStatementTable').DataTable({
        dom: 'Bfrtip',
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        buttons: [
            {
                extend: 'excelHtml5',
                title: fileTitle,
                messageTop: displayTitle,
                footer: true
            },
            {
                extend: 'pdfHtml5',
                title: fileTitle,
                messageTop: displayTitle,
                footer: true
            },
            {
                extend: 'print',
                title: 'Balance Statement',
                messageTop: displayTitle,
                footer: true,
                // Browsers repeat <tfoot> on every printed page - move it to
                // a normal last body row so the totals appear only at the end.
                customize: function (win) {
                    $(win.document.body).find('table').each(function () {
                        var $table = $(this);
                        $table.find('tfoot tr').appendTo($table.find('tbody'));
                        $table.find('tfoot').remove();
                    });
                }
            }
        ]
    });
});

</script>


@endsection

