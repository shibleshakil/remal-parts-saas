@extends('layouts.app')
@section('title', 'Create Expense Type')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route ('expense-type.index')}}" class="btn btn-primary">
                    {{ __('Expense Type List') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Expense Type') }}</li>
                    <li class="breadcrumb-item active">{{ __('Create Expense Type') }}</li>
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
                            <h4 class="card-title">Add Expense Type</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form action="{{ route ('expense-type.store') }}" method="post" class="form" enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        @include('expenseType.form')
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
        <!--/ basic form table -->
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">




</script>
@endsection
