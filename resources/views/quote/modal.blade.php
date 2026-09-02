<div class="modal fade text-left" id="inlineForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel33">Add New Customer</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCustomerForm" method="post">@csrf
                <div class="modal-body">
                    <label>Name</label>
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Customer Name" class="form-control" required>
                    </div>

                    <label>Phone Number</label>
                    <div class="form-group">
                        <input type="text" name="phone" placeholder="Phone Number" class="form-control">
                    </div>

                    <label>Address</label>
                    <div class="form-group">
                        <input type="text" name="address" placeholder="Address" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="reset" class="btn btn-outline-secondary btn-lg"
                        data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-primary btn-lg addCustomerFormButton">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
