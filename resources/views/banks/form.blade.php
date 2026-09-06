@php
    $url = Route::currentRouteName();
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="bank_name" id="bank_name" step="any" min="0" required
        value="{{ isset($bank) ? $bank->bank_name : old('bank_name') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="account_name">Account Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="account_name" id="account_name" step="any" min="0"
            required value="{{ isset($bank) ? $bank->account_name : old('account_name') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="account_no">Account No <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="account_no" id="account_no" step="any" min="0"
            required value="{{ isset($bank) ? $bank->account_no : old('account_no') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="branch">Branch <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="branch" id="branch" step="any" min="0" required value="{{ isset($bank) ? $bank->branch : old('branch') }}">
    </div>
</div>
