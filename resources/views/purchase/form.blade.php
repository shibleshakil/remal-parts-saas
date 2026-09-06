@php
$url = Route::currentRouteName();
@endphp

<div class="row">
    <!-- Vehicle Selector -->
    <div class="col-md-6 form-group">
        <div>
            <label>Scan or search by barcode</label>
            <fieldset class="form-group position-relative mb-0">
                <input type="search" class="form-control form-control-xl input-xl" id="iconLeft1">
                <div class="form-control-position">
                    <i class="feather icon-search font-medium-4"></i>
                </div>
            </fieldset>
        </div>
        <div id="parts-by-barcode-div" class=" py-2">
            <select name="parts-by-barcode" id="parts-by-barcode" class="form-control select2">

            </select>

        </div>
    </div>

    <!-- Spare Parts -->
    <div class="col-md-6 form-group">
        <label for="spare_parts">Spare Parts</label>
        <select name="spare_parts" id="spare_parts" class="form-control select2">
            <option value="">Select Spare Part</option>
        </select>
        @error('spare_parts')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <!-- Spare Parts Selector -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="parts">Spare Parts <span class="text-danger">*</span></label>
            <div class="input-group">
                <select name="parts" id="parts" class="form-control select2">
                    <option value="">Select a Spare Part</option>
                    @foreach ($parts as $part)
                    <option value="{{ $part->id }}">
                        {{ $part->parts_name }} - {{ $part->uid }} | {{ $part->car_name }} | {{ $part->car_model }}
                    </option>
                    @endforeach
                </select>

                @can('spare-parts.create')
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" title="Add New Spare Part" data-toggle="modal"
                        data-target="#inlineSparePartForm">
                        <i class="feather icon-plus"></i>
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>


    <!-- Date Picker -->
    <div class="col-md-6 form-group">
        <label for="date">Date <span class="text-danger">*</span></label>
        <div class="input-group date datetime" id="date">
            <input type="text" name="purchase_date" id="date_input" class="form-control date-input"
                value="{{ today() }}" required/>
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Supplier Selector -->
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

                @can('supplier.create')
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" title="Add New Supplier" data-toggle="modal"
                        data-target="#inlineSupplierForm">
                        <i class="feather icon-plus"></i>
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Product Table -->
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
        <tfoot>
            <tr class="font-weight-bold">
                <td class="text-right" colspan="4">Total Amount</td>
                <td class="text-right">
                    <span id="total_cost_text">0.00</span>
                    <input type="hidden" name="total_cost" id="total_cost" value="0">
                </td>
                <td></td>
            </tr>
            <tr class="font-weight-bold d-none">
                <td class="text-right" colspan="2">Final Total</td>
                <td class="text-right" colspan="3">
                    <span
                        id="final_amount_text"></span>
                    <input type="hidden" name="final_amount" id="final_amount" class="form-control phone"
                        value="{{ old('final_amount') }}" readonly>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="paid_amount">Paid Amount</label>
        <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
            min="0" value="">

        <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
        <small id="paidError" class="text-danger pt-1" style="display:none;">Paid amount cannot exceed total purchase amount!</small>
    </div>

    <div class="col-md-3 form-group">
        <label> Due Amount</label>
        <input type="number" class="form-control total-parts-price text-info" id="dueAmount" readonly>
    </div>

    <div class="col-md-6 form-group">
        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>

            <select name="payment_method" id="payment_method" class="form-control select2">
                <option value="">Select a Payment Method</option>
                <option value="cash">
                    Cash</option>
                <option value="bank_transfer">
                    Bank Transfer</option>
                <option value="cheque">
                    Cheque</option>
                <option value="due" {{ old('payment_method') == 'due' ? 'selected' : '' }}>Due</option>
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

                @can('banks.create')
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" title="Add New Customer" data-toggle="modal"
                        data-target="#inlineBankForm">
                        <i class="feather icon-plus"></i>
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-12 form-group">
        <label>Add Attachment</label>
        <input type="file" class="form-control" id="attachment" multiple name="attachment[]" accept=".pdf, .jpg, .jpeg, .png, .webp">
    </div>

    <div class="col-md-12 form-group">
        <label>Note</label>
        <textarea class="form-control" name="note" rows="3">{{ old('note') }}</textarea>
    </div>
</div>
