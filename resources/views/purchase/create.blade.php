@extends('layouts.app')
@section('title', 'Add Purchase')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('purchase.index') }}" class="btn btn-primary">
                    {{ __('Purchase List') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Purchase') }}</li>
                    <li class="breadcrumb-item active">{{ __('Add Purchase') }}</li>
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
                            <h4 class="card-title">Add Purchase</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="purchaseForm" action="{{ route('purchase.store') }}" method="post" class="form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-body">
                                        @include('purchase.form')
                                    </div>
                                    <div class="form-actions right" >
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

@include('supplier.modal')
@include('spare-parts.modal')
@include('sale.bank_modal')

@endsection
@section('script')

<script>
    let ignoreVehicleChange = false;
    let rowIndex = 0;

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


        $parts.add($carParts).add('#parts-by-barcode').on('change', function() {
            const $select = $(this);
            const selected = $select.find('option:selected');
            const itemId = selected.val();

            const itemName = selected.text();
            const availableQty = parseInt(selected.data('available-qty')) || 0;
            const unitPrice = parseFloat(selected.data('unit-price')) || 0;
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';

            if (itemId) {
                if (addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, $vehicle
                        .val())) {
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
            const itemType = $select.attr('id') === 'car_spare_parts' ? 'car_spare_part' : 'spare_part';

            if (itemId) {
                if (addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, $spareParts.val())) {
                    $select.val('').trigger('change');
                }
            }
        });

        function addItemToTable(itemId, itemName, unitPrice, availableQty, itemType, vehicleId = null) {


            let found = false;
            itemId = parseInt(itemId);
            itemName = itemName.split('|');

            itemActualPrice = unitPrice;

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
            const total = parseFloat($('#total_cost').val()) || 0;
            const discount = parseFloat($('#discount_amount').val()) || 0;
            const type = $('#discount_type').val();
            const finalAmount = type === 'percentage' ? total - (total * discount / 100) : total - discount;
            $('#final_amount_text').text(finalAmount.toFixed(2));
            $('#final_amount').val(finalAmount.toFixed(2));
        }

        $('#discount_amount, #discount_type').on('input change', function() {
            calculateFinalAmount();
        });

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
                    const qty = (item.available_quantity ?? 0);
                    const itemName = `${item.parts_name} - ${item.uid} | ${item.car_name} | ${item.car_model} | |`; // thos || for adjust string
                    addItemToTable(item.id, itemName, item.unit_price, qty, item.vehicle_id ? 'car_spare_part' : 'spare_part', item.vehicle_id);
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
            let total_parts_price = parseFloat($('#final_amount').val()) || 0;
            let paid_amount = parseFloat($('#paid_amount').val()) || 0;

            if(total_parts_price > 0 && paid_amount > 0){
                $('#dueAmount').val(total_parts_price - paid_amount);
            } else {
                $('#dueAmount').val(total_parts_price); // optional: reset to total if paid = 0
            }
        });

        $(function() {
            const $form = $('#purchaseForm');
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
                                    window.location.href = "{{ route('purchase.index') }}";
                                }, 500);
                            });
                        }
                    },
                    error: function(xhr) {
                        $form.find('[data-field]').remove();
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

                                let message = messages.join(' '); // combine messages if multiple


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
                        } else if(xhr.status === 403) {
                            Swal.fire({
                                title: "Error",
                                text: 'Balance exceeds, please check available balance',
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
