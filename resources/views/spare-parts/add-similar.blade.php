@extends('layouts.app')
@section('title', 'Add Similar Spare Part')

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
                    <li class="breadcrumb-item">{{ __('Spare Parts') }}</li>
                    <li class="breadcrumb-item active">{{ __('Add Similar Spare Part') }}</li>
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
                            <h4 class="card-title">Add Similar Spare Part</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="addPartForm" action="{{ route('spare-parts.store-similar', $spareParts->id) }}" method="post" class="form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="parts_name">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="parts_name" id="parts_name" required
                                                    value="{{ old('parts_name', $spareParts->parts_name) }}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="car_name">Car Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="car_name" id="car_name" required
                                                    value="{{ old('car_name', $spareParts->car_name) }}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="car_model">Car Model <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="car_model" id="car_model" required
                                                    value="{{ old('car_model', $spareParts->car_model) }}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="unit_price">Unit Price <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="unit_price" id="unit_price" step="any" min="0"
                                                    required value="{{ old('unit_price') }}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="target_sell_price">Target Sell Price</label>
                                                <input type="text" class="form-control" name="target_sell_price" id="target_sell_price" step="any"
                                                    value="{{ old('target_sell_price')}}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="parts_quantity">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="parts_quantity" id="parts_quantity" step="any"
                                                    min="0" required value="{{ old('parts_quantity') }}" oninput="calculateTotal()">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="total_parts_price">Total Purchase Price <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="total_parts_price" id="total_parts_price" step="any"
                                                    readonly required>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="date">Date <span class="text-danger">*</span></label>
                                                <div class="input-group date datetime" id="date">
                                                    <input type="text" name="date" id="date_input" class="form-control date-input" required
                                                        value="{{ old('date', now()->format('Y-m-d')) }}" />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                                @error('date')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                      <label for="vehicle_target_sell_price">Supplier</label>
                                                    <div class="input-group">
                                                        <select class="form-control select2" name="supplier_id" id="supplier_id">
                                                            <option value="">Walking Supplier</option>
                                                            @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}">
                                                                {{ $supplier->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" title="Add New Supplier" data-toggle="modal"
                                                                data-target="#inlineSupplierForm">
                                                                <i class="feather icon-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="paid_amount">Paid Amount</label>
                                                <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
                                                    min="0" value="{{ old('paid_amount') }}">

                                                <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label> Due Amount</label>
                                                <input type="number" class="form-control total-parts-price text-info" id="dueAmount" readonly>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="payment_method">Payment Method</label>

                                                <select name="payment_method" id="payment_method" class="form-control select2">
                                                    <option value="">Select a Payment Method</option>
                                                    <option value="cash"
                                                        {{ old('payment_method') == 'cash' ? 'selected' : ''}}>
                                                        Cash</option>
                                                    <option value="bank_transfer"
                                                        {{ old('payment_method')  == 'bank_transfer' ? 'selected' : ''}}>
                                                        Bank Transfer</option>
                                                    <option value="cheque"
                                                        {{ old('payment_method')  == 'cheque' ? 'selected' : ''}}>
                                                        Cheque</option>
                                                    <option value="due"
                                                        {{ old('payment_method')  == 'due' ? 'selected' : ''}}>
                                                        Due</option>
                                                </select>
                                            </div>

                                            <!-- Hidden bank list -->
                                            <div class="col-md-6">
                                                <div class="form-group" id="bank_list_wrapper" style="display:none;">
                                                    <label for="bank_id">Select Bank</label>
                                                    <div class="input-group">
                                                        <select name="bank_id" id="bank_id" class="form-control">
                                                            <option value="">-- Select Bank --</option>
                                                            @foreach($banks as $bank)
                                                            <option value="{{$bank->id}}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" title="Add New Customer" data-toggle="modal"
                                                                data-target="#inlineBankForm">
                                                                <i class="feather icon-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <label for="note">Note</label>
                                                <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $spareParts->note) }}</textarea>
                                            </div>
                                        </div>
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
        <!--/ basic form table -->
    </div>
</div>
@include('supplier.modal')

<div class="modal fade text-left" id="inlineBankForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel333" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel333">Add New Bank</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBankForm" method="post">@csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bank_name" id="bank_name" step="any" min="0" required
                                value="{{ old('bank_name') }}">

                        </div>
                        <div class="col-md-12 form-group">
                            <label for="account_name">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="account_name" id="account_name" step="any" min="0"
                                required value="{{ old('account_name') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="account_no">Account No <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="account_no" id="account_no" step="any" min="0"
                                required value="{{old('account_no') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="branch">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="branch" id="branch" step="any" min="0" required value="{{ old('branch') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-outline-secondary btn-lg"
                            data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-primary btn-lg addCustomerFormButton">Save</button>
                    </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        function calculateTotal() {
            const unitPrice = parseFloat($('#unit_price').val()) || 0;
            const partsQuantity = parseFloat($('#parts_quantity').val()) || 0;
            const total = unitPrice * partsQuantity;
            $('#total_parts_price').val(total.toFixed(2));
        }

        $('#unit_price, #parts_quantity').on('input', calculateTotal);

        $('#date').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });

    $('#payment_method').on('change', function() {
        if ($(this).val() === 'bank_transfer') {
            $('#bank_list_wrapper').show(); // Show bank list
        } else {
            $('#bank_list_wrapper').hide(); // Hide when not bank transfer
            $('#bank_id').val(''); // Reset bank selection
        }
    });


    $(function() {
        const $form = $('#addPartForm');
        let submitBtn = $form.find('button[type="submit"]');


        // 2) Submit handler (AJAX)
        $form.on('submit', function(e) {
            if ($('#payment_method').val() === 'bank_transfer') {
                if ($('#bank_id').val() === '') {
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

    $(document).on('input', '#paid_amount', function() {
        let total_parts_price = parseFloat($('#total_parts_price').val()) || 0;
        let paid_amount = parseFloat($('#paid_amount').val()) || 0;

        if (total_parts_price > 0 && paid_amount > 0) {
            $('#dueAmount').val(total_parts_price - paid_amount);
        } else {
            $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
        }
    });

    function calculateTotal() {
        // get input values
        let unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
        let quantity = parseFloat(document.getElementById('parts_quantity').value) || 0;

        // calculate total
        let total = unitPrice * quantity;

        // set total
        document.getElementById('total_parts_price').value = total.toFixed(2);
    }

    // optional: also calculate total when unit price changes
    calculateTotal();

    let total_parts_price = parseFloat($('#total_parts_price').val()) || 0;
    let paid_amount = parseFloat($('#paid_amount').val()) || 0;
    if (total_parts_price > 0 && paid_amount > 0) {
        $('#dueAmount').val(total_parts_price - paid_amount);
    } else {
        $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
    }

    if ($('#payment_method').val() === 'bank_transfer') {
        $('#bank_list_wrapper').show();
    }

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
