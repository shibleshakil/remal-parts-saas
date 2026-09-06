@php
    $url = \Route::currentRouteName();
@endphp
<div class="row">

    <div class="col-md-6 form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" id="name" required
        value="{{$url == 'customer.edit' ? $data->name : old('name')}}">
    </div>

    <div class="col-md-6 form-group">
        <label for="phone">Phone Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="phone" id="phone" required
        value="{{$url == 'customer.edit' ? $data->phone : old('phone')}}">
    </div>

    <div class="col-md-6 form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" name="email" id="email"
        value="{{$url == 'customer.edit' ? $data->email : old('email')}}">
    </div>

    <div class="col-md-6 form-group">
        <label for="address">Address <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="address" id="address"
        required
        value="{{$url == 'customer.edit' ? $data->address : old('address')}}">
    </div>
</div>
