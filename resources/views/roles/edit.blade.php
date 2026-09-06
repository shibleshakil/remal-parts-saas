@extends('layouts.app')
@section('title', 'Roles')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <!-- <a href="{{ route('roles.index') }}" class="btn btn-primary">
                        {{ __('Bank List') }} <i class="feather icon-eye"></i>
                    </a> -->
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item ">{{ __('Roles') }}</li>
                        <li class="breadcrumb-item ">{{ __('Roles Edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>


        <div class="content-body">
            <!-- basic form table -->
            <section id="basic-form">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Edit Role</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <form action="{{ route('roles.update', $role->id) }}" method="post" class="form"
                                        enctype="multipart/form-data">@csrf
                                        <div class="form-body">
                                            @include('roles.form')
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

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Role List</h4>
                            </div>
                            <div class="card-body card-dashboard">
                                <table class="table table-striped table-bordered zero-configuration">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roles as $key => $role)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $role->name }}</td>
                                                <td>
                                                    @can('roles.edit')
                                                    @if($role->name != 'Super Admin')

                                                    <a href="{{ route('roles.edit', $role->id) }}"
                                                        class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="feather icon-edit"></i>
                                                    </a>
                                                    @endif
                                                    @endcan

                                                    @can('roles.setPermissionsForm')
                                                    <a href="{{ route('roles.setPermissionsForm', $role->id) }}"
                                                        class="btn btn-sm btn-info" title="View Permissions">
                                                        <i class="feather icon-list"></i>
                                                    </a>
                                                    @endcan


                                                    @can('roles.delete')
                                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                                        onclick="deleteData('{{ route('roles.delete', [$role->id]) }}')" title="Delete">
                                                        <i class="feather icon-trash"></i>
                                                    </button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

@endsection
