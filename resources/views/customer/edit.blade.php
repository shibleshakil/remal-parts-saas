@extends('layouts.app')
@section('title', 'Edit Customer')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{route ('customer.index')}}" class="btn btn-primary">
                    {{ __('Customer list') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Setup') }}</li>
                    <li class="breadcrumb-item">{{ __('Customer') }}</li>
                    <li class="breadcrumb-item active">{{ __('Edit Customer') }}</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="content-body">
        <!-- basic form table -->
        <section id="basic-form">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Edit Customer</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form action="{{ route ('customer.update') }}" method="post" class="form" enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        <input type="hidden" name="id" value="{{ $data->id }}">
                                        @include('customer.form')
                                    </div>

                                    <div class="form-actions right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-check-square-o"></i> Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/ basic form table -->
    </div>

</div>

@endsection

@section('script')
<script type="text/javascript">

    $(document).ready(function () {

    });


</script>
@endsection
