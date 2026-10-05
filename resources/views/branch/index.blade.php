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
                                                            onclick="deleteBranch({{ $branch->id }}, '{{ addslashes($branch->name) }}')">
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

        function deleteBranch(id, name) {
            const relatedUrl = "{{ route('branch.related', ':id') }}".replace(':id', id);
            const deleteUrl  = "{{ route('branch.delete', ':id') }}".replace(':id', id);

            $.ajax({
                url: relatedUrl,
                type: 'GET',
                success: function (res) {
                    const related = res.related || {};
                    const entries = Object.entries(related);

                    if (entries.length > 0) {
                        // Blocked: build a list of related data for the modal
                        let listHtml = '<ul style="text-align:left; display:inline-block; margin-top:10px;">';
                        entries.forEach(function ([label, count]) {
                            listHtml += '<li>' + label + ': <b>' + count + '</b></li>';
                        });
                        listHtml += '</ul>';

                        Swal.fire({
                            title: 'Cannot Delete "' + name + '"',
                            html:
                                '<p class="text-danger">This branch has associated records and cannot be deleted.</p>' +
                                '<p style="font-size:14px;">Deleting a branch with data would permanently erase:</p>' +
                                listHtml +
                                '<p style="font-size:13px; margin-top:10px;">Reassign or remove these records first.</p>',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                        return;
                    }

                    // No related data — normal confirm, then delete
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Delete branch "' + name + '"? This cannot be undone!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'No',
                        confirmButtonText: 'Yes, delete it',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-warning mr-10',
                            cancelButton: 'btn btn-danger ml-1'
                        }
                    }).then(function (result) {
                        if (!result.value) return;

                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: { _token: $('#csrfToken').val() },
                            beforeSend: function () { blockBodyOnAjaxRequest(); },
                            complete: function () { $('body').unblock(); },
                            success: function (res) {
                                Swal.fire({
                                    title: res.title,
                                    text: res.msg,
                                    icon: res.type,
                                    confirmButtonClass: 'btn btn-success'
                                }).then(function () { location.reload(); });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error',
                                    text: (xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message))
                                        || 'Cannot delete this branch.',
                                    icon: 'error'
                                });
                            }
                        });
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Could not check branch relations. Please try again.', 'error');
                }
            });
        }
    </script>
@endsection
