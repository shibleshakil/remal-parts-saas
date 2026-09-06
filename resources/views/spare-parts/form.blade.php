@php
$url = \Route::currentRouteName();
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label for="parts_name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="parts_name" id="parts_name" required
            value="{{ old('parts_name', $url === 'spare-parts.edit' ? $spareParts->parts_name : '') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="car_name">Car Name <span class="text-danger">*</span></label>
        <input list="carNames" type="text" class="form-control" name="car_name" id="car_name" required
            value="{{ old('car_name', $url === 'spare-parts.edit' ? $spareParts->car_name : '') }}">

        <datalist id="carNames">
            @foreach ($carNames as $c_name)
            <option value="{{$c_name}}">
            @endforeach
        </datalist>
    </div>

    <div class="col-md-6 form-group">
        <label for="car_model">Car Model <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="car_model" id="car_model" required
            value="{{ old('car_model', $url === 'spare-parts.edit' ? $spareParts->car_model : '') }}">
    </div>
    @if ($url === 'spare-parts.edit')
</div>
@if (sizeof($spareParts->purchaseLines) > 0)
@foreach ($spareParts->purchaseLines as $index => $purchaseLine)
<h2>Purchase Line: {{ $loop->iteration }}</h2>
<div class="row purchase-line">
    <input type="hidden" name="purchase_line_id[]" value="{{ $purchaseLine->id }}">
    <input type="hidden" name="transaction_id[]" value="{{ $purchaseLine->transaction_id }}">

    <div class="col-md-6 form-group">
        <label>Unit Price <span class="text-danger">*</span></label>
        <input type="number" class="form-control unit-price" name="unit_price[]" step="any" min="0"
            required value="{{ old('unit_price.' . $index, $url === 'spare-parts.edit' ? $purchaseLine->unit_price : '') }}">
    </div>

    <div class="col-md-6 form-group">
        <label>Quantity <span class="text-danger">*</span></label>
        <input type="number" class="form-control parts-quantity" name="parts_quantity[]" step="any" min="0" required
            value="{{ $url === 'spare-parts.edit' ? $purchaseLine->quantity : '' }}">
    </div>

    <div class="col-md-6 form-group">
        <label>Total Purchase Price <span class="text-danger">*</span></label>
        <input type="number" class="form-control total-parts-price" name="total_parts_price[]" step="any"
            readonly required
            value="{{ $url === 'spare-parts.edit' ? $purchaseLine->total_parts_price : '' }}">
    </div>

    <div class="col-md-6 form-group">
        <label>Purchase Date <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="date" name="date[]" class="form-control date-input" required
                value="{{$purchaseLine->purchase_date ?? now()->format('Y-m-d') }}" />
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <div class="col-md-6 form-group">
        <label for="target_sell_price">Target Sell Price</label>
        <input type="text" class="form-control" name="target_sell_price[]" id="target_sell_price" step="any"
            value="{{ $purchaseLine->target_sell_price }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="vehicle_target_sell_price">Supplier</label>
        <div class="input-group">
            <select class="form-control select2" name="supplier_id[]" id="supplier_id">
                <option value="">Walking Supplier</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{$purchaseLine->supplier_id == $supplier->id ? 'selected' : '' }}>
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

    <div class="col-md-6 form-group">
        <label for="paid_amount">Paid Amount <span class="text-danger">*</span></label>
        <input type="number" class="form-control paid-amount" name="paid_amount[]" id="paid_amount" step="any"
            min="0"
            value="{{$purchaseLine->transaction?->paid_amount ?? ''}}">
        <input type="hidden" class="old-paid-amount" name="old_paid_amount[]" value="{{ $purchaseLine->transaction?->paid_amount ?? '' }}">
        <small class="balance-error text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
        <small class="paid-error text-danger pt-1" style="display:none;">Paid amount cannot exceed total purchase amount!</small>
    </div>

    <div class="col-md-6 form-group">
        <label for="dueAmount"> Due Amount</label>
        <input type="number" class="form-control text-info due-amount" id="dueAmount" value="{{ $purchaseLine->transaction?->final_amount - $purchaseLine->transaction?->paid_amount }}" readonly>
    </div>

    <div class="col-md-12 payment-section">
        <div class="col-md-6 form-group pl-0">
            <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method[]" class="form-control select2 payment-method" required>
                <option value="">Select a Payment Method</option>
                <option value="cash"
                    {{$purchaseLine->transaction?->payment_method == 'cash' ? 'selected' : '' }}>
                    Cash
                </option>
                <option value="bank_transfer"
                    {{$purchaseLine->transaction?->payment_method == 'bank_transfer' ? 'selected' : '' }}>
                    Bank Transfer
                </option>
                <option value="cheque"
                    {{$purchaseLine->transaction?->payment_method == 'cheque' ? 'selected' : '' }}>
                    Cheque
                </option>
                <option value="due"
                    {{$purchaseLine->transaction?->payment_method == 'due' ? 'selected' : '' }}>
                    Due
                </option>
            </select>
        </div>

        <div class="col-md-6 pl-0">
            <div class="form-group bank-list-wrapper"
                style="{{ $url == 'spare-parts.edit' && $purchaseLine->transaction?->payment_method == 'bank_transfer' ? 'display:block; margin-top:10px;' : 'display:none;' }}">
                <label>Select Bank</label>
                <div class="input-group">
                    <select name="bank_id[]" class="form-control bank-id">
                        <option value="">-- Select Bank --</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}"
                            {{ $url == 'spare-parts.edit' && $purchaseLine->transaction?->bank_id == $bank->id ? 'selected' : '' }}>
                            Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}
                        </option>
                        @endforeach
                    </select>
                    @can('banks.create')
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#inlineBankForm">
                            <i class="feather icon-plus"></i>
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

