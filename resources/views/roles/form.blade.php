<div class="row">
    <div class="col-md-12 form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" id="name" required
        value="{{ isset($role) ? $role->name : old('name') }}">
    </div>
</div>
