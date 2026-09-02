@extends('layouts.app')
@section('title', 'Supplier Due Payment')

@section('content')
<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Supplier Due Payment</h3>
        <a href="{{ route('supplier.index') }}" class="btn btn-outline-primary">
            <i class="fa fa-list"></i> Supplier List
        </a>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('supplier.index') }}">Supplier</a></li>
            <li class="breadcrumb-item active">Supplier Due Payment</li>
        </ol>
    </nav>

    <!-- Form -->
    <div class="card">
        <div class="card-header">
            <h5>Supplier: {{ $purchaseLines->first()?->supplier ? $purchaseLines->first()?->supplier?->name : 'Walking Supplier' }}</h5>
        </div>
        <div class="card-body">
            @include('partials.session_message')

            <form action="{{ route('supplier.pay', $purchaseLines->first()?->supplier_id) }}" method="POST" id="paymentForm">
                @csrf
                <div class="form-group mb-3">
                    <label for="select_transetion">Select Transection</label>
                    <select id="select_transetion" class="form-control select2" multiple >
                        @foreach($purchaseLines as $purchaseLine)
                            <option value="{{ $purchaseLine->invoice_no }}" data-id="{{ $purchaseLine->invoice_no }}" data-due="{{ $purchaseLine->due_amount }}">
                                Invoice #{{ $purchaseLine->invoice_no }}  -- (Amount: {{ number_format($purchaseLine->invoice_amount, 2) }} -- Due: {{ number_format($purchaseLine->due_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('select_transetion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Selected Transetions</label>
                    <table class="table table-bordered" id="transetion_table">
                        <thead>
                            <tr>
                                <th>Invoice#</th>
                                <th>Due Amount</th>
                                <th>Payment Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
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
                    <label for="payment_date">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    @error('payment_date')
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

                <div class="text-end">
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </div>
            </form>
        </div>
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
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Select2 if available
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('#select_transetion').select2({
            placeholder: 'Select Transection',
            allowClear: true,
            width: '100%'
        });
    } else {
        console.error('jQuery or Select2 not loaded. Ensure both are included in layouts.app.');
    }

    const selectInvoice = document.getElementById('select_transetion');
    const tableBody = document.querySelector('#transetion_table tbody');
    const addedInvoices = {};

    // Function to add invoice row
    function addInvoiceRow(invoiceId, due) {
        if (addedInvoices[invoiceId]) return; // prevent duplicates
        addedInvoices[invoiceId] = true;

        const row = document.createElement('tr');
        row.setAttribute('data-id', invoiceId);
        row.innerHTML = `
            <td>${invoiceId}<input type="hidden" name="transetions[${invoiceId}][id]" value="${invoiceId}"></td>
            <td>${due}<input type="hidden" name="transetions[${invoiceId}][due]" value="${due}"></td>
            <td>
                <input type="number" class="form-control" name="transetions[${invoiceId}][payment]"
                    min="0.01" max="${due}" step="any" value="${due}" required>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
        `;
        tableBody.appendChild(row);
        calculteTotalPaidAmount();
    }

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

    // Load initially selected options (for old input)
    Array.from(selectInvoice.selectedOptions).forEach(option => {
        const invoiceId = option.getAttribute('data-id');
        const due = parseFloat(option.getAttribute('data-due') || 0).toFixed(2);
        if (invoiceId && due > 0) addInvoiceRow(invoiceId, due);
    });

    // On select change, refresh table rows
    jQuery('#select_transetion').on('change', function () {
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
                jQuery('#select_transetion').trigger('change.select2');
            }

            calculteTotalPaidAmount();
        }
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


        // 2) Submit handler (AJAX)
        e.preventDefault();
        const formEl = this;
        let formData = new FormData(formEl);

        // remove previous errors inside this form
        $(form).find('.text-danger').remove();
        const submitBtn = $(this).find('button[type="submit"]');


        $.ajax({
            url: $(form).attr('action'),
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
                            window.location.href = "{{ route('supplier.index') }}";
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

