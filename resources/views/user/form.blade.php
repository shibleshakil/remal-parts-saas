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
        <label for="role_id">Role <span class="text-danger">*</span></label>
        <select class="form-control" name="role_id" id="role_id" required>
            <option value="">Select Role</option>
            @foreach($roles as $id => $role)
                <option value="{{ $id }}" {{ (isset($user) && $user->roles->pluck('id')->contains($id)) ? 'selected' : '' }}>
                    {{ $role }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Branch Dropdown -->
    <div class="col-md-6 form-group">
        <label for="branch_id">Branch <span class="text-danger">*</span></label>
        <select class="form-control" name="branch_id" id="branch_id" required>
            <option value="">Select Branch</option>
            @foreach($branches as $id => $branch)
                <option value="{{ $id }}" {{ (isset($user) && $user->branch->pluck('id')->contains($id)) ? 'selected' : '' }}>
                    {{ $branch }}
                </option>
            @endforeach
        </select>
    </div>
</div>

