@extends('layouts.app')
@section('title', 'Edit Quote')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('quotes.create')
                <a href="{{ route('quotes.index') }}" class="btn btn-primary">
                    {{ __('Quote list') }} <i class="feather icon-eye"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Setup') }}</li>
                    <li class="breadcrumb-item">{{ __('Quote') }}</li>
                    <li class="breadcrumb-item active">{{ __('Edit Quote') }}</li>
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
                            <h4 class="card-title">Edit Quote</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="updateQuote" action="{{ route('quotes.update', $quote->id) }}" method="post" class="form"
                                    enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        <input type="hidden" name="id" value="{{ $quote->id }}">
                                        @include('quote.form')
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
        <!--/ basic form table -->
    </div>

    <!--/ basic form table -->
    @include('sale.modal')
    @include('sale.bank_modal')

    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body" id="invoiceContent">
                    <!-- Invoice will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

<script>
    let ignoreVehicleChange = false;
    let rowIndex = 0;

    const oldProducts = @json(isset($quote) ? $quote->items ?? [] : []);
    const existingQuantities = @json(isset($existingQuantities) ? $existingQuantities : []);

    $(document).ready(function() {
        $('#vehicle, #car_spare_parts, #parts').select2();
        $('.datetime').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#parts-by-barcode-div').hide();
        const carPartsWrapper = $('#car_spare_parts').closest('.form-group').hide();

        $('#vehicle').on('change', function() {
            if (ignoreVehicleChange) return;
            const vehicleId = $(this).val();
            const carPartsSelect = $('#car_spare_parts');
            carPartsSelect.empty().append('<option value="">Loading...</option>');

            if (!vehicleId) {
                carPartsWrapper.hide();
                carPartsSelect.html('<option value="">Select Car Spare Part</option>');
                return;
            }

            carPartsWrapper.show();

            $.get("{{ route('sales.getSpareParts', ['-a']) }}".replace('-a', vehicleId), function(
                data) {
                carPartsSelect.empty().append(
                    '<option value="">Select Car Spare Part</option>');
                data.forEach(part => {
                    const availableQty = existingQuantities[part.id] ?? (part.available_quantity ?? 0);
                    carPartsSelect.append(
                        `<option value="${part.id}" data-available-qty="${availableQty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? 0}">${part.parts_name} - ${part.uid} | ${part.car_name} - ${part.car_model} | ${part.unit_price} | ${availableQty}</option>`
                    );
                });
                carPartsSelect.append(`<option value="0" data-available-qty="">Scrap</option>`);
            }).fail(function() {
                carPartsSelect.html('<option value="">Failed to load</option>');
            });
        });

        $('#parts, #car_spare_parts, #parts-by-barcode').on('change', function() {
            const $select = $(this);
            const selected = $select.find('option:selected');
            const itemId = selected.val();
            const itemName = selected.text();
            const availableQty = parseInt(selected.data('available-qty')) || 0;
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';
            const unitPrice = parseFloat(selected.data('unit-price')) || 0;
            const targetSellPrice = parseFloat(selected.data('target-sell-price')) || 0;

            if (itemId) {
                if (addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, $('#vehicle').val(), targetSellPrice)) {
                    $select.val('').trigger('change');
                }
            }
        });

        function addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, vehicleId = null, targetSellPrice = 0) {
            let found = false;

            const parts = itemName.split(' - ');
            const baseName = parts.shift();
            const extraDetails = parts.join(' - ');

            itemName = itemName.split('|');

            if (itemName.length > 2) {
                itemName.splice(-2, 2);
            }
            itemName = itemName.join('|');

            $('input[name^="products"][name$="[item_id]"]').each(function() {
                const input = $(this);
                const row = input.closest('tr');
                const existingItemId = input.val();
                const existingItemType = input.data('item-type');

                if (existingItemId == itemId && existingItemType === itemType && itemId != 0) {
                    const qtyInput = row.find('.qty');
                    const costInput = row.find('.cost');
                    const unitPriceInput = parseFloat(row.find('.unit-price').val()) || 0;
                    let currentQty = parseInt(qtyInput.val()) || 0;

                    // if (itemId === "0" || currentQty < availableQty) {
                        currentQty++;
                        qtyInput.val(currentQty);
                        costInput.val((unitPriceInput * currentQty).toFixed(2));
                        updateTotalCost();
                    // } else {
                    //     Swal.fire({
                    //         icon: 'warning',
                    //         title: 'Quantity Exceeded',
                    //         text: `Max quantity (${availableQty}) reached for ${itemName}.`
                    //     });
                    // }

                    found = true;
                    return false;
                }
            });

            if (found) return true;

            // if (itemId !== "0" && availableQty <= 0) {
            //     Swal.fire({
            //         icon: 'warning',
            //         title: 'Stock Out',
            //         text: `${itemName} is currently out of stock.`
            //     });
            //     return true;
            // }

            const vehicleInput = itemType === 'car_spare_part' && vehicleId ?
                `<input type="hidden" name="products[${rowIndex}][vehicle_id]" value="${vehicleId}">` : '';

            const row = `
                <tr id="row-${rowIndex}" title="Target Sell Price: ${targetSellPrice.toFixed(2)}">
                    <td>
                        <input type="hidden" name="products[${rowIndex}][item_id]" value="${itemId}" data-item-type="${itemType}">
                        <input type="hidden" name="products[${rowIndex}][item_type]" value="${itemType}">
                        <input type="hidden" id="itemNameHidden-${rowIndex}" name="products[${rowIndex}][item_name]" value="${itemName}">
                        ${vehicleInput}
                        ${
                            itemId == 0
                                ? `${baseName}
                                    <input type="text"
                                        class="form-control mt-1 item-name-input"
                                        placeholder="Add Item Details"
                                        data-row="${rowIndex}"
                                        data-base-name="${baseName}"
                                        value="${extraDetails}"
                                        oninput="updateItemName(this)">`
                                : itemName
                        }
                    </td>
                    <td><input type="number" name="products[${rowIndex}][unit_price]" class="form-control unit-price" value="${unitPrice.toFixed(2)}" min="0" step="any" oninput="updateRowCost(${rowIndex})"></td>
                    <td><input type="number" name="products[${rowIndex}][quantity]" class="form-control qty" value="1" min="1" step="any" oninput="updateRowCost(${rowIndex})"></td>
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

            const baseName = inputElement.getAttribute('data-base-name');
            const newValue = inputElement.value.trim();

            hiddenInput.value = newValue ? `${baseName} - ${newValue}` : baseName;
        };

        window.updateRowCost = function(index) {
            const row = $(`#row-${index}`);
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            let quantity = parseInt(row.find('.qty').val()) || 0;

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
            $('#total_cost').val(total.toFixed(2));
            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const totalCost = parseFloat($('#total_cost').val()) || 0;
            const discount = parseFloat($('#discount_amount').val()) || 0;
            const type = $('#discount_type').val();

            let finalAmount = 0;

            if (type === 'percentage') {
                if (discount > 100) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Discount',
                        text: 'Percentage discount cannot exceed 100%'
                    });
                    $('#discount_amount').val(100);
                    finalAmount = 0;
                } else {
                    finalAmount = totalCost - (totalCost * (discount / 100));
                }
            } else {
                if (discount > totalCost) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Discount',
                        text: 'Fixed discount cannot exceed total cost.'
                    });
                    $('#discount_amount').val(totalCost.toFixed(2));
                    finalAmount = 0;
                } else {
                    finalAmount = totalCost - discount;
                }
            }

            finalAmount = Math.max(finalAmount, 0);
            $('#final_amount_text').text(finalAmount.toFixed(2));
            $('#final_amount').val(finalAmount.toFixed(2));
        }

        $('#discount_amount, #discount_type').on('input change', function() {
            calculateFinalAmount();
        });

        function getSparePartsByBarcode() {

            const barcode = $("#iconLeft1").val().trim();
            $("#iconLeft1").val('');
            if (!barcode) return;

            const url = "{{ route('sales.getDataByBarcode', ['-a']) }}".replace('-a', barcode);

            $.get(url, function(res) {
                if (res.single) {
                    const item = res.data;
                    const availableQty = existingQuantities[item.id] ?? (item.available_quantity ?? 0);
                    const itemName = `${item.parts_name} - ${item.uid}`;
                    addItemToTable(item.id, itemName, item.unit_price, availableQty, item.vehicle_id ? 'car_spare_part' : 'spare_part', item.vehicle_id, item.target_sell_price ?? 0);
                } else if (res.car_id) {
                    carPartsWrapper.show();
                    ignoreVehicleChange = true;
                    $('#vehicle').val(res.car_id).trigger('change');

                    const carPartsSelect = $('#car_spare_parts');
                    carPartsSelect.empty().append('<option value="">Select Car Spare Part</option>');

                    res.data.forEach(part => {
                        const availableQty = existingQuantities[part.id] ?? (part.available_quantity ?? 0);
                        carPartsSelect.append(
                            `<option value="${part.id}" data-available-qty="${availableQty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? 0}">${part.parts_name} - ${part.uid} | ${part.car_model} | ${part.unit_price} | ${availableQty}</option>`
                        );
                    });

                    carPartsSelect.append(`<option value="0" data-available-qty="">Scrap</option>`);
                    carPartsSelect.select2('open');
                    ignoreVehicleChange = false;
                } else {
                    $sparePartsWrapper.show();
                    $spareParts.select2('destroy');
                    $spareParts.empty().append('<option value="">Select Spare Part</option>');

                    res.data.forEach(part => {
                        const qty = (part.available_quantity ?? 0);
                        $spareParts.append(
                            `<option value="${part.id}" data-available-qty="${qty}" data-unit-price="${part.unit_price}" data-target-sell-price="${part.target_sell_price ?? 0}">${part.parts_name} - ${part.uid}|${qty}</option>`
                        );
                    });


                    $spareParts.select2({
                        dropdownParent: select2Parent
                    });
                    $spareParts.select2('open');
                }
            }).fail(function(xhr) {
                console.error("Barcode fetch failed:", xhr);
                ignoreVehicleChange = false;
            });
        }

        $("#iconLeft1").on('change', getSparePartsByBarcode).focus();

        oldProducts.forEach(item => {
            const itemId = parseInt(item.purchase_line_id);
            const itemName = item.item_name ?? 'N/A';
            const itemType = item.item_type ?? 'spare_part';
            const unitPrice = parseFloat(item.unit_price) || 0;
            const quantity = parseInt(item.quantity) || 1;
            const cost = parseFloat(item.cost) || (unitPrice * quantity);
            const vehicleId = parseInt(item.vehicle_id) || item.vehicle_id || null;
            const targetSellPrice = parseFloat(item.purchase_line?.target_sell_price) || 0;

            let availableQty = Infinity;
            if (itemId !== "0") {
                const stockQty = existingQuantities[itemId] ?? 0;
                availableQty = stockQty + quantity;
            }

            addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, vehicleId, targetSellPrice);

            const row = $('#product-rows tr').last();
            row.find('.qty').val(quantity);
            row.find('.unit-price').val(unitPrice.toFixed(2));
            row.find('.cost').val(cost.toFixed(2));
        });

        const carSparePartItem = oldProducts.find(item => item.item_type === 'car_spare_part');
        if (carSparePartItem && carSparePartItem.vehicle_id) {
            ignoreVehicleChange = true;
            $('#vehicle').val(carSparePartItem.vehicle_id).trigger('change');
            ignoreVehicleChange = false;
        }

        updateTotalCost();

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

        $('#addCustomerForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.post({
                url: "{{ route('sales.customeStoreViaAjax') }}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#customer').append(
                            `<option value="${res.customer.id}" selected>${res.customer.name}</option>`
                        ).trigger('change');
                        $('#inlineForm').modal('hide');
                        $('#addCustomerForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    alert("Failed to add customer.");
                    console.error(xhr.responseJSON);
                }
            });
        });


        $(function() {
            const $form = $('#updateQuote');
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
                            icon: 'warning',
                            title: 'Bank Not Selected',
                            text: 'Please Select A Bank First!...'
                        });
                        return false;
                    }
                }

                e.preventDefault();
                const formEl = this;
                let formData = new FormData(formEl);

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
                                    window.location.href = "{{ route('quotes.index') }}";

                                    // Reattach Bootstrap JS functionality
                                    // location.reload(); // ensures everything rebinds properly

                                }, 500);
                            });
                        }
                    },
                    error: function(xhr) {
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
                        } else {
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

    $(document).on('input', '#paid_amount', function () {
        let total_parts_price = parseFloat($('#final_amount').val()) || 0;
        let paid_amount = parseFloat($('#paid_amount').val()) || 0;

        if(total_parts_price > 0 && paid_amount > 0){
            $('#dueAmount').val(total_parts_price - paid_amount);
        } else {
            $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
        }
    });
</script>
@endsection
