@extends('layouts.auth-app')

@section('content')

<section class="row flexbox-container">
    <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="col-lg-4 col-md-8 col-10 box-shadow-2 p-0">
            <div class="card border-grey border-lighten-3 m-0">
                <div class="card-header border-0">
                    <div class="card-title text-center">
                        <div class="p-1"><img src=" {{ asset('public/app-assets/images/logo/stack-logo-dark.png') }}" alt="branding logo"></div>
                    </div>
                    <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2"><span>{{ __('Verify Your Email Address') }}</span></h6>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                        @endif

                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        {{ __('If you did not receive the email') }},

                        <form class="form-horizontal form-simple" action="{{ route('verification.resend') }}" method="POST">@csrf
                            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="feather icon-unlock"></i> {{ __('click here to request another') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
