@extends('layouts.app')
@section('title', 'Stock Movment')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @can('stock-movment.index')
                    <a href="{{ route('stock-movment.index') }}" class="btn btn-primary">
                        {{ __('Stock Transfer List') }} <i class="feather icon-eye"></i>
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
            <!-- basic form table -->
            <section id="basic-form">
                <div class="row">
                    <div class="col-12">
                        @include('partials.session_message')
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Transfer Stock</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <form action="{{ route('stock-movment.store') }}" method="post" class="form"
                                        enctype="multipart/form-data">@csrf
                                        <div class="form-body">
                                            @include('stock-movment.form')
                                        </div>

                                        <div class="form-actions right">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-check-square-o"></i> Submit
                                            </button>
                                        </div>
                                    </form>
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
    <script>
        $(document).ready(function () {
            $('#parts').on('change', function () {
                let selected = $(this).find('option:selected');

                let partId = selected.val();
                let partName = selected.text();
                let unitPrice = selected.data('unit-price') || 0;
                let availableQty = selected.data('available-qty') || 0;

                if (partId == "") return;

                // Prevent duplicate
                if ($('#partsTable tbody tr[data-id="' + partId + '"]').length) {
                    alert('Part already added!');
                    return;
                }

                let row = `
                            <tr data-id="${partId}" data-available="${availableQty}">
                                <td>
                                    ${partName}
                                    <input type="hidden" name="parts_ids[]" value="${partId}">
                                </td>
                                <td class="unit-price">${unitPrice}</td>
                                <td class="available">${availableQty}</td>
                                <td>
                                    <input type="number" name="quantities[]"
                                        class="form-control qty"
                                        min="1"
                                        max="${availableQty}"
                                        value="1">
                                </td>
                                <td class="total">${unitPrice}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                </td>
                            </tr>
                        `;

                $('#partsTable tbody').append(row);

                calculateTotal();
                calculateFinal();
            });

            // Remove row
            $(document).on('click', '.removeRow', function () {
                $(this).closest('tr').remove();
                calculateTotal();
                calculateFinal();
            });

            // Quantity change
            $(document).on('input', '.qty', function () {
                let row = $(this).closest('tr');
                let qty = parseFloat($(this).val()) || 0;
                let price = parseFloat(row.find('.unit-price').text()) || 0;

                let total = qty * price;
                row.find('.total').text(total.toFixed(2));

                calculateTotal();
                calculateFinal();
            });

            // Optional: total sum
            function calculateTotal() {
                let grandTotal = 0;

                $('#partsTable tbody tr').each(function () {
                    let total = parseFloat($(this).find('.total').text()) || 0;
                    grandTotal += total;
                });
            }

        });

        $(document).on('input', '.qty', function () {
            let row = $(this).closest('tr');
            let qtyInput = $(this);

            let qty = qtyInput.val();
            let price = parseFloat(row.find('.unit-price').text()) || 0;
            let maxQty = parseFloat(row.data('available')) || 0;

            // Allow empty while typing
            if (qty === '') {
                row.find('.total').text('0.00');
                return;
            }

            qty = parseFloat(qty);

            // Max restriction
            if (qty > maxQty) {
                alert('Quantity exceeds available stock!');
                qty = maxQty;
                qtyInput.val(maxQty);
            }

            let total = qty * price;
            row.find('.total').text(total.toFixed(2));

            calculateFinal();
        });

        $(document).on('blur', '.qty', function () {
            let qtyInput = $(this);

            if (qtyInput.val() === '' || parseFloat(qtyInput.val()) < 1) {
                qtyInput.val(1);
            }
        });

        function calculateFinal() {
            console.log('called');

            let grandTotal = 0;
            let totalQty = 0;

            $('#partsTable tbody tr').each(function () {
                let qty = parseFloat($(this).find('.qty').val()) || 0;
                let total = parseFloat($(this).find('.total').text()) || 0;

                totalQty += qty;
                grandTotal += total;
            });

            $('#totalQty').text(totalQty);
            $('#grandTotal').text(grandTotal.toFixed(2));
        }

        $('#fromBranch').on('change', function () {

            let branchId = $(this).val();

            let toBranchId = $('#toBranch').val();

            if (branchId && toBranchId && branchId === toBranchId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Branch Selection',
                    text: 'From Branch and To Branch cannot be the same. Please select different branches.',
                });
                $(this).val('');
            }

            $('#partsTable tbody').empty();
            $('#totalQty').text(0);
            $('#grandTotal').text('0.00');


            $('#parts').html('<option value="">Loading...</option>');

            if (!branchId) return;

            $.ajax({
                url: "{{ route('stock-movment.getPartsByBranch', ':id') }}".replace(':id', branchId),
                type: 'GET',
                success: function (response) {

                    let options = '<option value="">Select a Spare Part</option>';

                    response.forEach(function (part) {

                        options += `
                            <option
                                value="${part.id}"
                                data-unit-price="${part.unit_price}"
                                data-available-qty="${part.available_quantity}"
                                data-target-sell-price="${part.target_sell_price ?? part.unit_price}"
                            >
                                ${part.parts_name} - ${part.uid} | ${part.car_name} | ${part.car_model} |
                                ${parseFloat(part.unit_price).toFixed(2)} | ${part.available_quantity}
                            </option>
                        `;
                    });

                    $('#parts').html(options).trigger('change'); // for select2
                }
            });
        });

        $('#toBranch').on('change', function () {
            let branchId = $(this).val();

            let fromBranchId = $('#fromBranch').val();

            if (branchId && fromBranchId && branchId === fromBranchId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Branch Selection',
                    text: 'From Branch and To Branch cannot be the same. Please select different branches.',
                });
                $(this).val('');
            }
        });
    </script>

@endsection
