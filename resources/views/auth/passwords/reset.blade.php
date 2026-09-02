@extends('layouts.auth-app')

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
                    <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2"><span>{{ __('Reset Password') }}</span></h6>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form-horizontal form-simple" method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="hidden" name="token" value="{{ $token }}">
                            </fieldset>

                            <fieldset class="form-group position-relative has-icon-left">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}"
                                    placeholder="Enter your Mail Address" required autocomplete="email" autofocus>
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
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                <div class="form-control-position">
                                    <i class="fa fa-key"></i>
                                </div>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </fieldset>

                            <fieldset class="form-group position-relative has-icon-left">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <div class="form-control-position">
                                    <i class="fa fa-key"></i>
                                </div>
                            </fieldset>

                            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="feather icon-unlock"></i> {{ __('Reset Password') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
