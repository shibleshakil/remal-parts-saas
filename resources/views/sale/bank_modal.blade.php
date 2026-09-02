<div class="modal fade text-left" id="inlineBankForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel333"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel333">Add New Bank</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBankForm" method="post">@csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bank_name" id="bank_name" step="any" min="0" required
                                value="{{ isset($bank) ? $bank->bank_name : old('bank_name') }}">

                        </div>
                        <div class="col-md-12 form-group">
                            <label for="account_name">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="account_name" id="account_name" step="any" min="0"
                                required value="{{ isset($bank) ? $bank->account_name : old('account_name') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="account_no">Account No <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="account_no" id="account_no" step="any" min="0"
                                required value="{{ isset($bank) ? $bank->account_no : old('account_no') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="branch">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="branch" id="branch" step="any" min="0" required value="{{ isset($bank) ? $bank->branch : old('branch') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-outline-secondary btn-lg"
                            data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-primary btn-lg addCustomerFormButton">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
