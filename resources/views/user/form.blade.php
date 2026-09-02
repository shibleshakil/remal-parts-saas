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
               value="{{ isset($user) ? $user->name : old('name') }}">
    </div>

    <!-- Email Field -->
    <div class="col-md-6 form-group">
        <label for="email">Email <span class="text-danger">*</span></label>
        <input type="email"
               class="form-control"
               name="email"
               id="email"
               required
               value="{{ isset($user) ? $user->email : old('email') }}">
    </div>


    <!-- Password Field -->
    <div class="col-md-6 form-group">
        <label for="password">{{ isset($user) ? 'New Password' : 'Password' }} <span class="text-danger">{{ isset($user) ? '' : '*' }}</span></label>
        <input type="password"
               class="form-control"
               name="password"
               id="password"
               {{ isset($user) ? '' : 'required' }}
               placeholder="{{ isset($user) ? 'Leave blank to keep current password' : '' }}">
    </div>

    <!-- Role Dropdown -->
    <div class="col-md-6 form-group">
        <label for="role">Role <span class="text-danger">*</span></label>
        <select class="form-control" name="role" id="role" required>
            <option value="">Select Role</option>
            <option value="admin" {{ (isset($user) && $user->role == 'admin') ? 'selected' : '' }}>Admin</option>
            <option value="employee" {{ (isset($user) && $user->role == 'employee') ? 'selected' : '' }}>Employee</option>
        </select>
    </div>
</div>

