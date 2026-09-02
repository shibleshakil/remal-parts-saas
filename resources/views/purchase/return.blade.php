@extends('layouts.app')
@section('title', 'Return / Exchange Purchase')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('purchase.index') }}" class="btn btn-primary">
                    {{ __('Purchase list') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="col-md-6 col-12">
            <div class="breadcrumb-wrapper float-right">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Purchase') }}</li>
                    <li class="breadcrumb-item active">{{ __('Return / Exchange Purchase') }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="return-form">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Return / Exchange Purchase #{{ $purchase[0]->invoice_no }}</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body">
                                <form id="return-form-input" action="{{ route('purchase.returnPurchasePost', $purchase[0]->invoice_no) }}" method="POST" class="form">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <!-- Supplier Info -->
                                            <div class="col-md-6 form-group">
                                                <label>Supplier</label>
                                                <input type="text" class="form-control" id="supplier" value="{{$purchase[0]?->supplier?->name ??  'walking Supplier' }}" readonly>
                                                <input type="text" class="form-control" name="supplier_id" value="{{$purchase[0]?->supplier?->id ?? null}}" hidden>
                                            </div>

                                            <!-- Return Date -->
                                            <div class="col-md-6 form-group">
                                                <label for="return_date_input">Return Date <span class="text-danger">*</span></label>
                                                <div class="input-group date datetime" id="return_date">
                                                    <input type="text" name="return_date" id="return_date_input" class="form-control date-input" required value="{{ old('return_date', now()->format('Y-m-d')) }}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                                @error('return_date')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                             <div class="col-12 card-header">
                                                @php
                                                    $finalAmount = $transection?->sum('final_amount') ?? 0;
                                                    $paidAmount = $transection?->sum('paid_amount') ?? 0;
                                                    $dueAmount = $finalAmount - $paidAmount;
                                                @endphp

                                                <h5>Total Amount: {{ number_format($finalAmount, 2) }}</h5>
                                                <h5>Paid Amount: {{ number_format($paidAmount, 2) }}</h5>
                                                <h5>Due Amount: {{ number_format($dueAmount, 2) }}</h5>
                                            </div>



                                            <!-- Reason -->
                                            <div class="col-md-12 form-group">
                                                <label for="reason">Reason for Return / Exchange</label>
                                                <textarea name="reason" id="reason" class="form-control">{{ old('reason') }}</textarea>
                                                @error('reason')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>


                                        <!-- Items -->
                                        <div class="table-responsive">
                                            <table class="table input-table">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="select-all-items"></th>
                                                        <th>Item Name</th>
                                                        <th class="text-center">Available Qty</th>
                                                        <th class="text-center">Unit Price</th>
                                                        <th class="text-center">Return Qty</th>
                                                        <th class="text-center">Return Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="return-rows">
                                                @foreach ($purchase->filter(fn($item) => $item->quantity != 0) as $index => $item)
                                                    <tr id="return-row-{{ $index }}" data-bs-toggle="tooltip" title="{{$item->vehicleDetail?->parts_name ?? ''}}">
                                                        <td>
                                                            <input type="checkbox" name="return_items[{{ $index }}][selected]" class="return-item-checkbox" data-index="{{ $index }}">
                                                        </td>
                                                        <td>
                                                            @php
                                                                $itemName = $item->vehicleDetail?->parts_name . ' - ' . $item->vehicleDetail?->uid . ' | ' . $item->vehicleDetail?->car_name . ' | ' . $item->vehicleDetail?->car_model;
                                                            @endphp
                                                            <input type="hidden" name="return_items[{{ $index }}][purchase_line_id]" value="{{ $item->id }}">
                                                            {{ $itemName }}
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-center">{{ $item->unit_price ?? 0 }}</td>
                                                        <td>
                                                            <input type="number" name="return_items[{{ $index }}][quantity]" class="form-control return-qty" value="0" min="0" max="{{ $item->quantity }}" step="any" data-index="{{ $index }}" disabled oninput="updateReturnCost({{ $index }})">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control return-cost" name="return_items[{{ $index }}][cost]" value="0.00" readonly>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>

                                                <tfoot>
                                                    <tr class="font-weight-bold">
                                                        <td colspan="5" class="text-right">Total Refund Amount</td>
                                                        <td class="text-right">
                                                            <span id="refund_amount_text">0.00</span>
                                                            <input type="hidden" name="refund_amount" id="refund_amount" value="0">
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <!-- Add Items to Exchange -->
                                        <h3 class="text-center mt-2 mb-3">Add Items to Exchange</h3>
                                        <div class="row mx-0">
                                            <!-- Vehicle Selector -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="search">Scan or search by barcode</label>
                                                    <fieldset class="form-group position-relative mb-0">
                                                        <input type="search" class="form-control form-control-xl input-xl" id="iconLeft1"
                                                            placeholder="">
                                                        <div class="form-control-position">
                                                            <i class="feather icon-search font-medium-4"></i>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <!-- Spare Parts Selector -->
                                                <div class="form-group">
                                                    <label for="parts">Spare Parts <span class="text-danger">*</span></label>
                                                    <select id="parts" class="form-control select2">
                                                        <option value="">Select a Spare Part</option>
                                                        @foreach ($parts as $part)
                                                        <option
                                                            value="{{ $part->id }}">
                                                            {{ $part->parts_name }} - {{ $part->uid }} | {{ $part->car_name }} | {{ $part->car_model }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @error('parts')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table class="table input-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 30%;">Item Name</th>
                                                                <th style="width: 15%;">Unit Price</th>
                                                                <th style="width: 15%;">Quantity</th>
                                                                <th style="width: 15%;">Target Selling Price</th>
                                                                <th style="width: 15%;">Total</th>
                                                                <th style="width: 10%;">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="product-rows">

                                                        </tbody>
                                                    </table>
                                                </div>

                                                <table class="table">
                                                    <tfoot>
                                                        <tr class="font-weight-bold">
                                                            <td class="text-right" colspan="4">Total Exchange Amount</td>
                                                            <td class="text-right">
                                                                <span id="total_cost_text">0.00</span>
                                                                <input type="hidden" name="total_exchange_cost" id="total_exchange_cost" value="0">
                                                            </td>
                                                            <td></td>
                                                        </tr>

                                                        <tr class="font-weight-bold">
                                                            <td class="text-right" colspan="4">Previous Due Amount</td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="previous_due_amount" id="previous_due_amount" value="{{ $dueAmount }}">
                                                                <span id="previous_due_amount_text">{{ number_format($dueAmount, 2) }}</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>

                                                        <tr class="font-weight-bold bg-secondary text-light">
                                                            <td class="text-right" colspan="4">Total Payble Amount</td>
                                                            <td class="text-right">
                                                                <span id="total_payble_amount_text">{{ number_format($dueAmount, 2) }}</span>
                                                                <input type="hidden" name="total_payble_amount" id="total_payble_amount" value="{{$dueAmount }}">
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label for="paid_amount" id="paid_amount_label">{{$dueAmount > 0 ? 'Paid' : 'Return'}} Amount</label>
                                                <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
                                                    min="0" value="">
                                            </div>

                                            <div class="col-md-3 form-group">
                                                <label>Total Due Amount</label>
                                                <input type="number" class="form-control text-info" id="dueAmount" name="dueAmount" value="0" readonly>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="payment_method">Payment Method</label>

                                                <select name="payment_method" id="payment_method" class="form-control select2">
                                                    <option value="">Select a Payment Method</option>
                                                    <option value="cash"
                                                        {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                                        Cash</option>
                                                    <option value="bank_transfer"
                                                        {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                                        Bank Transfer</option>
                                                    <option value="cheque"
                                                        {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>
                                                        Cheque</option>
                                                    <option value="due"
                                                        {{ old('payment_method') == 'due' ? 'selected' : '' }}>
                                                        Due</option>
                                                </select>


                                                <!-- Hidden bank list -->
                                                <div id="bank_list_wrapper" style="display:none; margin-top:10px;">
                                                    <label for="bank_id">Select Bank</label>
                                                    <div class="input-group">
                                                        <select name="bank_id" id="bank_id" class="form-control">
                                                            <option value="">-- Select Bank --</option>
                                                            @foreach($banks as $bank)
                                                            <option value="{{$bank->id}}">Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" title="Add New Supplier" data-toggle="modal"
                                                                data-target="#inlineBankForm">
                                                                <i class="feather icon-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-check-square-o"></i> Process Return
                                        </button>
                                    </div>
                                </form>
                            </div> <!-- card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@include('sale.bank_modal2')
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body" id="invoiceContent">
                <!-- Invoice will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    const existingQuantities = @json(isset($existingQuantities) ? $existingQuantities : []);
    $(document).ready(function() {
        const $vehicle = $('#vehicle');
        const $carParts = $('#car_spare_parts');
        const $spareParts = $('#spare_parts');
        const $parts = $('#parts');
        const $barcode = $('#iconLeft1');
        const select2Parent = $('.content-wrapper');


        // Initialize Select2
        $vehicle.add($parts).select2({
            dropdownParent: select2Parent
        });
        $carParts.select2({
            dropdownParent: select2Parent
        });

        $('.datetime').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#parts-by-barcode-div').hide();


        $parts.add($spareParts).add('#parts-by-barcode').on('change', function() {
            const $select = $(this);
            const selected = $select.find('option:selected');
            const itemId = selected.val();


            const itemName = selected.text();
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';

            if (itemId) {
                if (addItemToTable(itemId, itemName, itemType, $spareParts.val())) {
                    $select.val('').trigger('change');
                }
            }
        });


        function addItemToTable(itemId, itemName, itemType, vehicleId = null) {
            let found = false;
            itemId = parseInt(itemId);
            itemName = itemName.split('|');

            itemName = itemName.join('|');

            $('input[name^="products"][name$="[item_id]"]').each(function() {
                const row = $(this).closest('tr');
                const existingItemId = parseInt($(this).val());
                const existingItemType = $(this).data('item-type');
                if (existingItemId === itemId && existingItemType === itemType && itemId != 0) {
                    const qtyInput = row.find('.qty');
                    const costInput = row.find('.cost');
                    const unitPriceInput = parseFloat(row.find('.unit-price').val()) || 0;
                    let currentQty = parseInt(qtyInput.val()) || 0;
                    currentQty++;
                    qtyInput.val(currentQty);
                    costInput.val((unitPriceInput * currentQty).toFixed(2));
                    updateTotalCost();

                    found = true;
                    return false;
                }
            });

            if (found) return true;


            // Declare rowIndex globally or determine it dynamically
            if (typeof rowIndex === 'undefined') {
                rowIndex = $('#product-rows tr').length;
            }

            const vehicleInput = (itemType === 'car_spare_part' && vehicleId) ?
                `<input type="hidden" name="products[${rowIndex}][vehicle_id]" value="${vehicleId}">` : '';

            const row = `
                <tr id="row-${rowIndex}">
                    <td>
                        <input type="hidden" name="products[${rowIndex}][item_id]" value="${itemId}" data-item-type="${itemType}">
                        ${vehicleInput}
                        ${itemName}
                    </td>
                    <td><input type="number" name="products[${rowIndex}][unit_price]" class="form-control unit-price" value="" min="0" step="any" oninput="updateRowCost(${rowIndex})" required></td>
                    <td><input type="number" name="products[${rowIndex}][quantity]" class="form-control qty" value="1" min="1" step="any" oninput="updateRowCost(${rowIndex})"></td>
                    <td><input type="number" name="products[${rowIndex}][target_selling_price]" class="form-control target-selling-price" value="" min="0" step="any" oninput="updateRowCost(${rowIndex})"></td>
                    <td><input type="text" class="form-control cost" name="products[${rowIndex}][cost]" id="cost-${rowIndex}" value="" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow('row-${rowIndex}')">×</button></td>
                </tr>`;

            $('#product-rows').append(row);
            rowIndex++;
            updateTotalCost();
            return true;
        }


        window.updateRowCost = function(index) {
            const row = $(`#row-${index}`);
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            let quantity = parseInt(row.find('.qty').val()) || 0;
            const maxQtyAttr = row.find('.qty').attr('max');
            const maxQty = maxQtyAttr ? parseInt(maxQtyAttr) : null;

            row.find('.cost').val((unitPrice * quantity).toFixed(2));
            updateTotalCost();
        };


        window.removeRow = function(rowId) {
            $(`#${rowId}`).remove();
            updateTotalCost();
        };


        function updateTotalCost() {
            let total = 0;
            $('.cost').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total_cost_text').text(total.toFixed(2));
            $('#total_exchange_cost').val(total.toFixed(2));

            updateTotalRefund();
        }



        function getSparePartsByBarcode() {
            const barcode = $barcode.val().trim();
            $barcode.val('');
            if (!barcode) return;

            const url = `{{ route('purchase.getDataByBarcode', ['-a']) }}`.replace('-a', barcode);

            $.get(url, function(res) {
                if (!res.success) {
                    Swal.fire('Error', res.message || 'No parts found for the provided barcode.',
                        'warning');
                    return;
                }


                if (res.single) {
                    const item = res.data;
                    const itemName = `${item.parts_name} - ${item.uid} | ${item.car_name} | ${item.car_model}`; // thos || for adjust string
                    addItemToTable(item.id, itemName, item.unit_price,  item.vehicle_id ? 'car_spare_part' : 'spare_part', item.vehicle_id);
                }
            }).fail(function(xhr) {
                console.error("Barcode fetch failed:", xhr);
                Swal.fire('Error', 'Failed to fetch barcode data.', 'error');
                ignoreVehicleChange = false;
            });
        }


        $barcode.on('change', getSparePartsByBarcode).focus();


        $(window).on('keydown', function(e) {
            if (e.key === 'Enter') {
                const tag = e.target.tagName.toLowerCase();
                const type = e.target.type?.toLowerCase();
                if (type === 'search') {
                    e.preventDefault();
                    getSparePartsByBarcode();
                } else if (tag === 'input' || tag === 'select') {
                    e.preventDefault();
                }
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
                    $('#addBankForm').find('[data-field]').remove();
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


        //spare parts add
        $('#sparePartAddForm').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serialize();
            let submitBtn = $(this).find('button[type="submit"]');
            let url = $(this).attr('action');

            $.ajax({
                url: url,
                type: "POST",
                data: data,
                complete: function() {
                    submitBtn.removeAttr('disabled');
                    submitBtn.find('.spinner-border').remove();
                },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#parts').append(
                            `<option value="${res.part.id}" selected>${res.part.parts_name} - ${res.part.uid} | ${res.part.car_name} | ${res.part.car_model} | |</option>` //thos || for adjust string
                        ).trigger('change');
                        $('#inlineSparePartForm').modal('hide');
                        $('#sparePartAddForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    $('#sparePartAddForm').find('[data-field]').remove();
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;

                        $.each(errors, function(fieldKey, messages) {
                            // Laravel uses "quantity.0" for array; convert to bracket form: quantity[0]
                            const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');

                            // try to find exact input (quantity[0])
                            let $input = $('#sparePartAddForm').find(`[name="${bracketName}"]`);

                            // fallback to un-indexed array name (quantity[])
                            if (!$input.length) {
                                const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                                $input = $('#sparePartAddForm').find(`[name="${arrayName}"]`);
                            }

                            let message = messages.join(' '); // combine messages if multiple

                            if ($input.length) {
                                // determine the actual name attribute (could be quantity[] or quantity[0])
                                const actualName = $input.first().attr('name');

                                // remove any existing error attached to this input name
                                $('#sparePartAddForm').find(`.text-danger[data-field="${actualName}"]`).remove();

                                // insert error after the last element in case of grouped inputs (checkboxes/radios/arrays)
                                $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                            } else {
                                // if no matching input was found, show it at the top of the form (fallback)
                                $('#sparePartAddForm').prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
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


        $('#payment_method').on('change', function() {
            if ($(this).val() === 'bank_transfer') {
                $('#bank_list_wrapper').show(); // Show bank list
            } else {
                $('#bank_list_wrapper').hide(); // Hide when not bank transfer
                $('#bank_id').val(''); // Reset bank selection
            }
        });


        $(document).on('input', '#paid_amount', function () {
            let total_parts_price = parseFloat($('#total_payble_amount').val()) || 0;
            let paid_amount = parseFloat($('#paid_amount').val()) || 0;

            if(total_parts_price > 0 && paid_amount > 0){
                $('#dueAmount').val(total_parts_price - paid_amount);
            } else {
                $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
            }
        });


        $('#select-all-items').change(function () {
            const checked = this.checked;
            $('.return-item-checkbox').each(function () {
                const index = $(this).data('index');
                $(this).prop('checked', checked);
                toggleQtyField(index, checked);
                updateReturnCost(index);
            });
            updateTotalRefund();
        });

        $('.return-item-checkbox').change(function () {
            const index = $(this).data('index');
            const checked = this.checked;
            toggleQtyField(index, checked);
            updateReturnCost(index);
        });

        window.toggleQtyField = function (index, enabled) {
            const $qty = $(`#return-row-${index} .return-qty`);
            $qty.prop('disabled', !enabled);
            $qty.val(enabled ? 1 : 0);
        }

        window.updateReturnCost = function (index) {
            const $row = $(`#return-row-${index}`);
            const qty = parseInt($row.find('.return-qty').val()) || 0;
            const unitPrice = parseFloat($row.find('td:nth-child(4)').text()) || 0;
            const maxQty = parseInt($row.find('.return-qty').attr('max')) || 0;

            if (qty > maxQty) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Quantity Exceeded',
                    text: `Cannot return more than ${maxQty} items.`,
                });
                $row.find('.return-qty').val(maxQty);

                const cost = (maxQty * unitPrice).toFixed(2);
                $row.find('.return-cost').val(cost);
                updateTotalRefund();
                return;
            }

            const cost = (qty * unitPrice).toFixed(2);
            $row.find('.return-cost').val(cost);
            updateTotalRefund();
        }


        window.updateTotalRefund = function () {
            let total = 0;
            const prevDue = parseFloat($('#previous_due_amount').val()) || 0;
            $('.return-cost').each(function () {
                total += parseFloat($(this).val()) || 0;
            });

            $('#refund_amount_text').text((total).toFixed(2));
            $('#refund_amount').val((total).toFixed(2));

            let finalExchangeCost = parseFloat($('#total_exchange_cost').val()) || 0;


            $('#total_payble_amount_text').text(((prevDue - total) + finalExchangeCost).toFixed(2));
            $('#total_payble_amount').val(((prevDue - total) + finalExchangeCost).toFixed(2));

            calculateNewDue();
        }


        $('#return-form').submit(function (e) {
            const anySelected = $('.return-item-checkbox:checked').length > 0;
            if (!anySelected) {
                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least one item to return.',
                });
            }else{
                $('.return-item-checkbox').each(function () {
                    const index = $(this).data('index');
                    if (!$(this).is(':checked')) {
                        $(`#return-row-${index} input`).prop('disabled', true);
                    }
                });
            }
        });


        function calculateNewDue () {
            let total_parts_price = parseFloat($('#total_payble_amount').val()) || 0;
            let paid_amount = parseFloat($('#paid_amount').val()) || 0;

            let newdue = 0;


            if(total_parts_price > 0 && paid_amount > 0){
                newdue = Math.abs(total_parts_price - paid_amount);
                $('#dueAmount').val(newdue);
            }else if(total_parts_price < 0){
                newdue = Math.abs(total_parts_price + paid_amount);
                $('#dueAmount').val(newdue);

            } else {
                newdue = Math.abs(total_parts_price);
                $('#dueAmount').val(newdue); // optional: reset to total if paid = 0
            }
        }

        $(document).on('input change', '#paid_amount', function () {
            calculateNewDue();
        });


        // Submit Form with ajax
        $(function() {
            const $form = $('#return-form');
            let submitBtn = $form.find('button[type="submit"]');
            // 1) Remove the related error as soon as user changes the field
            $form.on('input change', 'input, textarea, select', function() {
                const name = $(this).attr('name');
                if (!name) return;
                // remove any error spans that were tagged for this input name
                $form.find(`.text-danger[data-field="${name}"]`).remove();
            });

            // 2) Submit handler (AJAX)
            $form.on('submit', function(e) {
                if ($('#payment_method').val() === 'bank_transfer') {
                    if ($('#bank_id').val() === '') {
                        Swal.fire({
                            title: 'Bank Not Selected',
                            text: 'Please Select A Bank First!...'
                        });

                        submitBtn.removeAttr('disabled');
                        submitBtn.find('.spinner-border').remove();

                        return false;
                    }
                }

                const finalAmount = parseFloat($('#total_payble_amount').val()) || 0;
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const Supplier = $('#Supplier').val();
                const reason = $('#reason').val();

                if(Supplier == "" && paidAmount < finalAmount){
                    Swal.fire({
                        title: 'Supplier Alert',
                        text: 'Paid amount must be equal to final amount for walking Supplier!'
                    });

                    submitBtn.removeAttr('disabled');
                    submitBtn.find('.spinner-border').remove();

                    return false;
                }

                e.preventDefault();
                let formData = new FormData(document.getElementById('return-form-input'));

                // remove previous errors inside this form
               $form.find('.text-danger').remove();

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
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: response.message || 'Purchase return & exchange processed successfully.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            // Inject invoice into modal
                            $('#invoiceContent').html(response.view);

                            // Show the modal
                            $('#invoiceModal').modal('show');

                            // After modal is shown, trigger print
                            $('#invoiceModal').on('shown.bs.modal', function() {
                                setTimeout(function() {
                                    // Print only the modal content
                                    const printContents = document.getElementById('invoiceContent').innerHTML;
                                    const originalContents = document.body.innerHTML;

                                    // Replace page content with invoice temporarily
                                    document.body.innerHTML = printContents;
                                    window.print();

                                    // Restore original page content
                                    document.body.innerHTML = originalContents;
                                    // Close modal and redirect after print
                                    $('#invoiceModal').modal('hide');
                                    window.location.href = "{{ route('purchase.index') }}";
                                }, 500);
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.removeAttr('disabled');
                        submitBtn.find('.spinner-border').remove();

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            Swal.fire({
                                title: "Error",
                                text: "You have some error in form input field. Please check input field value.",
                                type: "error"
                            });

                            const errors = xhr.responseJSON.errors;

                            $.each(errors, function(fieldKey, messages) {
                                const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');
                                let $input = $form.find(`[name="${bracketName}"]`);

                                if (!$input.length) {
                                    const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                                    $input = $form.find(`[name="${arrayName}"]`);
                                }

                                let message = messages.join(' ');

                                if ($input.length) {
                                    const actualName = $input.first().attr('name');

                                    // Remove existing error for this field
                                    $form.find(`.text-danger[data-field="${actualName}"]`).remove();

                                    // Determine if input is inside an input-group
                                    const $inputGroup = $input.closest('.input-group');

                                    if ($inputGroup.length) {
                                        // Place error *after* the entire input-group
                                        $inputGroup.after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                                    } else {
                                        // Otherwise place error directly after the input
                                        $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                                    }
                                } else {
                                    // Fallback — show error at top of form if input not found
                                    $form.prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
                                }
                            });
                        } else if(xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.errors){
                            Swal.fire({
                                title: "Error",
                                text: "Balance exceeds, please check available balance.",
                                type: "error"
                            });
                        } else if(xhr.status === 402 && xhr.responseJSON && xhr.responseJSON.errors){
                            Swal.fire({
                                title: "Error",
                                text: "Item not found or available quantity exceeds.",
                                type: "error"
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
        });
    });
</script>
@endsection
