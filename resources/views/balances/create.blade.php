@extends('layouts.app')
@section('title', 'Add Balance')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-1">
                <h3 class="content-header-title">
                    <a href="{{ route('balances.index') }}" class="btn btn-primary">
                        {{ __('Balance transaction List') }} <i class="feather icon-eye"></i>
                    </a>
                </h3>
            </div>
            <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-12">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Balance In') }}</li>
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
                                <h4 class="card-title">Balance In</h4>
                            </div>
                            <div class="card-content collapse show">
                                <div class="card-body card-dashboard">
                                    <form action="{{ route('balances.store') }}" method="post" class="form"
                                        enctype="multipart/form-data">@csrf
                                        <div class="form-body">
                                            @include('balances.form')
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
@include('sale.bank_modal')
@endsection

@section('script')
    <script>
        $('#payment_method').on('change', function() {
            if ($(this).val() === 'bank_transfer') {
                $('#bank_list_wrapper').show(); // Show bank list
            } else {
                $('#bank_list_wrapper').hide(); // Hide when not bank transfer
                $('#bank_id').val(''); // Reset bank selection
            }
        });

        $(function() {
            const $form = $('#saleForm');
            let submitBtn = $form.find('button[type="submit"]');
            // 1) Remove the related error as soon as user changes the field
            $form.on('input change', 'input, textarea, select', function() {
                const name = $(this).attr('name');
                if (!name) return;
                // remove any error spans that were tagged for this input name
                $form.find(`.text-danger[data-field="${name}"]`).remove();
            });

            // 2) Submit handler (AJAX)
            $form.on('submit', function(e) {
                if ($('#payment_method').val() === 'bank_transfer') {
                    if ($('#bank_id').val() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bank Not Selected',
                            text: 'Please Select A Bank First!...'
                        });
                        return false;
                    }
                }

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
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            Swal.fire({
                                title: "Error",
                                text: "You have some error in form input field. Please check input field value.",
                                type: "error"
                            });

                            const errors = xhr.responseJSON.errors;

                            $.each(errors, function(fieldKey, messages) {
                                const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');
                                let $input = $form.find(`[name="${bracketName}"]`);

                                if (!$input.length) {
                                    const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                                    $input = $form.find(`[name="${arrayName}"]`);
                                }

                                let message = messages.join(' ');

                                if ($input.length) {
                                    const actualName = $input.first().attr('name');

                                    // Remove existing error for this field
                                    $form.find(`.text-danger[data-field="${actualName}"]`).remove();

                                    // Determine if input is inside an input-group
                                    const $inputGroup = $input.closest('.input-group');

                                    if ($inputGroup.length) {
                                        // Place error *after* the entire input-group
                                        $inputGroup.after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                                    } else {
                                        // Otherwise place error directly after the input
                                        $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                                    }
                                } else {
                                    // Fallback — show error at top of form if input not found
                                    $form.prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: "Something went wrong.",
                                icon: "error"
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
