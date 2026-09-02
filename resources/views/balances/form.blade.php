@php
    $url = Route::currentRouteName();
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label for="final_amount">Amount<span class="text-danger">*</span></label>
        @if ($url == 'balances-out.create' || $url == 'balances-out.edit')
            <input type="number" class="form-control" name="final_amount" id="final_amount_out" step="any" min="0" required
            value="{{ isset($transaction) ? $transaction->paid_amount : old('final_amount') }}">

            <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
        @else
            <input type="number" class="form-control" name="final_amount" id="final_amount" step="any" min="0" required
            value="{{ isset($transaction) ? $transaction->paid_amount : old('final_amount') }}">
        @endif
    </div>

    <!-- Promise Payment Date Field -->
    <div class="col-md-6 form-group">
        <label for="transaction_date">Date</label>
        <div class="input-group date datetime" id="date_wrapper">
            <input type="text" name="transaction_date" id="transaction_date"
                class="form-control date-input"
                value="{{ old('transaction_date', $transaction->transaction_date ?? today()) }}">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        @error('transaction_date')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" id="payment_method" class="form-control select2">
            <!-- <option value="">Select a Payment Method</option> -->
            <option value="cash"
                {{ old('payment_method', $url == 'balances.edit' || $url == 'balances-out.edit' ? $transaction->payment_method : '') == 'cash' ? 'selected' : '' }}>
                Cash</option>
            <option value="bank_transfer"
                {{ old('payment_method', $url == 'balances.edit' || $url == 'balances-out.edit' ? $transaction->payment_method : '') == 'bank_transfer' ? 'selected' : '' }}>
                Bank Transfer</option>
            <option value="cheque"
                {{ old('payment_method', $url == 'balances.edit' || $url == 'balances-out.edit' ? $transaction->payment_method : '') == 'cheque' ? 'selected' : '' }}>
                Cheque</option>
            <option value="due"
                {{ old('payment_method', $url == 'balances.edit' || $url == 'balances-out.edit' ? $transaction->payment_method : '') == 'due' ? 'selected' : '' }}>
                Due</option>
        </select>
    </div>

    <div class="col-md-6 form-group">
        <!-- Hidden bank list -->
       <div id="bank_list_wrapper"
            style="{{ old('bank_id') || (($url == 'balances.edit' || $url == 'balances-out.edit') && $transaction->bank_id) ? 'display:block;' : 'display:none;' }}">


            <label for="bank_id">Select Bank</label>
            <div class="input-group">
                <select name="bank_id" id="bank_id" class="form-control">
                    <option value="">-- Select Bank --</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}" {{ old('bank_id', $url == 'balances.edit' || $url == 'balances-out.edit' ? $transaction->bank_id : '') == $bank->id ? 'selected' : '' }}>Name: {{ $bank->bank_name }} - Account Name: {{ $bank->account_name }} - Account No: {{ $bank->account_no }}</option>
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

