@extends('layouts.app')
@section('title', 'Create Spare Parts')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-primary">
                        {{ __('Spare Parts List') }} <i class="feather icon-eye"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item ">{{ __('Spare Parts') }}</li>
                        <li class="breadcrumb-item active">{{ __('Create Spare Parts') }}</li>
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
                                <h4 class="card-title">Add Spare Parts</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <form id="sparePartsForm" action="{{ route('spare-parts.store') }}" method="post" class="form"
                                        enctype="multipart/form-data">@csrf
                                        <div class="form-body">
                                            @include('spare-parts.form')
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

                @include('supplier.modal')
                @include('sale.bank_modal')
            </section>
            <!--/ basic form table -->
        </div>
    </div>
@endsection

@section('script')
<script>
    function calculateTotal() {
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        const partsQuantity = parseFloat($('#parts_quantity').val()) || 0;
        const total = unitPrice * partsQuantity;
        $('#total_parts_price').val(total.toFixed(2));
    }

    $('#unit_price, #parts_quantity').on('input', calculateTotal);

    // Initialize date picker
    $('#date').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#payment_method').on('change', function () {
        if ($(this).val() === 'bank_transfer') {
            $('#bank_list_wrapper').show();   // Show bank list
        } else {
            $('#bank_list_wrapper').hide();   // Hide when not bank transfer
            $('#bank_id').val('');            // Reset bank selection
        }
    });

    $(document).on('input', '#paid_amount', function () {
        let total_parts_price = parseFloat($('#total_parts_price').val()) || 0;
        let paid_amount = parseFloat($('#paid_amount').val()) || 0;

        if(total_parts_price > 0 && paid_amount > 0){
            $('#dueAmount').val(total_parts_price - paid_amount);
        } else {
            $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
        }
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

    $(function() {
        const $form = $('#sparePartsForm');
        let submitBtn = $form.find('button[type="submit"]');


        // 2) Submit handler (AJAX)
        $form.on('submit', function(e) {
            if($('#payment_method').val() === 'bank_transfer'){
                if($('#bank_id').val() === ''){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bank Not Selected',
                        text: 'Please Select A Bank First!...'
                    });
                    submitBtn.removeAttr('disabled');
                    submitBtn.find('.spinner-border').remove();
                    return false;
                }
            }
        })
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
