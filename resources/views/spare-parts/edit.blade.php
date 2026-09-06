@extends('layouts.app')
@section('title', 'Edit Spare Parts')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('spare-parts.index')
                <a href="{{ route('spare-parts.index') }}" class="btn btn-primary">
                    {{ __('Spare Parts list') }} <i class="feather icon-eye"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Setup') }}</li>
                    <li class="breadcrumb-item">{{ __('Spare Parts') }}</li>
                    <li class="breadcrumb-item active">{{ __('Edit Spare Parts') }}</li>
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
                            <h4 class="card-title">Edit Spare Parts</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="sparePartsForm" action="{{ route('spare-parts.update', $spareParts->id) }}" method="post"
                                    class="form" enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        <input type="hidden" name="id" value="{{ $spareParts->id }}">
                                        @include('spare-parts.form')
                                    </div>

                                    <div class="form-actions right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-check-square-o"></i> Update
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
    function calculateTotalForRow(row) {
        let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        let quantity = parseFloat(row.querySelector('.parts-quantity').value) || 0;
        row.querySelector('.total-parts-price').value = (unitPrice * quantity).toFixed(2);
        calculateDueForRow(row);
    }

    function calculateDueForRow(row) {
        const total = parseFloat(row.querySelector('.total-parts-price').value) || 0;
        const paidInput = row.querySelector('.paid-amount');
        const paid = parseFloat(paidInput.value) || 0;
        const due = total - paid;
        row.querySelector('.due-amount').value = due >= 0 ? due.toFixed(2) : 0;
        validatePaidAmountForRow(row, paid, total);
    }

    function validatePaidAmountForRow(row, paid, total) {
        const errorEl = row.querySelector('.paid-error');
        const input = row.querySelector('.paid-amount');
        if (paid > total) {
            errorEl.style.display = 'block';
            input.classList.add('is-invalid');
        } else {
            errorEl.style.display = 'none';
            input.classList.remove('is-invalid');
        }
    }

    function initCalculateTotals() {
        document.querySelectorAll('.purchase-line').forEach(row => {
            let unitInput = row.querySelector('.unit-price');
            let qtyInput = row.querySelector('.parts-quantity');
            let paidInput = row.querySelector('.paid-amount');

            [unitInput, qtyInput].forEach(input => {
                input.addEventListener('input', () => calculateTotalForRow(row));
            });

            if (paidInput) {
                paidInput.addEventListener('input', () => calculateDueForRow(row));
            }

            // Run once on page load (prefill)
            calculateTotalForRow(row);
        });
    }

    // Run after DOM loads
    document.addEventListener('DOMContentLoaded', initCalculateTotals);
</script>
<script>
    $(document).ready(function() {
        $('#datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD',
            icons: {
                date: 'fa fa-calendar'
            }
        });
    });

    $(document).on('input', '.paid-amount', function () {
        let row = $(this).closest('.purchase-line'); // find the specific line
        calculateDueForRow(row[0]);
    });

    $(function () {
        // Toggle bank list based on selected payment method
        $(document).on('change', '.payment-method', function () {
            let section = $(this).closest('.payment-section');
            let selectedMethod = $(this).val();
            let bankWrapper = section.find('.bank-list-wrapper');

            if (selectedMethod === 'bank_transfer') {
                bankWrapper.slideDown(200);
            } else {
                bankWrapper.slideUp(200);
                section.find('.bank-id').val('');
            }
        });

        // Validate bank selection before form submission
        const $form = $('#sparePartsForm');
        let submitBtn = $form.find('button[type="submit"]');

        $form.on('submit', function (e) {
            let valid = true;

            $('.purchase-line').each(function () {
                const row = this;
                const total = parseFloat(row.querySelector('.total-parts-price').value) || 0;
                const paid = parseFloat(row.querySelector('.paid-amount').value) || 0;

                if (paid > total) {
                    valid = false;
                    validatePaidAmountForRow(row, paid, total);
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Paid Amount',
                        text: 'Paid amount cannot be greater than the total purchase amount!',
                        confirmButtonColor: '#3085d6',
                    });
                    return false; // break the loop
                }
            });

            if (!valid) {
                e.preventDefault();
                submitBtn.removeAttr('disabled');
                submitBtn.find('.spinner-border').remove();
                return;
            }

            $('.payment-section').each(function () {
                const method = $(this).find('.payment-method').val();
                const bankId = $(this).find('.bank-id').val();
                if (method === 'bank_transfer' && (!bankId || bankId === '')) {
                    valid = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bank Not Selected',
                        text: 'Please select a bank for all bank transfer payments!',
                        confirmButtonColor: '#3085d6',
                    });
                    return false; // break the loop
                }
            });

            if (!valid) {
                e.preventDefault();
                submitBtn.removeAttr('disabled');
                submitBtn.find('.spinner-border').remove();
            }
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

                    // append new bank to all bank selects
                    $('.bank-id').each(function () {
                        // add new option only if it doesn't exist already
                        if (!$(this).find(`option[value="${res.bank.id}"]`).length) {
                            $(this).append(
                                `<option value="${res.bank.id}">Name: ${res.bank.bank_name} - Account Name: ${res.bank.account_name} - Account No: ${res.bank.account_no}</option>`
                            );
                        }

                        // if this is the bank dropdown of the **last opened modal row**, auto-select it
                        // here we assume you want to select the last visible .bank-id that triggered the add
                        // otherwise, you can target a specific section
                        if ($(this).is(':visible')) {
                            $(this).val(res.bank.id).trigger('change');
                        }
                    });

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

   document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.paid-amount');
        const prevPaidInput = document.querySelectorAll('.old-paid-amount');
        const prevPaidValues = Array.from(prevPaidInput).map(input => parseFloat(input.value) || 0);
        const totalPrevPaid = prevPaidValues.reduce((sum, val) => sum + val, 0);

        let currentBalance = 0;

        // Fetch balance once
        fetch('{{ route("balances.getBalance") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    currentBalance = parseFloat(data.balance);
                }
            });

        // Add listener for each paid amount input
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                // Get all current entered paid amounts
                const currentPaidValues = Array.from(inputs).map(i => parseFloat(i.value) || 0);
                const totalCurrentPaid = currentPaidValues.reduce((sum, val) => sum + val, 0);

                // Combined total (old + new)
                const grandTotal = totalCurrentPaid - totalPrevPaid;

                // Find the error message for this input
                const errorMsg = this.closest('.form-group').querySelector('.balance-error');

                // Validation
                if (grandTotal > currentBalance) {
                    errorMsg.style.display = 'block';
                    this.classList.add('is-invalid');
                } else {
                    errorMsg.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
</script>
@endsection
