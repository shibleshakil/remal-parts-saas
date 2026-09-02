@extends('layouts.app')

@section('title', 'Change Password')

@section('css')
    <style>
        .flexbox-container {
            display: flex;
            align-items: center;
            height: calc(var(--vh, 4vh) * 78);
        }
    </style>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body blank-page blank-page">
            <section class="flexbox-container">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <div class="col-lg-6 col-md-8 col-10 box-shadow-2 p-0">
                        <div class="card border-grey border-lighten-3 m-0">
                            <div class="card-content">
                                <div class="card-header pb-1 border-0">
                                    <h3 class="card-title text-center">
                                        <span>Change password to keep your account secure</span></h3>
                                </div>
                                <div class="card-body">
                                    @if (session('success'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <form class="form-horizontal form-simple form" action="{{ route ('password.update')}}" method="post">@csrf
                                        <div class="form-body">
                                            <div class="form-group">
                                                <label for="password">New Password
                                                    <span class="text-danger">* <small>(password must be at least 8 characters)</small>
                                                    </span>
                                                </label>
                                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="password">Confirm Password <span class="text-danger">*</span></label>
                                                <input type="password" name="password_confirmation" id="password-confirm" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="form-footer">
                                            <button type="submit" class="btn btn-primary btn-block submit-btn">
                                                <i class="feather icon-unlock"></i> Change Password
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
@endsection
