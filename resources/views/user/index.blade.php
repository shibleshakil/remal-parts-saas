@extends('layouts.app')
@section('title', 'User')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @if(\App\Helpers\CommonHelper::userHasAccess())
                        <a href="{{ route('user.create') }}" class="btn btn-primary">
                            {{ __('Add User') }} <i class="feather icon-plus"></i>
                        </a>
                    @endif
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('User') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="content-body">
            <section id="configuration">
                <div class="row">
                    <div class="col-12">
                        @include('partials.session_message')
                        <div class="card rounded">
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <h4 class="card-title">User List</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sl</th>
                                                    <th>User Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($users as $user)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ $user->role }}</td>

                                                        <td>
                                                            @if ( \App\Helpers\CommonHelper::userHasAccess() )
                                                            <a href="{{ route('user.edit', $user->id) }}"
                                                                class="btn btn-md btn-primary" title="Edit"><i
                                                                    class="feather icon-edit"></i></a>

                                                            <button type="button" class="btn btn-md btn-danger"
                                                                title="Delete"
                                                                onclick="deleteData('{{ route('user.delete', $user->id) }}')">
                                                                <i class="feather icon-trash"></i>
                                                            </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No data found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
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
    <script type="text/javascript">
        $(document).ready(function() {
            // DataTable initialization (assuming it's set up in layouts.app or elsewhere)
        });
    </script>
@endsection