</div>
@endforeach

@endif
@else
<div class="col-md-6 form-group">
    <label for="unit_price">Unit Price <span class="text-danger">*</span></label>
    <input type="number" class="form-control" name="unit_price" id="unit_price" step="any" min="0"
        required value="{{ old('unit_price', $url === 'spare-parts.edit' ? $spareParts->unit_price : '') }}">
</div>

<div class="col-md-6 form-group">
    <label for="parts_quantity">Quantity <span class="text-danger">*</span></label>
    <input type="number" class="form-control" name="parts_quantity" id="parts_quantity" step="any"
        min="0" required
        value="{{ old('parts_quantity', $url === 'spare-parts.edit' ? $spareParts->parts_quantity : '') }}"
        oninput="calculateTotal()">
</div>

<div class="col-md-6 form-group">
    <label for="total_parts_price">Total Purchase Price <span class="text-danger">*</span></label>
    <input type="number" class="form-control" name="total_parts_price" id="total_parts_price" step="any"
        readonly required
        value="{{ old('total_parts_price', $url === 'spare-parts.edit' ? $spareParts->total_parts_price : '') }}">
</div>

<div class="col-md-6 form-group">
    <label for="date">Purchase Date <span class="text-danger">*</span></label>
    <div class="input-group" id="date">
        <input type="date" name="date" id="date_input" class="form-control date-input" required
            value="{{ old('date', $url === 'spare-parts.edit' ? $spareParts->date : now()->format('Y-m-d')) }}" />
    </div>
</div>

<div class="col-md-6 form-group">
    <label for="target_sell_price">Target Sell Price</label>
    <input type="text" class="form-control" name="target_sell_price" id="target_sell_price" step="any"
        value="{{ old('target_sell_price', $url === 'spare-parts.edit' ? $spareParts->target_sell_price : '') }}">
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

<div class="col-md-6 form-group">
    <label for="paid_amount">Paid Amount</label>
    <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
        min="0"
        value="{{ old('paid_amount', $url === 'spare-parts.edit' ? $spareParts->paid_amount : '') }}">

    <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
    <small id="paidError" class="text-danger pt-1" style="display:none;">Paid amount cannot exceed total purchase amount!</small>
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
            {{ old('payment_method', $url == 'spare-parts.edit' ? $spareParts->payment_method : '') == 'cash' ? 'selected' : '' }}>
            Cash</option>
        <option value="bank_transfer"
            {{ old('payment_method', $url == 'spare-parts.edit' ? $spareParts->payment_method : '') == 'bank_transfer' ? 'selected' : '' }}>
            Bank Transfer</option>
        <option value="cheque"
            {{ old('payment_method', $url == 'spare-parts.edit' ? $spareParts->payment_method : '') == 'cheque' ? 'selected' : '' }}>
            Cheque</option>
        <option value="due"
            {{ old('payment_method', $url == 'spare-parts.edit' ? $spareParts->payment_method : '') == 'due' ? 'selected' : '' }}>
            Due</option>
    </select>


    <!-- Hidden bank list -->

</div>

<div class="col-md-6">
    <div class="form-group" id="bank_list_wrapper" style="{{ $url == 'spare-parts.edit' && $spareParts->payment_method == 'bank_transfer' ? 'display:block; margin-top:10px;' : 'display:none;' }}">
        <label for="bank_id">Select Bank</label>
        <div class="input-group">
            <select name="bank_id" id="bank_id" class="form-control">
                <option value="">-- Select Bank --</option>
                @foreach($banks as $bank)
                <option value="{{$bank->id}}" {{$url == 'spare-parts.edit' && $spareParts->bank_id == $bank->id ? 'selected' : '' }}>Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
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
</div>
@endif

<div class="row">
    <div class="col-md-12 form-group">
        <label for="note">Note</label>
        <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $url === 'spare-parts.edit' ? $spareParts->note : '') }}</textarea>
    </div>
</div>
