@php
    $url = Route::currentRouteName();
@endphp
<div class="row">
    <!-- Name Field -->
    <div class="col-md-6 form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control"
               name="name"
               id="name"
               required
               value="{{ isset($branch) ? $branch->name : old('name') }}">
    </div>

    <!-- Email Field -->
    <div class="col-md-6 form-group">
        <label for="email">Email</label>
        <input type="email"
               class="form-control"
               name="email"
               id="email"
               value="{{ isset($branch) ? $branch->email : old('email') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="phone">Phone</label>
        <input type="text"
               class="form-control"
               name="phone"
               id="phone"
               value="{{ isset($branch) ? $branch->phone : old('phone') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="address">Address</label>
        <input type="text"
               class="form-control"
               name="address"
               id="address"
               value="{{ isset($branch) ? $branch->address : old('address') }}">
    </div>

</div>

