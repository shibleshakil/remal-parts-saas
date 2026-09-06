@extends('layouts.app')
@section('title', 'Roles | Set Permissions')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                @can('roles.index')
                <a href="{{ route('roles.index') }}" class="btn btn-primary">
                    <i class="feather icon-arrow-left"></i> {{ __('Roles') }} <i class="feather icon-list"></i>
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Roles') }}</li>
                    <li class="breadcrumb-item ">{{ __('Set Permissions') }}</li>
                </ol>
            </div>
        </div>
    </div>


    <div class="content-body">
        <!-- basic form table -->
        <section id="basic-form">
            <div class="row">
                <div class="col-md-12">
                    @include('partials.session_message')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Set Permissions With <span class="text-primary">{{ $role->name??'Role' }}</span></h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form action="" method="post" class="form"
                                    enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        <div class="card">
                                            <div class="">
                                                <h4 class="mb-1 text-center text-primary"><i class="feather icon-lock"></i> Permissions</h4>

                                                <div class="table">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>
                                                                        <input class="" type="checkbox" id="selsectAll">
                                                                    </th>
                                                                    <th>Module</th>
                                                                    <th>Permissions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $groupedPermissions = $permissions->groupBy(function ($permission) {
                                                                        return explode('.', $permission->name)[0];
                                                                    });
                                                                @endphp

                                                                @foreach ($groupedPermissions as $module => $permissions)
                                                                    <tr>
                                                                        <td>
                                                                            <input class="select-row" type="checkbox" data-module="{{ $module }}">
                                                                        </td>
                                                                        <td>{{ \Illuminate\Support\Facades\Lang::has('permission.' . $module) ? __('permission.' . $module) : ucfirst($module) }}</td>
                                                                    <td>
                                                                        @foreach ($permissions as $permission)
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                                                    value="{{ $permission->name }}"
                                                                                    id="permission{{ $permission->id }}"
                                                                                    data-module="{{ $module }}"
                                                                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                                <label class="form-check-label" for="permission{{ $permission->id }}">
                                                                                    {{ __('permission.' . $permission->name) }}
                                                                                </label>
                                                                            </div>
                                                                            @endforeach
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
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
            </div>
        </section>

    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function () {

        // Sync all rows based on pre-checked permissions
        $('.select-row').each(function () {
            let module = $(this).data('module');

            let total = $('input[name="permissions[]"][data-module="' + module + '"]').length;
            let checked = $('input[name="permissions[]"][data-module="' + module + '"]:checked').length;

            $(this).prop('checked', total === checked);
        });

        // Sync global select all
        checkSelectAll();
    });

    $('#selsectAll').on('change', function () {
        let checked = $(this).is(':checked');

        $('input[name="permissions[]"], .select-row').prop('checked', checked);
    });


    // Row-wise select
    $('.select-row').on('change', function () {
        let module = $(this).data('module');
        let checked = $(this).is(':checked');

        $('input[name="permissions[]"][data-module="' + module + '"]').prop('checked', checked);

        checkSelectAll();
    });


    // Individual permission change
    $('input[name="permissions[]"]').on('change', function () {
        let module = $(this).data('module');

        let total = $('input[name="permissions[]"][data-module="' + module + '"]').length;
        let checked = $('input[name="permissions[]"][data-module="' + module + '"]:checked').length;

        // If all checked → check row
        $('.select-row[data-module="' + module + '"]').prop('checked', total === checked);

        checkSelectAll();
    });


    // Function to check/uncheck global select all
    function checkSelectAll() {
        let totalPermissions = $('input[name="permissions[]"]').length;
        let checkedPermissions = $('input[name="permissions[]"]:checked').length;

        $('#selsectAll').prop('checked', totalPermissions === checkedPermissions);
    }
</script>
@endsection
