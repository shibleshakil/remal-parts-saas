@php
$url = \Route::currentRouteName();
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input list="names" type="text" class="form-control name-input" name="name" id="name"
            value="{{ $url == 'vehicle.edit' ? $vehicle->name : old('name') }}">

        <datalist id="names">
            @foreach ($vehicles as $v)
            <option value="{{$v}}">
                @endforeach
        </datalist>
    </div>
    <div class="col-md-6 form-group">
        <label for="model">Model <span class="text-danger">*</span></label>
        <input type="text" class="form-control model-input" name="model" id="model"
            value="{{ $url == 'vehicle.edit' ? $vehicle->model : old('model') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="chasis_no">Chasis No. <span class="text-danger">*</span></label>
        <input type="text" class="form-control chasis_no-input" name="chasis_no" id="chasis_no"
            value="{{ $url == 'vehicle.edit' ? $vehicle->chasis_no : old('chasis_no') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="engine_no">Engine No. <span class="text-danger">*</span></label>
        <input type="text" class="form-control engine_no-input" name="engine_no" id="engine_no"
            value="{{ $url == 'vehicle.edit' ? $vehicle->engine_no : old('engine_no') }}">
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="date">Date <span class="text-danger">*</span></label>
            <div class='input-group date datetime' id='date'>
                <input type='text' class="form-control date-input" name="date" id="date_input" required
                    value="{{ old('date', $url == 'vehicle.edit' ? $vehicle->date : now()->format('Y-m-d')) }}" />
                <div class="input-group-append">
                    <span class="input-group-text">
                        <span class="fa fa-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 form-group">
        <label for="total_purchase_price">Total Purchase Price <span class="text-danger">*</span></label>
        <input type="number" class="form-control total_purchase_price" name="total_purchase_price"
            id="total_purchase_price"
            value="{{ $url == 'vehicle.edit' ? $vehicle->total_purchase_price : old('total_purchase_price') }}">
    </div>


    <div class="col-md-6 form-group">
        <label for="total_vehicle_price">Total Vehicle Price <span class="text-danger">*</span></label>
        <input type="text" class="form-control total_vehicle_price" name="total_vehicle_price"
            id="total_vehicle_price"
            value="{{ $url == 'vehicle.edit' ? $vehicle->total_vehicle_price : old('total_vehicle_price') }}" readonly>
    </div>

    <div class="col-md-6 form-group">
        <label for="vehicle_target_sell_price">Target Sell Price</label>
        <input type="text" class="form-control vehicle_target_sell_price" name="vehicle_target_sell_price"
            id="vehicle_target_sell_price"
            value="{{$url == 'vehicle.edit' ? $vehicle->target_sell_price : old('vehicle_target_sell_price')}}">
    </div>

    <div class="col-md-6">
        <div class="form-group">
                <label for="vehicle_target_sell_price">Supplier</label>
            <div class="input-group">
                <select class="form-control select2" name="supplier_id" id="supplier_id">
                    <option value="">Walking Supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $url == 'vehicle.edit' ? ($vehicle->supplier_id == $supplier->id ? 'selected' : '') : old('total_purchase_price') }}>
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


    <div class="table-responsive">
        <table class="table table-bordered table-striped w-100" id="product-table">
            <thead class="">
                <tr>
                    <th style="width: 25%">Name <span class="text-danger">*</span></th>
                    <th style="width: 15%">Quantity <span class="text-danger">*</span></th>
                    <th style="width: 20%">Unit Price <span class="text-danger">*</span></th>
                    <th style="width: 20%">Target Selling Price</th>
                    <th style="width: 20%">Total Price <span class="text-danger">*</span></th>
                    <th style="width: 10%" class="text-center">Action</th>
                </tr>
            </thead>

            <tbody id="product-container">

                {{-- Vehicle Parts data --}}
                <datalist id="parts">
                    @foreach ($parts as $part_name)
                    <option value="{{$part_name}}">
                        @endforeach
                </datalist>

                @if ($url == 'vehicle.edit' && $vehicle->details && $vehicle->details->isNotEmpty())
                @foreach ($vehicle->details ?? [] as $index => $detail)
                <!-- {{ $detail->purchaseLines }} -->
                <tr class="product-row align-middle">
                    <td>
                        <input type="hidden" name="product_id[]" value="{{ $detail->id }}">
                        <input list="parts" type="text" name="product_name[]" class="form-control product_name-input"
                            value="{{ old('product_name.' . $index, $detail->parts_name) }}" required>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control quantity-input"
                            value="{{ old('quantity.' . $index, $detail->parts_quantity) }}" step="any" min="0" required>
                    </td>
                    <td>
                        <input type="number" name="price[]" class="form-control price-input"
                            value="{{ old('price.' . $index, optional($detail->purchaseLines->first())->unit_price) }}" step="any" min="0" required>
                    </td>
                    <td>
                        <input type="text" name="target_sell_price[]" class="form-control target_sell_price-input"
                            value="{{ old('target_sell_price.' . $index, optional($detail->purchaseLines->first())->target_sell_price) }}" step="any" min="0">
                    </td>
                    <td>
                        <input type="number" name="vehicle_price[]" class="form-control vehicle_price-input"
                            value="{{ old('vehicle_price.' . $index, $detail->total_parts_price ?? $detail->unit_price * $detail->parts_quantity) }}"
                            step="any" readonly required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="product-row align-middle">
                    <td>
                        <input type="hidden" name="product_id[]" value="">
                        <input list="parts" type="text" name="product_name[]" class="form-control product_name-input" required>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control quantity-input" step="any" min="0" required>
                    </td>
                    <td>
                        <input type="number" name="price[]" class="form-control price-input" step="any" min="0" required>
                    </td>
                    <td>
                        <input type="text" name="target_sell_price[]" class="form-control target_sell_price-input" step="any" min="0">
                    </td>
                    <td>
                        <input type="number" name="vehicle_price[]" class="form-control vehicle_price-input" step="any" readonly required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>


