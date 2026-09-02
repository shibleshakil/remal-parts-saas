

<div class="modal fade text-left" id="inlineSupplierForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3333"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel3333">Add New Supplier</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="supplierAddForm" action="{{ route('supplier.store') }}"  method="post" class="form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-body">
                        @include('supplier.form')
                    </div>

                    <div class="form-actions right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
