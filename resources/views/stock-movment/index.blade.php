@extends('layouts.app')
@section('title', 'Stock Movment List')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @can('stock-movment.create')
                        <a href="{{ route('stock-movment.create') }}" class="btn btn-primary">
                            {{ __('Transfer Stock') }} <i class="feather icon-copy"></i>
                        </a>
                    @endcan
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Stock Movment') }}</li>
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
                                    <h4 class="card-title">Stock Movment List</h4>
                                    <div class="table-responsive">
                                        <table id="stockMovement" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl</th>
                                                    <th>From Branch</th>
                                                    <th>To Branch</th>
                                                    <th>Item</th>
                                                    <th style="text-align: right;">Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                               @foreach ($groupedMovements as $index => $group)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $group['from_branch_name'] }}</td>
                                                    <td>{{ $group['to_branch_name'] }}</td>
                                                    <td>{{ $group['total_qty'] }}</td>
                                                    <td style="text-align:right;">
                                                        {{ number_format($group['total_amount'], 2) }}
                                                    </td>
                                                    <td>
                                                        <button
                                                            class="btn btn-sm btn-primary view-details"
                                                            data-items='@json($group['items'])'
                                                            data-from="{{ $group['from_branch_name'] }}"
                                                            data-to="{{ $group['to_branch_name'] }}">
                                                            View
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" style="text-align: right;">Total:</th>
                                                    <th style="text-align: right;">
                                                        {{ number_format($stockMovments->sum('total_amount'), 2) }}
                                                    </th>
                                                    <th></th>
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

            <div class="modal fade" id="movementModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="movementModalTitle">Movement Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <table id="movementTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Parts</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>

                                <tbody id="movementDetailsBody"></tbody>

                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th style="text-align:right;">Total:</th>
                                        <th id="totalQty"></th>
                                        <th id="totalAmount" style="text-align:right;"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // DataTable initialization (assuming it's set up in layouts.app or elsewhere)
            $('#stockMovement').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": false,
                    "info": true,
                    "autoWidth": false,
            });
        });

        let movementTable;

        document.querySelectorAll('.view-details').forEach(btn => {
            btn.addEventListener('click', function () {

                let items = JSON.parse(this.dataset.items);
                let from = this.dataset.from;
                let to = this.dataset.to;
                let title = `Stock Movement: ${from} → ${to}`;

                if ($.fn.DataTable.isDataTable('#movementTable')) {
                    $('#movementTable').DataTable().destroy();
                }
                // 👉 Set dynamic title
                document.getElementById('movementModalTitle').innerText = title;

                let tbody = document.getElementById('movementDetailsBody');
                tbody.innerHTML = '';

                let totalQty = 0;
                let totalAmount = 0;

                items.forEach(item => {
                    let partName = item.purchase_line?.vehicle_detail?.parts_name ?? '';
                    let qty = parseFloat(item.quantity);
                    let amount = parseFloat(item.total_amount);

                    totalQty += qty;
                    totalAmount += amount;

                    tbody.innerHTML += `
                        <tr>
                            <td>${partName}</td>
                            <td style="text-align:right;">${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${qty}</td>
                            <td style="text-align:right;">${amount.toFixed(2)}</td>
                        </tr>
                    `;
                });

                document.getElementById('totalQty').innerText = totalQty;
                document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);



                $('#movementTable').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    searching: false,
                    info: false,

                    buttons: [
                        {
                            extend: 'excel',
                            footer: true,
                            title: title,
                            exportOptions: {
                                modifier: {
                                    page: 'all'
                                }
                            }
                        },
                        {
                            extend: 'pdf',
                            title: title,
                            footer: true,
                            exportOptions: {
                                modifier: {
                                    page: 'all'
                                }
                            }
                        },
                        {
                            extend: 'print',
                            title: title,
                            footer: true,
                            exportOptions: {
                                modifier: {
                                    page: 'all'
                                }
                            }
                        }
                    ]
                });

                $('#movementModal').modal('show');
            });
        });
    </script>
@endsection
