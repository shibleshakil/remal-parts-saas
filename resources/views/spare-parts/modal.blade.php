<div class="modal fade text-left" id="inlineSparePartForm" tabindex="-1" role="dialog" aria-labelledby="inlineSparePartFormLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="inlineSparePartFormLabel">Add New Spare Part</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="sparePartAddForm" action="{{ route('spare-parts.ajaxStore') }}"  method="post" class="form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="parts_name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parts_name" id="parts_name" required value="">
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="car_name">Car Name <span class="text-danger">*</span></label>
                                <input list="carNames" type="text" class="form-control" name="car_name" id="car_name" required value="">

                                <datalist id="carNames">
                                    @foreach ($carNames as $c_name)
                                    <option value="{{$c_name}}">
                                        @endforeach
                                </datalist>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="car_model">Car Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="car_model" id="car_model" required value="">
                            </div>
                        </div>
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
