@extends('layouts.app')
@section('title', 'Branch List')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @can('branch.create')
                        <a href="{{ route('branch.create') }}" class="btn btn-primary">
                            {{ __('Add Branch') }} <i class="feather icon-plus"></i>
                        </a>
                    @endcan
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Branch') }}</li>
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
                                    <h4 class="card-title">Branch List</h4>
                                    <div class="table-responsive">
                                        <table id="branchTable" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl</th>
                                                    <th>Branch</th>
                                                    <th style="text-align:right;">Receivable</th>
                                                    <th style="text-align:right;">Payable</th>
                                                    <th style="text-align:right;">Net Due</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($branches as $index => $branch)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $branch->name }}</td>

                                                    <td style="text-align:right;">{{ number_format($branch->receivable, 2) }}</td>
                                                    <td style="text-align:right;">{{ number_format($branch->payable, 2) }}</td>
                                                    <td style="text-align:right;">{{ number_format($branch->net_due, 2) }}</td>

                                                    <td>
                                                        <button class="btn btn-md btn-info toggle-details" data-id="{{ $branch->id }}">
                                                            <i class="feather icon-eye"></i>
                                                            Details
                                                        </button>
                                                        @can('branch.edit')
                                                        <a href="{{ route('branch.edit', $branch->id) }}"
                                                            class="btn btn-md btn-primary" title="Edit"><i
                                                                class="feather icon-edit"></i></a>
                                                        @endcan

                                                        @can('branch.delete')
                                                        <button type="button" class="btn btn-md btn-danger"
                                                            title="Delete"
                                                            onclick="deleteData('{{ route('branch.delete', $branch->id) }}')">
                                                            <i class="feather icon-trash"></i>
                                                        </button>
                                                        @endcan
                                                    </td>
                                                </tr>

                                                {{-- DETAILS ROW --}}
                                                <tr class="details-row d-none" id="details-{{ $branch->id }}">
                                                    <td colspan="7">

                                                        <div class="row">

                                                            {{-- RECEIVE FROM --}}
                                                            <div class="col-md-6">
                                                                <h6 class="text-success">Will Receive From</h6>
                                                                <ul>
                                                                    @forelse($branch->receive_from_branch_id?? [] as $item)

                                                                        <li>
                                                                            <span class="badge badge-success">{{ $item['name'] }}</span>
                                                                        </li>
                                                                    @empty
                                                                        <li>No receivable</li>
                                                                    @endforelse
                                                                </ul>
                                                            </div>

                                                            {{-- PAY TO --}}
                                                            <div class="col-md-6">
                                                                <h6 class="text-danger">Will Pay To</h6>
                                                                <ul>
                                                                    @forelse($branch->pay_to_branch_id?? [] as $item)

                                                                       <li>
                                                                            <span class="badge badge-danger">{{ $item['name'] }}</span>
                                                                        </li>
                                                                    @empty
                                                                        <li>No payable</li>
                                                                    @endforelse
                                                                </ul>
                                                            </div>

                                                        </div>

                                                        <hr>{{-- OPTIONAL TRANSACTION TABLE --}}
                                                        <h6>Transactions</h6>
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>From</th>
                                                                    <th>To</th>
                                                                    <th>Item</th>
                                                                    <th style="text-align:right;">Unit Price</th>
                                                                    <th>Qty</th>
                                                                    <th style="text-align:right;">Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @php
                                                                // Prepare the collection: filter and group
                                                                $groupedMovements = $stockMovments
                                                                    ->filter(function($m) use ($branch) {
                                                                        return $m->branch_from_id == $branch->id || $m->branch_to_id == $branch->id;
                                                                    })
                                                                    ->groupBy(function($item) {
                                                                        return $item->branch_from_id . '-' . $item->branch_to_id;
                                                                    });
                                                            @endphp

                                                            @foreach ($groupedMovements as $group)
                                                                @php $groupTotal = 0; @endphp

                                                                {{-- Data Rows --}}
                                                                @foreach ($group as $movment)
                                                                    @php $groupTotal += $movment->total_amount; @endphp
                                                                    <tr>
                                                                        <td>{{ $movment->fromBranch?->name ?? 'N/A' }}</td>
                                                                        <td>{{ $movment->toBranch?->name ?? 'N/A' }}</td>
                                                                        <td>{{ $movment->purchaseLine?->vehicleDetail?->parts_name ?? '' }}</td>
                                                                        <td style="text-align:right;">{{ number_format($movment->unit_price, 2) }}</td>
                                                                        <td>{{ $movment->quantity }}</td>
                                                                        <td style="text-align:right;">{{ number_format($movment->total_amount, 2) }}</td>
                                                                    </tr>
                                                                @endforeach

                                                                {{-- Subtotal Row for the Group --}}
                                                                <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                                    <td colspan="5" style="text-align: right;">
                                                                        Total for {{ $group->first()->fromBranch?->name }} to {{ $group->first()->toBranch?->name }}:
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        {{ number_format($groupTotal, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>

                                                        <hr>
                                                    </td>
                                                </tr>

                                            @endforeach
                                            </tbody>
                                        </table>

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

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('toggle-details')) {
                let id = e.target.getAttribute('data-id');
                document.getElementById('details-' + id).classList.toggle('d-none');
            }
        });
    </script>
@endsection
