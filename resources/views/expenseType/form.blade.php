@php
    $url = \Route::currentRouteName();
@endphp
<div class="row">

    <div class="col-md-12 form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" id="name" required
        value="{{$url == 'expense-type.edit' ? $data->name : old('name')}}">
    </div>

</div>
