@extends('layouts.app')
@section('title', 'Expense Details')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route ('expense.index')}}" class="btn btn-primary">
                    {{ __('Expense List') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>

        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item ">{{ __('Expense') }}</li>
                    <li class="breadcrumb-item active">{{ __('Expense Details') }}</li>
                </ol>
            </div>
        </div>
    </div>


    <div class="content-body">
        <section id="basic-form">
            <div class="row">
                <div class="col-12">
                    @include('partials.session_message')
                    <div class="card mt-2">
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form action="{{ route ('expense.store') }}" method="POST" class="form" enctype="multipart/form-data">@csrf
                                    <div class="form-body">
                                        @include('expense.form')
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


    });

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('amount');
        const errorMsg = document.getElementById('balanceError');
        let currentBalance = 0;

        // Fetch balance once
        fetch('{{ route("balances.getBalance") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    currentBalance = parseFloat(data.balance);
                }
            });

        // Listen for input changes
        input.addEventListener('input', function () {

            const entered = parseFloat(this.value) || 0;

            if (entered > currentBalance) {
                errorMsg.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                errorMsg.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection
