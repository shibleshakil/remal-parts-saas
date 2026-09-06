@extends('layouts.app')
@section('title', 'Customer Due Payment')

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
                    <li class="breadcrumb-item active">Customer Due Payment</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customer Due Payment</h3>
    </div>

    <!-- Form -->
    <div class="card">
        <div class="card-header">
            <h5>Customer: {{ $sale->customer ? $sale->customer->name : 'Walking Customer' }}</h5>
        </div>
        <div class="card-body">
            @include('partials.session_message')

            <form action="{{ route('customer.pay', $sale->id) }}" method="POST" id="paymentForm">
                @csrf
                <div class="form-group mb-3">
                    <label for="select_invoice">Select Invoice(s)</label>
                    <select name="select_invoice[]" id="select_invoice" class="form-control select2" multiple >
                        @php
                            $oldSelectedInvoices = old('select_invoice', []);
                        @endphp
                        @foreach($unpaidInvoices as $invoice)
                            @php
                                $due = $invoice->final_amount - $invoice->paid_amount;
                                $selected = in_array($invoice->id, $oldSelectedInvoices) ? 'selected' : '';
                            @endphp
                            <option value="{{ $invoice->id }}" data-id="{{ $invoice->id }}" data-due="{{ $due }}" {{ $selected }}>
                                Invoice #{{ $invoice->id }} (Due: {{ number_format($due, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('select_invoice')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Selected Invoices</label>
                    <table class="table table-bordered" id="invoice_table">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Due Amount</th>
                                <th>Payment Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align: right;" colspan="2">Total Paid Amount</td>
                                <td id="finalPaidAmount"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
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
                    <label for="payment_date">Payment Note</label>
                    <textarea name="payment_note" id="payment_note" class="form-control" rows="3">{{ old('payment_note') }}</textarea>
                    @error('payment_note')
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
    // Initialize Select2 if available
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('#select_invoice').select2({
            placeholder: 'Select invoices',
            allowClear: true,
            width: '100%'
        });
    } else {
        console.error('jQuery or Select2 not loaded. Ensure both are included in layouts.app.');
    }

    const selectInvoice = document.getElementById('select_invoice');
    const tableBody = document.querySelector('#invoice_table tbody');
    const addedInvoices = {};

    // Function to add invoice row
    function addInvoiceRow(invoiceId, due) {
        if (addedInvoices[invoiceId]) return; // prevent duplicates
        addedInvoices[invoiceId] = true;

        const row = document.createElement('tr');
        row.setAttribute('data-id', invoiceId);
        row.innerHTML = `
            <td>${invoiceId}<input type="hidden" name="invoices[${invoiceId}][id]" value="${invoiceId}"></td>
            <td>${due}<input type="hidden" name="invoices[${invoiceId}][due]" value="${due}"></td>
            <td>
                <input type="number" class="form-control" name="invoices[${invoiceId}][payment]"
                    min="0.01" max="${due}" step="any" value="${due}" required>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
        `;
        tableBody.appendChild(row);

        calculteTotalPaidAmount();
    }

    // Load initially selected options (for old input)
    Array.from(selectInvoice.selectedOptions).forEach(option => {
        const invoiceId = option.getAttribute('data-id');
        const due = parseFloat(option.getAttribute('data-due') || 0).toFixed(2);
        if (invoiceId && due > 0) addInvoiceRow(invoiceId, due);
    });

    // On select change, refresh table rows
    jQuery('#select_invoice').on('change', function () {
        // Remove rows that are no longer selected
        const selectedIds = Array.from(selectInvoice.selectedOptions).map(opt => opt.value);
        Object.keys(addedInvoices).forEach(id => {
            if (!selectedIds.includes(id)) {
                const row = tableBody.querySelector(`tr[data-id="${id}"]`);
                if (row) row.remove();
                delete addedInvoices[id];
            }
        });

        // Add new selected rows
        Array.from(selectInvoice.selectedOptions).forEach(option => {
            const invoiceId = option.getAttribute('data-id');
            const due = parseFloat(option.getAttribute('data-due') || 0).toFixed(2);
            if (invoiceId && due > 0) addInvoiceRow(invoiceId, due);
        });
    });

    // Remove button handler
    tableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const row = e.target.closest('tr');
            const id = row.getAttribute('data-id');
            row.remove();
            delete addedInvoices[id];

            // Deselect in select box
            Array.from(selectInvoice.options).forEach(opt => {
                if (opt.value === id) opt.selected = false;
            });

            // Trigger Select2 change to update UI
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery('#select_invoice').trigger('change.select2');
            }
        }

        calculteTotalPaidAmount();
    });

    // Validate payment amounts on submit
    const form = document.getElementById('paymentForm');
    form.addEventListener('submit', function (e) {
        const paymentInputs = tableBody.querySelectorAll('input[name$="[payment]"]');

        // Check if any invoices selected
        if (paymentInputs.length === 0) {
            alert('Please select at least one invoice to pay.');
            e.preventDefault();
            return;
        }

        let valid = true;

        paymentInputs.forEach(input => {
            const value = parseFloat(input.value);
            const max = parseFloat(input.getAttribute('max'));
            if (!value || value <= 0 || value > max) {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please enter valid payment amounts for all selected invoices.');
        }
    });

    function calculteTotalPaidAmount() {
        totalPaidAmount = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const due = parseFloat(row.querySelector('td:nth-child(2)').textContent) || 0;
            const payment = parseFloat(row.querySelector('td:nth-child(3) input').value) || 0;
            totalPaidAmount += payment;
        });
        document.getElementById('finalPaidAmount').textContent = totalPaidAmount.toFixed(2);
    }

    $(document).on('input', 'input[name$="[payment]"]', function () {
        calculteTotalPaidAmount();
    });
});
</script>
@endsection
