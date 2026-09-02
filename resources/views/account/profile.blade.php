@extends('layouts.app')

@section('title', $data->name)

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
                                    <form class="form" action="{{ route('profile', [$data->name]) }}" method="post" enctype="multipart/form-data">@csrf
                                        <div class="media">
                                            <a href="javascript: void(0);">
                                                <img src="{{auth()->user()->image ?
                                                    asset('public/storage/' . auth()->user()->image)
                                                    : asset ('public/app-assets/images/avatar.jpg') }}"
                                                    class="rounded mr-75" alt="profile image" height="64" width="64">
                                            </a>
                                            <div class="media-body mt-75">
                                                <div class="col-12 px-0 d-flex flex-sm-row flex-column justify-content-start">
                                                    <label class="btn btn-sm btn-primary ml-50 mb-50 mb-sm-0 cursor-pointer"
                                                        for="account-upload">Upload new photo</label>
                                                    <input type="file" id="account-upload" hidden name="image">
                                                    <button class="btn btn-sm btn-secondary ml-50">Reset</button>
                                                </div>
                                                <p class="text-muted ml-75 mt-50">
                                                    <small>Allowed JPG, GIF or PNG. Max size of 800kB</small>
                                                    @error('image')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label for="name">{{ __('Name') }}</label>
                                                        <input type="text" class="form-control  @error('name') is-invalid @enderror"
                                                        id="name" name="name" placeholder="{{ __('Name') }}" value="{{ $data->name }}">
                                                        @error('name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label for="email">{{ __('Email') }}</label>
                                                        <input type="email" class="form-control  @error('email') is-invalid @enderror"
                                                        id="email" name="email" placeholder="{{ __('Email') }}" value="{{ $data->email }}">
                                                        @error('email')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex flex-sm-row flex-column justify-content-end">
                                                <button type="submit" class="btn btn-primary mr-sm-1 mb-1 mb-sm-0 submit-btn">Save
                                                    changes</button>
                                                <button type="button" onclick="(location.reload())"
                                                    class="btn btn-light">Cancel</button>
                                            </div>
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
