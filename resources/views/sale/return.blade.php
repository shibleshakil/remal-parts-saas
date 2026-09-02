@extends('layouts.app')
@section('title', 'Return / Exchange Sale')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('sales.index') }}" class="btn btn-primary">
                    {{ __('Sale list') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="col-md-6 col-12">
            <div class="breadcrumb-wrapper float-right">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Setup') }}</li>
                    <li class="breadcrumb-item">{{ __('Sale') }}</li>
                    <li class="breadcrumb-item active">{{ __('Return / Exchange Sale') }}</li>
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
                            <h4 class="card-title">Return / Exchange Sale #{{ $sale->invoice_no }}</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body">
                                <form id="return-form-input" action="{{ route('sales.return', $sale->id) }}" method="POST" class="form">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <!-- Customer Info -->
                                            <div class="col-md-6 form-group">
                                                <label>Customer</label>
                                                <input type="text" class="form-control" id="customer" value="{{ $sale->customer ? $sale?->customer?->name : 'walking customer' }}" readonly>
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
                                                        <th class="text-center">Sold Qty</th>
                                                        <th class="text-center">Unit Price</th>
                                                        <th class="text-center">Return Qty</th>
                                                        <th class="text-center">Return Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="return-rows">
                                                    @foreach ($sale->items as $index => $item)
                                                    <tr id="return-row-{{ $index }}" data-bs-toggle="tooltip" title="{{optional($item->purchaseLines)->target_sell_price}}">
                                                        <td>
                                                            <input type="checkbox" name="return_items[{{ $index }}][selected]" class="return-item-checkbox" data-index="{{ $index }}">
                                                        </td>
                                                        <td>
                                                            @php
                                                            $itemName = '';
                                                            if($item->item_id){
                                                                $itemName = $item->partsDetail?->parts_name . ' - ' . $item->partsDetail?->uid . ' | ' . $item->partsDetail?->car_name . ' | ' . $item->partsDetail?->car_model;
                                                            } else {
                                                                $itemName = $item->item_name;
                                                            }
                                                            @endphp
                                                            <input type="hidden" name="return_items[{{ $index }}][sale_detail_id]" value="{{ $item->id }}">
                                                            {{ $itemName }}
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-center unit-price">{{ $item->unit_price ?? 0 }}</td>
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
                                                        <td colspan="5" class="text-right">Total Refund Subtotal</td>
                                                        <td class="text-right">
                                                            <span id="refund_subtotal_text">0.00</span>
                                                        </td>
                                                    </tr>
                                                     <tr class="font-weight-bold">
                                                        <td colspan="5" class="text-right">VAT Amount ({{ $sale->vat  * 100 }}% <span id="vat_type_text">{{ $sale->vat_type }}</span>)</td>
                                                        <td class="text-right">
                                                            <span id="vat_amount_text">0.00</span>
                                                        </td>
                                                    </tr>

                                                    <tr class="font-weight-bold">
                                                        <td colspan="5" class="text-right">Total Refund With VAT</td>
                                                        <td class="text-right">
                                                            <span id="refund_amount_text">0.00</span>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <!-- Add Items to Exchange -->
                                            <h3 class="text-center mt-2 mb-3">Add Items to Exchange</h3>
                                            <div class="row mx-0">
                                                <!-- Vehicle Selector -->
                                                <div class="col-md-12">
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

                                                <!-- Vehicle Selector -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="vehicle">Vehicle <span class="text-danger">*</span></label>
                                                        <select name="vehicle" id="vehicle" class="form-control select2">
                                                            <option value="">Select a Vehicle</option>
                                                            @foreach ($vehicles as $vehicle)
                                                            <option value="{{ $vehicle->id }}"
                                                                {{ old('vehicle') == $vehicle->id ? 'selected' : '' }}>
                                                                {{ $vehicle->name }} - {{ $vehicle->uid }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @error('vehicle')
                                                        <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                                                    <!-- Car Spare Parts -->
                                                    <div class="form-group">
                                                        <label for="car_spare_parts">Car Spare Parts<span class="text-danger">*</span></label>
                                                        <select name="car_spare_parts" id="car_spare_parts" class="form-control select2">
                                                            <option value="">Select Car Spare Part</option>
                                                        </select>
                                                        @error('car_spare_parts')
                                                        <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <!-- Spare Parts Selector -->
                                                    <div class="form-group">
                                                        <label for="parts">Spare Parts <span class="text-danger">*</span></label>
                                                        <select name="parts" id="parts" class="form-control select2">
                                                            <option value="">Select a Spare Part</option>
                                                            @foreach ($parts as $part)
                                                            <option value="{{ $part->id }}" data-unit-price="{{ $part->unit_price }}"
                                                                data-available-qty="{{ $part->available_quantity }}" data-target-sell-price="{{ $part->target_sell_price ?? $part->unit_price }}"
                                                                {{ old('parts') == $part->id ? 'selected' : '' }}>
                                                                {{ $part->parts_name }} - {{ $part->uid }} | {{ $part->car_name }} | {{ $part->car_model }} |
                                                                {{ number_format($part->unit_price, 2) }}|{{ $part->available_quantity }}
                                                            </option>
                                                            @endforeach
                                                            <option value="0" data-unit-price="0"
                                                                data-available-qty="">
                                                                Scrap
                                                            </option>
                                                        </select>
                                                        @error('parts')
                                                        <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                                                    <!--Spare Parts purchase line  -->
                                                    <div class="form-group">
                                                        <label for="spare_parts">Spare Parts From Purchase Line</label>
                                                        <select name="spare_parts" id="spare_parts" class="form-control select2">
                                                            <option value="">Select Spare Part</option>
                                                        </select>
                                                        @error('spare_parts')
                                                        <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <table class="table input-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 30%;">Item Name</th>
                                                                <th style="width: 15%;">Available Quantity</th>
                                                                <th style="width: 15%;">Unit Price</th>
                                                                <th style="width: 15%;">Quantity</th>
                                                                <th style="width: 15%;">Total</th>
                                                                <th style="width: 10%;">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="product-rows">

                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="font-weight-bold">
                                                                <td class="text-right" colspan="4">Total Exchange Subtotal</td>
                                                                <td class="text-right">
                                                                    <span id="total_exchange_subtotal_text">0.00</span>
                                                                </td>
                                                                <td></td>
                                                            </tr>


                                                            <tr class="font-weight-bold">
                                                                <!-- <td class="" colspan="4">
                                                                    <div class="float-right">
                                                                        <div class="input-group">
                                                                            <div class="input-group-append">

                                                                                <select name="vat_type" id="vat_type" class="form-control select" required>
                                                                                    <option value="inclusive" {{ isset($sale) && $sale->vat_type == 'inclusive' ? 'selected' : '' }}>
                                                                                        Vat Amount ({{ isset($sale) ? $sale->vat * 100 : config('app.vat')  * 100 }}% Inclusive)
                                                                                    </option>

                                                                                    <option value="exclusive" {{ isset($sale) && $sale->vat_type == 'exclusive' ? 'selected' : '' }}>
                                                                                        Vat Amount ({{ isset($sale) ? $sale->vat * 100 : config('app.vat')  * 100 }}% Exclusive)
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td> -->
                                                                <td colspan="4" class="text-right">
                                                                    VAT Amount ({{ $sale->vat  * 100 }}% <span id="vat_type_text">{{ $sale->vat_type }}</span>)
                                                                    <input type="hidden" value="{{ $sale->vat_type }}" name="vat_type" id="vat_type">
                                                                </td>
                                                                <td class="text-right">
                                                                    <span id="total_exchange_vat_amount">0.00</span>
                                                                    <input type="hidden" name="vat_amount" id="vat_amount" value="0">
                                                                </td>
                                                                <td>

                                                                </td>
                                                            </tr>

                                                            <tr class="font-weight-bold">
                                                                <td class="text-right" colspan="4">Total Exchange Amount</td>
                                                                <td class="text-right">
                                                                    <span id="total_cost_text">0.00</span>
                                                                    <input type="hidden" name="total_cost" id="total_cost" value="0">
                                                                </td>
                                                                <td></td>
                                                            </tr>

                                                            <tr class="font-weight-bold">
                                                                <td class="text-right" colspan="4">Previous Due Amount</td>
                                                                <td class="text-right">
                                                                    @php
                                                                        $dueAmount = ($sale->final_amount - $sale->paid_amount) ?? 0;
                                                                    @endphp
                                                                    <input type="hidden" id="previous_due_amount" name="previous_due_amount" value="{{ $dueAmount }}">
                                                                    <span id="previous_due_amount_text">{{ number_format($dueAmount, 2) }}</span>
                                                                </td>
                                                                <td></td>
                                                            </tr>

                                                            <tr class="font-weight-bold bg-secondary text-light">
                                                                <td class="text-right" colspan="4"><span id="receiverText">{{$dueAmount > 0 ? 'Total Payble' : 'Total Refund'}} Amount</span></td>
                                                                <td class="text-right">
                                                                    <span id="final_amount_text">{{ number_format(ABS(0 - $dueAmount), 2) }}</span>
                                                                    <input type="hidden" name="final_amount" id="final_amount" value="{{ number_format(ABS(0 - $dueAmount), 2) }}">
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                <div class="col-md-3 form-group">
                                                    <label id="paid_amount_label">{{$dueAmount > 0 ? 'Paid' : 'Refund'}} Amount</label>
                                                    <input type="number" class="form-control" name="{{$dueAmount > 0 ? 'paid_amount' : 'refund_amount'}}" id="paid_amount" step="any"
                                                        min="0" value="">
                                                </div>

                                                <div class="col-md-3 form-group">
                                                    <label>New Due Amount</label>
                                                    <input type="number" class="form-control text-info" id="dueAmount" name="dueAmount" readonly>
                                                </div>

                                                <div class="col-md-6 form-group">
                                                    <label for="payment_method">Payment Method <span class="text-danger">*</span></label>

                                                    <select name="payment_method" id="payment_method" class="form-control select2">
                                                        <!-- <option value="">Select a Payment Method</option> -->
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
                                                                <button class="btn btn-primary" type="button" title="Add New Customer" data-toggle="modal"
                                                                    data-target="#inlineBankForm">
                                                                    <i class="feather icon-plus"></i>
                                                                </button>
                                                            </div>
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
    $(function () {
        $('.datetime').datetimepicker({ format: 'YYYY-MM-DD' });

        const $vehicle = $('#vehicle');
        const $carParts = $('#car_spare_parts');
        const $spareParts = $('#spare_parts');
        const $parts = $('#parts');
        const $barcode = $('#iconLeft1');
        const $carPartsWrapper = $carParts.closest('.form-group').hide();
        const $sparePartsWrapper = $spareParts.closest('.form-group').hide();
        const select2Parent = $('.content-wrapper');
        let isVatTypeChanged = true;
        const vatRate = parseFloat({{ $sale->vat }}) || 0;


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

        $vehicle.on('change', function() {

            const vehicleId = $(this).val();
            $carParts.empty().append('<option value="">Loading...</option>');

            if (!vehicleId) {
                $carPartsWrapper.hide();
                $carParts.html('<option value="">Select Car Spare Part</option>');
                return;
            }


            $.get(`{{ route('sales.getSpareParts', ['-a']) }}`.replace('-a', vehicleId), function(data) {

                $carPartsWrapper.show();
                $carParts.select2('destroy');
                $carParts.empty().append('<option value="">Select Car Spare Part</option>');

                data.forEach(part => {
                    const qty = existingQuantities[part.id] ?? (part.available_quantity ?? 0);
                    $carParts.append(
                        `<option value="${part.id}" data-available-qty="${qty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? part.unit_price }">${part.parts_name} - ${part.uid} | ${part.car_name} | ${part.car_model} | ${part.unit_price} | ${qty}</option>`
                    );
                });

                $carParts.append(`<option value="0" data-available-qty="">Scrap</option>`);
                $carParts.select2({
                    dropdownParent: select2Parent
                });
            }).fail(function() {
                $carParts.html('<option value="">Failed to load</option>');
                Swal.fire('Error', 'Failed to load spare parts.', 'error');
            });
        });

        $parts.add($carParts).add('#parts-by-barcode').on('change', function() {
            const $select = $(this);
            const selected = $select.find('option:selected');
            const itemId = selected.val();

            const itemName = selected.text();
            const availableQty = parseInt(selected.data('available-qty')) || 0;
            const unitPrice = parseFloat(selected.data('unit-price')) || 0;
            const targetPrice = parseFloat(selected.data('target-sell-price'));
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';

            if (itemId) {
                if (addItemToTable(itemId, itemName, unitPrice, targetPrice, availableQty, itemType, $vehicle.val())) {
                    $select.val('').trigger('change');

                }
            }
        });

        $parts.add($spareParts).add('#parts-by-barcode').on('change', function() {
            const $select = $(this);
            const selected = $select.find('option:selected');
            const itemId = selected.val();


            const itemName = selected.text();
            const availableQty = parseInt(selected.data('available-qty')) || 0;
            const unitPrice = parseFloat(selected.data('unit-price')) || 0;
            const targetPrice = parseFloat(selected.data('target-sell-price'));
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';

            if (itemId) {
                if (addItemToTable(itemId, itemName, unitPrice, targetPrice, availableQty, itemType, $spareParts.val())) {
                    $select.val('').trigger('change');
                }
            }
        });

        function addItemToTable(itemId, itemName, unitPrice, targetPrice = 0, availableQty, itemType, vehicleId = null) {
            let found = false;
            let vatType = $('#vat_type').val();
            itemId = parseInt(itemId);
            itemName = itemName.split('|');

            if (itemName.length > 2) {
                itemName.splice(-2, 2);
            }

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

                    if (currentQty < availableQty) {
                        currentQty++;
                        qtyInput.val(currentQty);
                        costInput.val((unitPriceInput * currentQty).toFixed(2));
                        updateTotalCost();
                    } else {
                        Swal.fire('Quantity Exceeded',
                            `Max quantity (${availableQty}) reached for ${itemName}.`, 'warning');
                    }

                    found = true;
                    return false;
                }
            });

            let itemActualPrice = unitPrice;

            // adding vat to unit price
            if(vatType === 'exclusive') {
                // Convert inclusive price to exclusive price
                unitPrice = unitPrice;
            } else if(vatType === 'inclusive') {
                // Convert exclusive price to inclusive price
                unitPrice = unitPrice - ((unitPrice * vatRate) / (1 + vatRate));
            }


            if (found) return true;

            if (itemId !== 0 && availableQty <= 0) {
                Swal.fire('Stock Out', `${itemName} is currently out of stock.`, 'warning');
                return true;
            }

            // Declare rowIndex globally or determine it dynamically
            if (typeof rowIndex === 'undefined') {
                rowIndex = $('#product-rows tr').length;
            }

            const vehicleInput = (itemType === 'car_spare_part' && vehicleId) ?
                `<input type="hidden" name="products[${rowIndex}][vehicle_id]" value="${vehicleId}">` :
                '';

            const row = `
                <tr id="row-${rowIndex}" data-bs-toggle="tooltip" title="${targetPrice}">
                    <td>
                        <input type="hidden" name="products[${rowIndex}][item_id]" value="${itemId}" data-item-type="${itemType}">
                        <input type="hidden" name="products[${rowIndex}][item_type]" value="${itemType}">
                        <input type="hidden" id="itemNameHidden-${rowIndex}" name="products[${rowIndex}][item_name]" value="${itemName}">
                        <input type="hidden" name="products[${rowIndex}][item_actual_price]" value="${itemActualPrice}">
                        ${vehicleInput}
                        ${
                            itemId == 0
                                ? `${itemName}
                                <input type="text"
                                        class="form-control mt-1 item-name-input"
                                        placeholder="Add Item Details"
                                        data-row="${rowIndex}"
                                        value=""
                                        oninput="updateItemName(this)">`
                                : itemName
                        }

                    </td>
                    <td class="text-center">${itemId !== 0 ? availableQty : '∞'}</td>
                    <td><input type="number" name="products[${rowIndex}][unit_price]" class="form-control unit-price" value="${unitPrice.toFixed(2)}" min="0" step="any" oninput="updateRowCost(${rowIndex})"></td>
                    <td><input type="number" name="products[${rowIndex}][quantity]" class="form-control qty" value="1" min="1" ${itemId !== 0 ? `max="${availableQty}"` : ''} step="any" oninput="updateRowCost(${rowIndex})"></td>
                    <td><input type="text" class="form-control cost" name="products[${rowIndex}][cost]" id="cost-${rowIndex}" value="${unitPrice.toFixed(2)}" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow('row-${rowIndex}')">×</button></td>
                </tr>`;

            $('#product-rows').append(row);
            rowIndex++;

            updateTotalCost();
            return true;
        }


        window.updateItemName = function(inputElement) {
            const rowIndex = inputElement.getAttribute('data-row');
            const hiddenInput = document.getElementById(`itemNameHidden-${rowIndex}`);
            if (!hiddenInput) return;

            // Always use the original base name, not the current value
            let baseName = inputElement.getAttribute('data-base-name');
            if (!baseName) {
                baseName = hiddenInput.value; // first-time fallback
                inputElement.setAttribute('data-base-name', baseName);
            }

            const newValue = inputElement.value.trim();
            hiddenInput.value = newValue ? `${baseName} - ${newValue}` : baseName;
        };


        window.updateRowCost = function(index) {
            const row = $(`#row-${index}`);
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            let quantity = parseInt(row.find('.qty').val()) || 0;
            const maxQtyAttr = row.find('.qty').attr('max');
            const maxQty = maxQtyAttr ? parseInt(maxQtyAttr) : null;

            if (maxQty !== null && quantity > maxQty) {
                Swal.fire('Quantity Exceeded', `Cannot exceed stock (${maxQty}).`, 'warning');
                quantity = maxQty;
                row.find('.qty').val(quantity);
            }

            row.find('.cost').val((unitPrice * quantity).toFixed(2));
            updateTotalCost();
        };


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
                return;
            }

            const cost = (qty * unitPrice).toFixed(2);
            $row.find('.return-cost').val(cost);
            updateTotalRefund();
        }


        window.updateTotalRefund = function () {
            let total = 0;

            const prevDue = parseFloat($('#previous_due_amount').val()) || 0;
            const totalCost = parseFloat($('#total_cost').val()) || 0;

            $('.return-cost').each(function () {
                total += parseFloat($(this).val()) || 0;
            });

            $('#refund_subtotal_text').text(total.toFixed(2));

            const vatRate = parseFloat({{ $sale->vat }}) || 0;
            const vatType = $('#vat_type').val();

            let vatAmount = 0;
            let refundAmount = 0;

            if (vatType === 'inclusive') {
                // Refund total already includes VAT
                vatAmount = total * vatRate;
                refundAmount = total + vatAmount;
            } else if (vatType === 'exclusive') {
                // VAT is added on top of refund subtotal
                vatAmount = total * vatRate;
                refundAmount = total + vatAmount;
            }


            $('#vat_amount_text').text(vatAmount.toFixed(2));
            $('#refund_amount_text').text(refundAmount.toFixed(2));

            const finalAmount = Math.abs((prevDue + totalCost) - refundAmount);

            $('#final_amount_text').text(finalAmount.toFixed(2));
            $('#final_amount').val(finalAmount.toFixed(2));

            calculateFinalAmount();
        };

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

        window.removeRow = function(rowId) {
            $(`#${rowId}`).remove();
            updateTotalCost();
        };

        // $('#vat_type').on('change', function () {
        //     firstLoad = false;
        //     isVatTypeChanged = true;

        //     $('#vat_type_text').text($(this).val());

        //     updateItemRow($(this).val());

        //     isVatTypeChanged = false;

        //     updateTotalCost();
        // });

        // function updateItemRow(vatType) {

        //     $('#product-rows tr').each(function () {

        //         const unitPriceInput = $(this).find('.unit-price');
        //         const qtyInput = $(this).find('.qty');
        //         const totalPriceInput = $(this).find('.cost');

        //         if (unitPriceInput.length) {
        //             let price = parseFloat(unitPriceInput.val()) || 0;
        //             let totalCost = 0;

        //             if (vatType === 'exclusive') {
        //                 // Convert inclusive price to exclusive price
        //                 price = price - (price * vatRate / (1 + vatRate));

        //             } else if (vatType === 'inclusive') {
        //                 // Convert exclusive price to inclusive price
        //                 price = price + (price * vatRate);
        //             }

        //             totalCost = price * qtyInput.val();

        //             totalPriceInput.val(totalCost.toFixed(2));
        //             unitPriceInput.val(price.toFixed(2));
        //         }
        //     });

        //     let returnSubtotal = 0;
        //     $('#return-rows tr').each(function () {
        //         const returnUnitPriceInput = $(this).find('.unit-price');
        //         const returnQtyInput = $(this).find('.return-qty');
        //         const returnTotalPriceInput = $(this).find('.return-cost');

        //         if (returnUnitPriceInput.length) {
        //             let returnPrice = parseFloat(returnUnitPriceInput.text()) || 0;
        //             let returnTotalCost = 0;

        //             if (vatType === 'exclusive') {
        //                 // Convert inclusive price to exclusive price
        //                 returnPrice = returnPrice - (returnPrice * vatRate / (1 + vatRate));

        //             } else if (vatType === 'inclusive') {
        //                 // Convert exclusive price to inclusive price
        //                 returnPrice = returnPrice + (returnPrice * vatRate);
        //             }

        //             returnTotalCost = returnPrice * returnQtyInput.val();

        //             returnSubtotal += returnTotalCost;

        //             returnUnitPriceInput.text(returnPrice.toFixed(2));
        //             returnTotalPriceInput.val(returnTotalCost.toFixed(2));
        //         }
        //     });

        //     $('#refund_subtotal_text').text(returnSubtotal.toFixed(2));
        // }

        function updateTotalCost() {
            let total = 0;

            $('.cost').each(function () {
                total += parseFloat($(this).val()) || 0;
            });

            $('#total_exchange_subtotal_text').text(total.toFixed(2));

            const vatRate = parseFloat({{ $sale->vat }}) || 0;
            let vatType = $('#vat_type').val();

            let vatAmount = 0;
            let totalCost = 0;

            if (vatType === 'inclusive') {
                // Total already includes VAT
                vatAmount = total * vatRate;
                totalCost = total + vatAmount;
            } else {
                // Add VAT on top of subtotal
                vatAmount = total * vatRate;
                totalCost = total + vatAmount;
            }

            $('#total_exchange_vat_amount').text(vatAmount.toFixed(2));
            $('#total_cost_text').text(totalCost.toFixed(2));
            $('#total_cost').val(totalCost.toFixed(2));

            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const total = parseFloat($('#total_cost').val()) || 0;
            const discount = parseFloat($('#discount_amount').val()) || 0;
            const type = $('#discount_type').val();
            const returnAmount = parseFloat($('#refund_amount_text').text()) || 0;
            const finalAmount = type === 'percentage' ? total - (total * discount / 100) : total - discount;
            const prevDue = parseFloat($('#previous_due_amount').val());
            const finalPaybleAmount = (prevDue + finalAmount) - returnAmount ;
            const calculatedFinalPaybleAmount = Math.abs(finalPaybleAmount);

            $('#paid_amount_text').text(calculatedFinalPaybleAmount.toFixed(2));
            $('#paid_amount').val(calculatedFinalPaybleAmount.toFixed(2));
            $('#final_amount_text').text(calculatedFinalPaybleAmount.toFixed(2));
            $('#final_amount').val(calculatedFinalPaybleAmount.toFixed(2));

            if(finalPaybleAmount < 0){
                $('#paid_amount_label').html('Refund Amount');
                $('#receiverText').html('Total Refund Amount');
                $('#paid_amount').attr('name', 'refund_amount');
            } else {
                 $('#paid_amount_label').html('Paid Amount');
                 $('#receiverText').html('Total Payble Amount');
                 $('#paid_amount').attr('name', 'paid_amount');
            }


            calculateNewDue();
        }


        $('#discount_amount, #discount_type').on('input change', function() {
            calculateFinalAmount();
        });

        $('#payment_method').on('change', function() {
            if ($(this).val() === 'bank_transfer') {
                $('#bank_list_wrapper').show(); // Show bank list
            } else {
                $('#bank_list_wrapper').hide(); // Hide when not bank transfer
                $('#bank_id').val(''); // Reset bank selection
            }
        });

        function calculateNewDue () {
            let total_parts_price = parseFloat($('#final_amount').val()) || 0;
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


        function getSparePartsByBarcode() {
            const barcode = $barcode.val().trim();
            $barcode.val('');
            if (!barcode) return;

            const url = `{{ route('sales.getDataByBarcode', ['-a']) }}`.replace('-a', barcode);

            $.get(url, function(res) {
                if (!res.success) {
                    Swal.fire('Error', res.message || 'No parts found for the provided barcode.',
                        'warning');
                    return;
                }

                if (res.single) {
                    const item = res.data;
                    const qty = (item.available_quantity ?? 0);
                    const itemName = `${item.parts_name} - ${item.uid} | ${item.car_name} | ${item.car_model} | |`; // thos || for adjust string
                    addItemToTable(item.id, itemName, item.unit_price, item.target_sell_price, qty, item.vehicle_id ? 'car_spare_part' : 'spare_part', item.vehicle_id);
                } else if (res.car_id) {
                    ignoreVehicleChange = true;
                    $vehicle.val(res.car_id).trigger('change');

                    $carPartsWrapper.show();
                    $carParts.select2('destroy');
                    $carParts.empty().append('<option value="">Select Car Spare Part</option>');

                    res.data?.forEach(part => {
                        const qty = (part.available_quantity ?? 0);
                        $carParts.append(
                            `<option value="${part.id}" data-available-qty="${qty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? part.unit_price }">${part.parts_name} - ${part.uid} | ${part.car_name} | ${part.car_model} | ${part.unit_price} | ${qty}</option>`
                        );
                    });

                    $carParts.append(`<option value="0" data-available-qty="">Scrap</option>`);
                    $carParts.select2({
                        dropdownParent: select2Parent
                    });
                    $carParts.select2('open');
                    ignoreVehicleChange = false;
                } else {


                    $sparePartsWrapper.show();
                    $spareParts.select2('destroy');
                    $spareParts.empty().append('<option value="">Select Spare Part</option>');

                    res.data.forEach(part => {
                        const qty = (part.available_quantity ?? 0);
                        $spareParts.append(
                            `<option value="${part.id}" data-available-qty="${qty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? part.unit_price }">${part.parts_name} - ${part.uid} | ${part.car_name} | ${part.car_model} | ${part.unit_price} | ${qty}</option>`
                        );
                    });


                    $spareParts.select2({
                        dropdownParent: select2Parent
                    });
                    $spareParts.select2('open');
                }
            }).fail(function(xhr) {
                Swal.fire('Error', 'Parts is out of stock or not found.', 'error');
                ignoreVehicleChange = false;
            });
        }


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

                const finalAmount = parseFloat($('#final_amount').val()) || 0;
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const customer = $('#customer').val();
                const reason = $('#reason').val();

                if(customer == "" && paidAmount < finalAmount){
                    Swal.fire({
                        title: 'Customer Alert',
                        text: 'Paid amount must be equal to final amount for walking customer!'
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
                                text: response.message || 'Sale return & exchange processed successfully.',
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
                                    window.location.href = "{{ route('sales.index') }}";
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
                        }else if(xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.errors){
                            Swal.fire({
                                title: "Error",
                                text: "Balance exceeds, please check available balance.",
                                type: "error"
                            });
                        } else if (xhr.status === 401) {
                            Swal.fire({
                                title: "Error",
                                text: xhr.responseJSON.message,
                                icon: "error"
                            });
                        }
                        else {
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
