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
            <select name="parts-by-barcode" id="parts-by-barcode" class="form-control select2">

            </select>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Vehicle Selector -->
        <div class="form-group">
            <label for="vehicle">Vehicle <span class="text-danger">*</span></label>
            <select name="vehicle" id="vehicle" class="form-control select2">
                <option value="">Select a Vehicle</option>
                @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ old('vehicle') == $vehicle->id ||
                        ($url === 'sales.edit' && isset($sale) && $sale->vehicle_id == $vehicle->id)
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
</div>
<div class="row">
    <!-- Date Picker -->
    <div class="col-md-6 form-group">
        <label for="date">Date <span class="text-danger">*</span></label>
        <div class="input-group date datetime" id="date">
            <input type="text" name="date" id="date_input" class="form-control date-input"
                value="{{ old('date', $url === 'sales.edit' && isset($sale) ? $sale->date : now()->format('Y-m-d')) }}" />
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
                        ($url === 'sales.edit' && isset($sale) && $sale->customer_id == $customer->id)
                            ? 'selected'
                            : '' }}>
                    {{ $customer->name }}
                </option>
                @endforeach
            </select>
            @can('customer.create')
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" title="Add New Customer" data-toggle="modal"
                    data-target="#inlineForm">
                    <i class="feather icon-plus"></i>
                </button>
            </div>
            @endcan
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
                <td class="text-right" colspan="4">Sub Total</td>
                <td class="text-right">
                    <span id="total_cost_text">0.00</span>
                    <input type="hidden" name="total_cost" id="total_cost" value="0">
                </td>
                <td></td>
            </tr>

            <tr class="font-weight-bold">
                <td class="" colspan="4">
                    <div class="float-right">
                        <div class="input-group">
                            <div class="input-group-append">

                                <select name="vat_type" id="vat_type" class="form-control select" required>
                                    <option value="exclusive" {{ $url == 'sales.edit' && isset($sale) && $sale->vat_type == 'exclusive' ? 'selected' : '' }}>
                                        Vat Amount({{ $url == 'sales.edit' && isset($sale) ? $sale->vat * 100 : config('app.vat')  * 100 }}% Exclusive)
                                    </option>
                                    <option value="inclusive" {{ $url == 'sales.edit' && isset($sale) && $sale->vat_type == 'inclusive' ? 'selected' : '' }}>
                                        Vat Amount({{ $url == 'sales.edit' && isset($sale) ? $sale->vat * 100 : config('app.vat')  * 100 }}% Inclusive)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-right">
                    <span id="vat_amount_text">0.00</span>
                    <input type="hidden" name="vat_amount" id="vat_amount" value="0">
                </td>
                <td>

                </td>
            </tr>

            <tr class="font-weight-bold">
                <td class="text-right" colspan="4">Total Amount</td>
                <td class="text-right">
                    <span id="final_amount_text">0.00</span>
                    <input type="hidden" name="final_amount" id="final_amount" class="form-control phone"
                        value="{{ old('final_amount', $url == 'sales.edit' ? $sale->final_amount : 0) }}" readonly>
                </td>
                <td></td>
            </tr>
            <!-- <tr>
                <td class="text-right" colspan="2">Discount</td>
                <td colspan="3">
                    <div class="input-group">
                        <input type="number" class="form-control text-right phone" name="discount_amount"
                            id="discount_amount"
                            value="{{ old('discount_amount', $url == 'sales.edit' ? $sale->discount_amount : 0) }}"
                            oninput="calculateFinalAmount()">
                        <div class="input-group-append">
                            <select name="discount_type" id="discount_type" class="form-control select"
                                onchange="calculateFinalAmount()" required>
                                <option value="flat"
                                    {{ old('discount_type', $url == 'sales.edit' && $sale->discount_type == 'flat' ? 'flat' : '') == 'flat' ? 'selected' : '' }}>
                                    Flat</option>
                                <option value="percentage"
                                    {{ old('discount_type', $url == 'sales.edit' && $sale->discount_type == 'percentage' ? 'percentage' : '') == 'percentage' ? 'selected' : '' }}>
                                    Percentage</option>
                            </select>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr> -->
        </tfoot>
    </table>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="paid_amount">Paid Amount <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="any"
            min="0" required value="{{ $url === 'sales.edit' ? $sale->transactions->first()->paid_amount : 0 }}">

        @if($url === 'sales.edit' && isset($sale) && $sale->transactions != null)
        <table class="table table-bordered" id="transactions_table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->transactions as $transaction)
                <tr>
                    <td class="text-nowrap">
                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                    </td>
                    <td>
                        {{ $typeLabels[$transaction->type] ?? ucwords(str_replace('_', ' ', $transaction->type)) }}
                    </td>
                    <td class="text-right {{ $transaction->type === 'refund' ? 'refund_amount' : 'paid_amount' }}">
                        {{ number_format($transaction->paid_amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="2" class="text-right">Total</td>
                    <td class="text-right">
                        <b id="total_paid_amount">{{ number_format($sale->transactions->where('type', '!=', 'refund')->sum('paid_amount') - $sale->transactions->where('type', 'refund')->sum('paid_amount'), 2) }}</b>
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>


    <div class="col-md-3 form-group">
        <label> Due Amount</label>
        <input type="number" class="form-control text-info" id="dueAmount" name="dueAmount" value="{{$url === 'sales.edit' ? $sale->final_amount - $sale->paid_amount : 0 }}" readonly>
        <input type="hidden" class="form-control text-info" id="hdueAmount" name="hdueAmount" readonly>
    </div>

    <div class="col-md-6 form-group">
        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>

        <select name="payment_method" id="payment_method" class="form-control">
            <!-- <option value="">Select a Payment Method</option> -->
            <option value="cash"
                {{ old('payment_method', $url == 'sales.edit' ? $sale->payment_method : '') == 'cash' ? 'selected' : '' }}>
                Cash</option>
            <option value="bank_transfer"
                {{ old('payment_method', $url == 'sales.edit' ? $sale->payment_method : '') == 'bank_transfer' ? 'selected' : '' }}>
                Bank Transfer</option>
            <option value="cheque"
                {{ old('payment_method', $url == 'sales.edit' ? $sale->payment_method : '') == 'cheque' ? 'selected' : '' }}>
                Cheque</option>
            <option value="due"
                {{ old('payment_method', $url == 'sales.edit' ? $sale->payment_method : '') == 'due' ? 'selected' : '' }}>
                Due</option>
        </select>


        <!-- Hidden bank list -->
        <div id="bank_list_wrapper" style="{{ $url == 'sales.edit' && $sale->payment_method == 'bank_transfer' ? 'display:block; margin-top:10px;' : 'display:none; margin-top:10px;' }}">
            <label for="bank_id">Select Bank</label>
            <div class="input-group">
                <select name="bank_id" id="bank_id" class="form-control">
                    <option value="">-- Select Bank --</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}" {{$url == 'sales.edit' && $sale->bank_id == $bank->id ? 'selected' : '' }}>Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
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
    <!-- Warranty Field -->
    <div class="col-md-6 form-group">
        <label for="warranty">Warranty</label>
        <fieldset>
            <div class="input-group">
                <input type="text" class="form-control" name="warranty" id="warranty"
                    value="{{ old('warranty', $url === 'sales.edit' ? $sale->warranty : '') }}"
                    aria-describedby="warranty-addon">
                <div class="input-group-append">
                    <span class="input-group-text" id="warranty-addon">Days of Warranty</span>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Promise Payment Date Field -->
    <div class="col-md-6 form-group">
        <label for="promise_payment_date">Promise Payment Date</label>
        <div class="input-group date datetime" id="promise_payment_date_wrapper">
            <input type="text" name="promise_payment_date" id="promise_payment_date"
                class="form-control date-input"
                value="{{ old('promise_payment_date', $sale->promise_payment_date ?? '') }}">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('promise_payment_date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Email Send --}}
    <div class="col-md-12 form-group">
        <label for="send_email">Send Email?</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="send_email" name="send_email" disabled>
            <label class="form-check-label" for="send_email">
                Yes, Send Email to the Customer.
            </label>
        </div>
        @error('send_email')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

</div>