<div style="text-align: right">
    <button type="button" class="btn btn-primary btn-md add-row ">Add More</button>
</div>
    </div>


    <div class="col-md-3 form-group">
        <label for="paid_amount">Paid Amount</label>
        <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
            min="0"
            value="{{ old('paid_amount', $url === 'vehicle.edit' ? $vehicle?->purchaseTransaction?->paid_amount : '') }}">

        <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
    </div>

    <div class="col-md-3 form-group">
        <label for="dueAmount"> Due Amount</label>
        <input type="number" class="form-control total-parts-price text-danger" id="dueAmount" readonly
            value="{{ old('paid_amount', $url === 'vehicle.edit' ? ($vehicle->total_purchase_price - $vehicle?->purchaseTransaction?->paid_amount) : '') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>

        <select name="payment_method" id="payment_method" class="form-control select2">
            <option value="">Select a Payment Method</option>
            <option value="cash"
                {{ old('payment_method', $url == 'vehicle.edit' ? $vehicle?->purchaseTransaction?->payment_method : '') == 'cash' ? 'selected' : '' }}>
                Cash</option>
            <option value="bank_transfer"
                {{ old('payment_method', $url == 'vehicle.edit' ? $vehicle?->purchaseTransaction?->payment_method : '') == 'bank_transfer' ? 'selected' : '' }}>
                Bank Transfer</option>
            <option value="cheque"
                {{ old('payment_method', $url == 'vehicle.edit' ? $vehicle?->purchaseTransaction?->payment_method : '') == 'cheque' ? 'selected' : '' }}>
                Cheque</option>
            <option value="due"
                {{ old('payment_method', $url == 'vehicle.edit' ? $vehicle?->purchaseTransaction?->payment_method : '') == 'due' ? 'selected' : '' }}>
                Due</option>
        </select>


        <!-- Hidden bank list -->

    </div>

    <div class="col-md-6">
        <div class="form-group" id="bank_list_wrapper" style="{{ $url == 'vehicle.edit' && $vehicle?->purchaseTransaction?->payment_method == 'bank_transfer' ? 'display:block; margin-top:10px;' : 'display:none;' }}">
            <label for="bank_id">Select Bank</label>
            <div class="input-group">
                <select name="bank_id" id="bank_id" class="form-control">
                    <option value="">-- Select Bank --</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}" {{$url == 'vehicle.edit' && $vehicle?->purchaseTransaction?->bank_id == $bank->id ? 'selected' : '' }}>Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
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




<div class="row">
    <div class="col-md-12 form-group">
        <fieldset class="form-group">
            <label for="note">Note</label>
            <textarea class="form-control" id="basicTextarea" name="note" rows="3">{{ old('note', $url == 'vehicle.edit' ? $vehicle->note : '') }}</textarea>
        </fieldset>
    </div>
</div>
