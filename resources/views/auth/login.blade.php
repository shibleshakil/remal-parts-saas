@extends('layouts.auth-app')
@section('title', 'Login')

@section('content')
<section class="row flexbox-container">
    <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="col-lg-4 col-md-8 col-10 box-shadow-2 p-0">
            <div class="card border-grey border-lighten-3 m-0">
                <div class="card-header border-0">
                    <div class="card-title text-center">
                        <div class="p-1">
                            <img src="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}" alt="logo"
                                style="height:auto; width:110px;">
                        </div>
                    </div>
                    <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                        <span>{{ __('Login') }}</span>
                    </h6>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form-horizontal form-simple" action="{{ route('login') }}"
                            method="POST">@csrf
                            <fieldset class="form-group position-relative has-icon-left">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                    placeholder="Enter your Email Address">
                                <div class="form-control-position">
                                    <i class="feather icon-user"></i>
                                </div>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </fieldset>

                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="password" class="form-control" id="user-password"
                                    name="password" placeholder="Password" required>
                                <div class="form-control-position">
                                    <i class="fa fa-key"></i>
                                </div>
                                <div class="form-control-position-right">
                                    <i class="fa fa-eye-slash" id="eye"></i>
                                </div>
                            </fieldset>
                            <div class="form-group row">
                                <div class="col-sm-6 col-12 text-center text-sm-left">
                                    <fieldset>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label" for="remember">
                                                {{ __('Remember Me') }}
                                            </label>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-sm-6 col-12 text-center text-sm-right">
                                    <a href="{{ route('password.request') }}" class="card-link"> {{ __('Forgot Your Password?') }}</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="feather icon-unlock"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
