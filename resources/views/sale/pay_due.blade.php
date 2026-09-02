@extends('layouts.app')
@section('title', 'Pay Due Amount')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-1">
            <h3 class="content-header-title">
                <a href="{{ route('sales.index') }}" class="btn btn-primary">
                    {{ __('Sale List') }} <i class="feather icon-eye"></i>
                </a>
            </h3>
        </div>
        <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Sale') }}</li>
                    <li class="breadcrumb-item active">{{ __('Sale Payment') }}</li>
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
                            <h4 class="card-title">Pay Due Amount for Sale #{{ $sale->id }}</h4>
                            <h5>Customer: {{ $sale->customer ? $sale->customer->name : 'Walking Customer' }}</h5>
                            <h5>Total Amount: {{ number_format($sale->final_amount, 2) }}</h5>
                            <h5>Paid Amount: {{ number_format($sale->paid_amount, 2) }}</h5>
                            <h5>Due Amount: {{ number_format($sale->final_amount - $sale->paid_amount, 2) }}</h5>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <form id="payDue" action="{{ route('sales.pay-due', $sale->id) }}" method="POST" class="form">
                                    @csrf
                                    <div class="form-body">
                                        <div class="form-group">
                                            <label for="payment_amount">Payment Amount</label>
                                            <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="any" min="0" max="{{ $sale->final_amount - $sale->paid_amount }}" required>
                                            @error('payment_amount')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

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

                                        <div class="form-group">
                                            <label for="payment_date">Payment Date</label>
                                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                            @error('payment_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-actions right">
                                        <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
                                        <a href="{{ route('sales.index') }}" class="btn btn-secondary mt-3">Cancel</a>
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
        // 1) Remove the related error as soon as user changes the field
        // $form.on('input change', 'input, textarea, select', function() {
        //     const name = $(this).attr('name');
        //     if (!name) return;
        //     // remove any error spans that were tagged for this input name
        //     $form.find(`.text-danger[data-field="${name}"]`).remove();
        // });

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
                                window.location.href = "{{ route('sales.index') }}";
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
</script>
@endsection
