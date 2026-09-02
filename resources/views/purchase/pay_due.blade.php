@extends('layouts.app')
@section('title', 'Pay Due Amount')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    @can('purchase.index')
                        <a href="{{ route('purchase.index') }}" class="btn btn-primary">
                            {{ __('Purchase List') }} <i class="feather icon-eye"></i>
                        </a>
                    @endcan
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item">{{ __('Purchase') }}</li>
                        <li class="breadcrumb-item active">{{ __('Purchase Due Payment') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="content-body">
            <section id="basic-form">
                <div class="row">
                    <div class="col-12">
                        @include('partials.session_message')
                        <div class="card">
                            <div class="card-header">
                                @php
                                    $finalAmount = $purchase?->transactions?->sum('final_amount') ?? 0;
                                    $paidAmount = $purchase?->transactions?->sum('paid_amount') ?? 0;
                                    $dueAmount = $finalAmount - $paidAmount;
                                @endphp

                                <h4 class="card-title">Pay Due Amount for Purchase #{{ $purchase->invoice_no ?? $purchase->id }}</h4>
                                <h5>Supplier: {{ $purchase->supplier ? $purchase->supplier->name : 'Walking Supplier' }}</h5>
                                <h5>Total Amount: {{ number_format($finalAmount, 2) }}</h5>
                                <h5>Paid Amount: {{ number_format($paidAmount, 2) }}</h5>
                                <h5>Due Amount: {{ number_format($dueAmount, 2) }}</h5>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <form id="payDue" action="{{ route('purchase.pay-due', $purchase->id) }}" method="POST" class="form">
                                        @csrf
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="paid_amount">Payment Amount</label>
                                                        <input type="number" name="payment_amount" id="paid_amount" class="form-control" step="any" min="1" max="{{ $dueAmount }}" required>
                                                        <small id="balanceError" class="text-danger pt-1" style="display:none;">Entered amount exceeds current balance!</small>
                                                        @error('payment_amount')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="payment_method">Payment Method</label>
                                                        <select name="payment_method" id="payment_method" class="form-control" required>
                                                            <option value="cash">Cash</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                            <option value="cheque">Cheque</option>
                                                            <option value="due">Due</option>
                                                        </select>
                                                        @error('payment_method')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="payment_date">Payment Date</label>
                                                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                                        @error('payment_date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-actions right">
                                            <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
                                            <a href="{{ route('purchase.index') }}" class="btn btn-secondary mt-3">Cancel</a>
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
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body" id="invoiceContent">
                    <!-- Invoice will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            const $form = $('#payDue');
            let submitBtn = $form.find('button[type="submit"]');

            // 2) Submit handler (AJAX)
            $form.on('submit', function(e) {
                e.preventDefault();
                const formEl = this;
                let formData = new FormData(formEl);

                // remove previous errors inside this form
                $form.find('.text-danger').remove();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    complete: function() {
                        submitBtn.removeAttr('disabled');
                        submitBtn.find('.spinner-border').remove();
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Inject invoice into modal
                            $('#invoiceContent').html(response.view);

                            // Show the modal
                            $('#invoiceModal').modal('show');

                            // After modal is shown, trigger print
                            $('#invoiceModal').on('shown.bs.modal', function() {
                                setTimeout(function() {
                                    // Print only the modal content
                                    const printContents = document.getElementById('invoiceContent').innerHTML;
                                    const originalContents = document.body.innerHTML;

                                    // Replace page content with invoice temporarily
                                    document.body.innerHTML = printContents;
                                    window.print();

                                    // Restore original page content
                                    document.body.innerHTML = originalContents;
                                    // Close modal and redirect after print
                                    $('#invoiceModal').modal('hide');
                                    window.location.href = "{{ route('purchase.index') }}";
                                }, 500);
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.removeAttr('disabled');
                        submitBtn.find('.spinner-border').remove();

                        Swal.fire({
                            title: "Error",
                            text: "Something went wrong.",
                            icon: "error"
                        });
                    }
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('paid_amount');
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
