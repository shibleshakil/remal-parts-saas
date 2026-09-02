@php
$url = Route::currentRouteName();
@endphp

<div class="row">
    <!-- Vehicle Selector -->
    <div class="col-md-12 form-group">
        <div>
            {{-- <label for="search">Search </label> --}}
            <fieldset class="form-group position-relative mb-0">
                <input type="search" class="form-control form-control-xl input-xl" id="iconLeft1"
                    placeholder="Scan or search by barcode">
                <div class="form-control-position">
                    <i class="feather icon-search font-medium-4"></i>
                </div>
            </fieldset>
        </div>
        <div id="parts-by-barcode-div" class=" py-2">
            <select name="parts-by-barcode" id="parts-by-barcode" class="form-control select2"></select>
        </div>
    </div>
    <!-- Vehicle Selector -->
    <div class="col-md-6 form-group">
        <label for="vehicle">Vehicle <span class="text-danger">*</span></label>
        <select name="vehicle" id="vehicle" class="form-control select2">
            <option value="">Select a Vehicle</option>
            @foreach ($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}"
                {{ old('vehicle') == $vehicle->id ||
                    ($url === 'quotes.edit' && isset($quote) && $quote->vehicle_id == $vehicle->id)
                        ? 'selected'
                        : '' }}>
                {{ $vehicle->name }} - {{ $vehicle->uid }}
            </option>
            @endforeach
        </select>
        @error('vehicle')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Spare Parts Selector -->
    <div class="col-md-6 form-group">
        <label for="parts">Spare Parts <span class="text-danger">*</span></label>
        <select name="parts" id="parts" class="form-control select2">
            <option value="">Select a Spare Part</option>
            @foreach ($parts as $part)
            <option value="{{ $part->id }}" data-unit-price="{{ $part->unit_price }}"
                data-available-qty="{{ $part->available_quantity }}"
                {{ old('parts') == $part->id ? 'selected' : '' }}>
                {{ $part->parts_name }} - {{ $part->uid }}| {{ $part->car_name }} {{ $part->car_model }} |
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

    <!-- Car Spare Parts -->
    <div class="col-md-6 form-group">
        <label for="car_spare_parts">Car Spare Parts<span class="text-danger">*</span></label>
        <select name="car_spare_parts" id="car_spare_parts" class="form-control select2">
            <option value="">Select Car Spare Part</option>
        </select>
        @error('car_spare_parts')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Date Picker -->
    <div class="col-md-6 form-group">
        <label for="date">Date <span class="text-danger">*</span></label>
        <div class="input-group date datetime" id="date">
            <input type="text" name="date" id="date_input" class="form-control date-input"
                value="{{ old('date', $url === 'quotes.edit' && isset($quote) ? $quote->date : now()->format('Y-m-d')) }}" />
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <!-- Customer Selector -->
    <div class="col-md-6 form-group">
        <label for="customer">Customer <span class="text-danger">*</span></label>
        <div class="input-group">
            <select name="customer" id="customer" class="form-control select2">
                <option value="">Walking Customer</option>
                @foreach ($customers as $customer)
                <option value="{{ $customer->id }}"
                    {{ old('customer') == $customer->id ||
                        ($url === 'quotes.edit' && isset($quote) && $quote->customer_id == $customer->id)
                            ? 'selected'
                            : '' }}>
                    {{ $customer->name }}
                </option>
                @endforeach
            </select>
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" title="Add New Customer" data-toggle="modal"
                    data-target="#inlineForm">
                    <i class="feather icon-plus"></i>
                </button>
            </div>
        </div>
        @error('customer')
        <small class="text-danger">{{ $message }}</small>
        @enderror
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
                <th style="width: 15%;">Total</th>
                <th style="width: 10%;">Action</th>
            </tr>
        </thead>
        <tbody id="product-rows">

        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td class="text-right" colspan="3">Total Amount</td>
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
                        id="final_amount_text">{{ old('final_amount', $url == 'quotes.edit' ? $quote->final_amount : 0) }}</span>
                    <input type="hidden" name="final_amount" id="final_amount" class="form-control phone"
                        value="{{ old('final_amount', $url == 'quotes.edit' ? $quote->final_amount : 0) }}" readonly>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="row">
    <!-- Expected Sale Date Field -->
    <div class="col-md-6 form-group">
        <label for="expected_purchase_date">Expected Sale Date</label>
        <div class="input-group date datetime" id="expected_purchase_date_wrapper">
            <input type="text" name="expected_purchase_date" id="expected_purchase_date"
                class="form-control date-input"
                value="{{ old('expected_purchase_date', $quote->expected_purchase_date ?? '') }}">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('expected_purchase_date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>
