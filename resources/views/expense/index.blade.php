@extends('layouts.app')
@section('title', 'Expense')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('expense.create') }}" class="btn btn-primary">
                        {{ __('Add Expense') }} <i class="feather icon-plus"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Expense') }}</li>
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
                                    <h4 class="card-title">Expense List</h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{ route('expense.index') }}" method="get"
                                                class="row my-1 gx-3 gy-2 align-items-center">
                                                <div class=" col-auto">
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

                                                </div>
                                            </div>

                                                <div class="col-auto">
                                                    <input type="search" name="search" class="form-control"
                                                        placeholder="{{ __('Search') }} (Expense Type)"
                                                        value="{{ request('search') }}">
                                                </div>

                                                <div class="col-auto">
                                                    <div class="float-right">
                                                        <button type="submit"
                                                            class="btn btn-primary">{{ __('Search') }}</button>
                                                        <a href="{{ route('expense.index') }}"
                                                            class="btn btn-secondary">{{ __('Reset') }}</a>
                                                    </div>
                                                </div>
                                                 <div class="">

                                                <a href="{{ route('expense.export', request()->query()) }}" class="btn btn-md btn-success">
                                                    <i class="fa fa-file-excel"></i> {{ __('Export') }}
                                                </a>
                                                <a href="{{ route('expense.print', request()->query()) }}" target="_blank" class="btn btn-md btn-info">
                                                    <i class="fa fa-print"></i> {{ __('Print') }}
                                                </a>
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
                                                    <th>Expense Type</th>
                                                    <th class="text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (sizeof($datas) > 0)
                                                    <?php $sl = 0; ?>
                                                    @foreach ($datas as $data)
                                                        <tr>
                                                            <td>{{ ++$sl }}</td>

                                                            <td>
                                                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#expenseDetails{{ $data->id }}">
                                                                    Expense Details
                                                                </button>
                                                                <!-- Modal -->
                                                                <div class="modal fade text-left"
                                                                    id="expenseDetails{{ $data->id }}" role="dialog"
                                                                    aria-labelledby="expenseDetailslabel{{ $data->id }}"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h4 class="modal-title"
                                                                                    id="expenseDetailslabel{{ $data->id }}">
                                                                                    Expense Details for: <strong>{{ $data->name }}</strong>

                                                                                </h4>
                                                                                <button type="button" class="close"
                                                                                    data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    <div
                                                                                        class="col-md-12 table-responsive">
                                                                                        @if ($data->expenses && $data->expenses->count())
                                                                                            <table id="datatable2-{{ $data->id }}"
                                                                                                class="table table-sm table-bordered mb-0 datatable2"
                                                                                                data-vehicle-name="{{ $data->name }}">
                                                                                                <thead>
                                                                                                    <tr>
                                                                                                        <th>Action</th>
                                                                                                        <th>SL</th>
                                                                                                        <th>Title</th>
                                                                                                        <th>Expense Date</th>
                                                                                                        <th class="text-right">Amount</th>
                                                                                                        <th>Note</th>
                                                                                                    </tr>
                                                                                                </thead>

                                                                                                <tbody>

                                                                                                    @foreach ($data->expenses as $expense)
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                <div class="btn-group mr-1 mb-1">
                                                                                                                    <button type="button"
                                                                                                                        class="btn btn-secondary dropdown-toggle"
                                                                                                                        data-toggle="dropdown" aria-haspopup="true"
                                                                                                                        aria-expanded="false">
                                                                                                                        Action
                                                                                                                    </button>
                                                                                                                    @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                                                                                    <div class="dropdown-menu">
                                                                                                                        <a href="{{ route('expense.edit', $expense->id) }}"
                                                                                                                            class="dropdown-item" title="Edit">
                                                                                                                            <i class="fa fa-pencil"></i> Edit
                                                                                                                        </a>

                                                                                                                        <button type="button" class="dropdown-item"
                                                                                                                            title="Delete"
                                                                                                                            onclick="deleteData('{{ route('expense.delete',$expense->id) }}')">
                                                                                                                            <i class="fa fa-trash"></i> Delete
                                                                                                                        </button>
                                                                                                                    </div>
                                                                                                                    @endif
                                                                                                                </div>
                                                                                                            </td>
                                                                                                            <td>{{ $loop->iteration }}</td>
                                                                                                            <td>{{ $expense->title ?? 'N/A' }}</td>
                                                                                                            <td>{{\Carbon\Carbon::parse($expense->expense_date ?? null)->format('d M Y') }}</td>
                                                                                                            <td class="text-right">{{ number_format(($expense->amount ?? 0), 2) }}</td>
                                                                                                            <td>{{ $expense->note ?? 'N/A' }}</td>
                                                                                                        </tr>
                                                                                                    @endforeach
                                                                                                    <tr>
                                                                                                        <th></th>
                                                                                                        <th></th>
                                                                                                        <th></th>
                                                                                                        <th class="text-right">Total:</th>
                                                                                                        <th class="text-right">
                                                                                                            {{ number_format(($data->expenses->sum('amount') ?? 0), 2) }}
                                                                                                        </th>
                                                                                                        <th></th>
                                                                                                    </tr>
                                                                                                </tbody>
                                                                                            </table>
                                                                                        @else
                                                                                            <span class="text-muted">No Expenses Available</span>
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


                                                            <td>{{ $data->name }}</td>

                                                            <td class="text-right">{{ number_format($data->total_amount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <th colspan="3" class="text-right">Grand Total:</th>
                                                    <th class="text-right">
                                                        {{ number_format($totalFinalAmount, 2) }}
                                                    </th>

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
            $('.datatable2').each(function() {
                var expenseName = $(this).data('vehicle-name') || 'Expense List';

                // Initialize only once per table
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        dom: 'Bfrtip',
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                title: expenseName,
                                exportOptions: {
                                    columns: ':not(:first-child)'
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                title: expenseName,
                                exportOptions: {
                                    columns: ':not(:first-child)'
                                }
                            },
                            {
                                extend: 'print',
                                title: expenseName,
                                exportOptions: {
                                    columns: ':not(:first-child)'
                                }
                            }
                        ]
                    });
                }
            });
        });
    </script>

@endsection
