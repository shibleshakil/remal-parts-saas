@extends('layouts.app')
@section('title', 'Customer Direct Payment')

@section('content')

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                 @can('customer.index')
                <a href="{{ route('customer.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-list"></i> Customer List
                </a>
                @endcan
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">Customer</a></li>
                    <li class="breadcrumb-item active">Customer Direct Payment</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customer Direct Payment</h3>
    </div>


    <!-- Form -->
    <div class="card">
        <div class="card-header">
            <h5>Customer: {{ $customer->name }}</h5>
            <h5>Total Due: {{ number_format($total_due, 2) }}</h5>
        </div>
        <div class="card-body">
            @include('partials.session_message')

            <form action="{{ route('customer.directPay') }}" method="POST" id="directPaymentForm">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="form-group mb-3">
                    <label for="payment_amount">Payment Amount</label>
                    <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="any" value="{{ old('payment_amount') }}" required>
                    @error('payment_amount')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="due" {{ old('payment_method') == 'due' ? 'selected' : '' }}>Due</option>
                    </select>
                    @error('payment_method')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="payment_date">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    @error('payment_date')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="note">Note (Optional)</label>
                    <textarea name="note" id="note" class="form-control">{{ old('note') }}</textarea>
                    @error('note')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="text-end">
                    <a href="{{ route('customer.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('directPaymentForm');
    const paymentAmountInput = document.getElementById('payment_amount');

    form.addEventListener('submit', function (e) {
        const paymentAmount = parseFloat(paymentAmountInput.value);

        // Validate payment amount
        if (!paymentAmount || paymentAmount <= 0) {
            paymentAmountInput.classList.add('is-invalid');
            e.preventDefault();
            alert('Please enter a valid payment amount greater than 0.');
        } else {
            paymentAmountInput.classList.remove('is-invalid');
        }
    });
});
</script>
@endsectionl
