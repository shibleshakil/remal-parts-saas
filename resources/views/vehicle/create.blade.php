@extends('layouts.app')
@section('title', 'Create Vehicle')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('vehicle.index')
                <a href="{{ route('vehicle.index') }}" class="btn btn-primary">
                    {{ __('Vehicle List') }} <i class="feather icon-eye"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Vehicle') }}</li>
                    <li class="breadcrumb-item active">{{ __('Create Vehicle') }}</li>
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
                            <h4 class="card-title">Add Vehicle</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="vehicle-store-form" action="{{ route('vehicle.store') }}" method="post" class="form"
                                    enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        @include('vehicle.form')
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
        @include('supplier.modal')
        @include('sale.bank_modal')
        <!--/ basic form table -->
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        function calculateRow(row) {
            let price = parseFloat(row.find('.price-input').val()) || 0;
            let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            let vehicle_price = price * quantity;

            // Calculate new temp total
            let tempTotal = 0;
            $('.product-row').each(function() {
                if (this === row[0]) {
                    tempTotal += vehicle_price;
                } else {
                    tempTotal += parseFloat($(this).find('.vehicle_price-input').val()) || 0;
                }
            });

            const totalPurchasePrice = parseFloat($('#total_purchase_price').val()) || 0;

            // 🚨 Check if limit is exceeded
            if (tempTotal > totalPurchasePrice) {

                Swal.fire({
                    title: 'Warning',
                    text: 'The values entered cause the Total Vehicle Price to exceed the Total Purchase Price. Please adjust the values.',
                    type: 'warning',
                    confirmButtonClass: "btn btn-danger"
                }).then(function() {
                    // Reset current row
                    row.find('.price-input').val('0');
                    row.find('.quantity-input').val('0');
                    row.find('.vehicle_price-input').val('0.00');

                    updateTotals(); // Recalculate overall
                    return;
                });

            }

            row.find('.vehicle_price-input').val(vehicle_price.toFixed(2));
            updateTotals();
        }

        function updateTotals() {
            let totalvehicle_price = 0;
            let totalTargetSellingPrice = 0;

            $('.product-row').each(function() {
                totalvehicle_price += parseFloat($(this).find('.vehicle_price-input').val()) || 0;
                totalTargetSellingPrice += (parseFloat($(this).find('.target_sell_price-input').val()) || 0) * (parseFloat($(this).find('.quantity-input').val()) || 0);
            });

            $('#total_vehicle_price_text').text(totalvehicle_price.toFixed(2));
            $('#total_vehicle_price').val(totalvehicle_price.toFixed(2));
            $('#vehicle_target_sell_price').val(totalTargetSellingPrice.toFixed(2));
            $('#dueAmount').val(totalvehicle_price.toFixed(2));
        }

        function updateRowIndices() {
            $('.product-row').each(function(index) {
                $(this).find('input[name^="product_name"]').attr('name', `product_name[${index}]`);
                $(this).find('input[name^="price"]').attr('name', `price[${index}]`);
                $(this).find('input[name^="quantity"]').attr('name', `quantity[${index}]`);
                $(this).find('input[name^="vehicle_price"]').attr('name', `vehicle_price[${index}]`);
            });
        }

        // Initial calculation on load
        // $('.product-row').each(function() {
        //     calculateRow($(this));
        // });

        // Add new row
        $('.add-row').click(function() {
            let newRow = $('.product-row:first').clone();
            newRow.find('input').val('');
            newRow.find('select').val('').select2();
            newRow.appendTo('#product-container');
            updateRowIndices();
        });

        // Delete row
        $(document).on('click', '.delete-row', function() {
            if ($('.product-row').length > 1) {
                $(this).closest('.product-row').remove();
                updateTotals();
                updateRowIndices();
            }
        });

        // Recalculate when price or quantity changes
        $(document).on('input', '.price-input, .quantity-input, .target_sell_price-input', function() {
            calculateRow($(this).closest('.product-row'));
        });

        // Re-validate totals when total_purchase_price changes
        $('#total_purchase_price').on('input', function() {
            $('.product-row').each(function() {
                calculateRow($(this));
            });
        });

        // Date picker
        $('#date').datetimepicker({
            format: 'YYYY-MM-DD'
        });


        $(function() {
            const $form = $('#vehicle-store-form');

            // 1) Remove the related error as soon as user changes the field
            $form.on('input change', 'input, textarea, select', function() {
                const name = $(this).attr('name');
                if (!name) return;
                // remove any error spans that were tagged for this input name
                $form.find(`.text-danger[data-field="${name}"]`).remove();
            });

            // 2) Submit handler (AJAX)
            $form.on('submit', function(e) {
                e.preventDefault();
                const formEl = this;
                let formData = new FormData(formEl);

                // remove previous errors inside this form
                $form.find('.text-danger').remove();
                let submitBtn = $form.find('button[type="submit"]');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function() {
                        submitBtn.removeAttr('disabled');
                        submitBtn.find('.spinner-border').remove();
                    },
                    success: function(response) {
                        // remove any leftover errors on successful submission
                        $form.find('.text-danger').remove();

                        Swal.fire({
                            title: "Success",
                            text: response.message || "Saved.",
                            type: "success"
                        });

                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = "{{ route('vehicle.index') }}";
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            Swal.fire({
                                title: "Error",
                                text: "You have some error in form input field. Please check input field value.",
                                type: "error",
                            });

                            const errors = xhr.responseJSON.errors;

                            $.each(errors, function(fieldKey, messages) {
                                // Laravel uses "quantity.0" for array; convert to bracket form: quantity[0]
                                const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');

                                // try to find exact input (quantity[0])
                                let $input = $form.find(`[name="${bracketName}"]`);

                                // fallback to un-indexed array name (quantity[])
                                if (!$input.length) {
                                    const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                                    $input = $form.find(`[name="${arrayName}"]`);
                                }

                                let message = messages.join(' '); // combine messages if multiple

                                if ($input.length) {
                                    // determine the actual name attribute (could be quantity[] or quantity[0])
                                    const actualName = $input.first().attr('name');

                                    // remove any existing error attached to this input name
                                    $form.find(`.text-danger[data-field="${actualName}"]`).remove();

                                    // insert error after the last element in case of grouped inputs (checkboxes/radios/arrays)
                                    $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                                } else {
                                    // if no matching input was found, show it at the top of the form (fallback)
                                    $form.prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
                                }
                            });
                        }else if(xhr.status === 403) {
                            Swal.fire({
                                title: "Error",
                                text: 'Balance exceeds, please check available balance',
                                type: "error"
                            });
                        }else {
                            Swal.fire({
                                title: "Error",
                                text: "Something went wrong.",
                                type: "error"
                            });
                        }
                    }
                });
            });
        });
    });

    $('#addBankForm').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        let submitBtn = $(this).find('button[type="submit"]');

        $.ajax({
            url: "{{ route('sales.bankStoreViaAjax') }}",
            type: "POST",
            data: data,
            complete: function() {
                submitBtn.removeAttr('disabled');
                submitBtn.find('.spinner-border').remove();
            },
            success: function(res) {
                if (res.status === 'success') {
                    $('#bank_id').append(
                        `<option value="${res.bank.id}" selected>Name:${res.bank.bank_name} - Account name: ${res.bank.account_name} - Account No: ${res.bank.account_no} </option>`
                    ).trigger('change');
                    $('#inlineBankForm').modal('hide');
                    $('#addBankForm')[0].reset();
                }
            },
            error: function(xhr) {

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function(fieldKey, messages) {
                        // Laravel uses "quantity.0" for array; convert to bracket form: quantity[0]
                        const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');

                        // try to find exact input (quantity[0])
                        let $input = $('#addBankForm').find(`[name="${bracketName}"]`);

                        // fallback to un-indexed array name (quantity[])
                        if (!$input.length) {
                            const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                            $input = $('#addBankForm').find(`[name="${arrayName}"]`);
                        }

                        let message = messages.join(' '); // combine messages if multiple

                        if ($input.length) {
                            // determine the actual name attribute (could be quantity[] or quantity[0])
                            const actualName = $input.first().attr('name');

                            // remove any existing error attached to this input name
                            $('#addBankForm').find(`.text-danger[data-field="${actualName}"]`).remove();

                            // insert error after the last element in case of grouped inputs (checkboxes/radios/arrays)
                            $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                        } else {
                            // if no matching input was found, show it at the top of the form (fallback)
                            $('#addBankForm').prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: "Something went wrong.",
                        icon: "error"
                    });
                }
            }
        });
    });

    $(document).on('input', '#paid_amount', function () {
        let total_parts_price = parseFloat($('#total_purchase_price').val()) || 0;
        let paid_amount = parseFloat($('#paid_amount').val()) || 0;

        if(total_parts_price > 0 && paid_amount > 0){
            $('#dueAmount').val(total_parts_price - paid_amount);
        } else {
            $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
        }
    });

    $('#payment_method').on('change', function () {
        if ($(this).val() === 'bank_transfer') {
            $('#bank_list_wrapper').show();   // Show bank list
        } else {
            $('#bank_list_wrapper').hide();   // Hide when not bank transfer
            $('#bank_id').val('');            // Reset bank selection
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('paid_amount');
        const errorMsg = document.getElementById('balanceError');
        let currentBalance = 0;

        // Fetch balance once
        fetch('{{ route("balances.getBalance") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    currentBalance = parseFloat(data.balance);
                }
            });

        // Listen for input changes
        input.addEventListener('input', function () {
            const entered = parseFloat(this.value) || 0;

            if (entered > currentBalance) {
                errorMsg.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                errorMsg.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });
    });
</script>

@endsection
