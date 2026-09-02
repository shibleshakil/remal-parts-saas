(function(window, undefined) {
'use strict';

/*
NOTE:
------
PLACE HERE YOUR OWN JAVASCRIPT CODE IF NEEDED
WE WILL RELEASE FUTURE UPDATES SO IN ORDER TO NOT OVERWRITE YOUR JAVASCRIPT CODE PLEASE CONSIDER WRITING YOUR SCRIPT HERE.  */

})(window);

function blockBodyOnAjaxRequest() {
    $.blockUI({
        message: '<div class="feather icon-refresh-cw icon-spin font-medium-2"></div>',
        overlayCSS: {
            backgroundColor: '#FFF',
            opacity: 0.8,
            cursor: 'wait'
        },
        css: {
            border: 0,
            padding: 0,
            backgroundColor: 'transparent'
        }
    });
}

function deleteData(url) {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to delete this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "No",
        confirmButtonText: "Yes",
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn btn-warning mr-10",
            cancelButton: "btn btn-danger ml-1"
        }
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    "_token": $('#csrfToken').val(),
                },
                beforeSend: function () {
                    blockBodyOnAjaxRequest();
                },
                complete: function () {
                    $('body').unblock();
                },
                success: function (res) {
                    Swal.fire({
                        title: res.title,
                        text: res.msg,
                        icon: res.type,
                        confirmButtonClass: "btn btn-success"
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        Swal.fire({
                            title: "Access Denied",
                            text: xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "Sorry, you’re not authorized to perform this action.",
                            icon: "error"
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Something went wrong. Please try again later.",
                            icon: "error"
                        });
                    }
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: "Cancelled",
                text: "No action taken 🙂",
                icon: "error",
                confirmButtonClass: "btn btn-success"
            });
        }
    });
}

function restoreData(url) {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to restore this data!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: 'No',
        confirmButtonText: "Yes, Restore",
        confirmButtonClass: "btn btn-warning mr-10",
        cancelButtonClass: "btn btn-danger ml-1",
        buttonsStyling: false,
    }).then(function (result) {
        console.log("HII");
        if (result.value) {
            $.ajax({
                url: url,
                type: "POST", // or POST, depending on your route
                data: {
                    "_token": $('#csrfToken').val(),
                },
                beforeSend: function () {
                    blockBodyOnAjaxRequest();
                },
                complete: function () {
                    $('body').unblock();
                },

                success: function (res) {
                    Swal.fire({
                        title: res.title ?? "Restored!",
                        text: res.msg ?? "The data has been successfully restored.",
                        icon: res.type ?? "success",
                        confirmButtonClass: "btn btn-success"
                    }).then(function () {
                        location.reload();
                    });
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: "Cancelled",
                text: "No action taken 🙂",
                icon: "info",
                confirmButtonClass: "btn btn-success"
            });
        }
    });
}

// Get initial report date
let initialReportDate = $("#reportDate").val();

// Show/hide on page load
if (initialReportDate === 'custom') {
    $('#customDate').removeClass('d-none');
} else {
    $('#customDate').addClass('d-none');
}

if (initialReportDate === 'vehicle') {
    $('#vehicleFilter').removeClass('d-none');
} else {
    $('#vehicleFilter').addClass('d-none');
}

if (initialReportDate === 'parts') {
    $('#partsFilter').removeClass('d-none');
} else {
    $('#partsFilter').addClass('d-none');
}

// Watch start date
$("#startDay").on('change', function () {
    let startDay = $("#startDay").val();
    let endDay = $("#endDay").val();

    if (startDay && endDay && startDay > endDay) {
        Swal.fire({
            icon: 'warning',
            title: 'Date',
            text: 'End day cannot be earlier than start day...'
        });

        $("#startDay").val('');

        return;
    }
});

// Watch end date
$("#endDay").on('change', function () {
    let startDay = $("#startDay").val();
    let endDay = $("#endDay").val();

    if (startDay && endDay && startDay > endDay) {
        Swal.fire({
            icon: 'warning',
            title: 'Date',
            text: 'End day cannot be earlier than start day...'
        });

        $("#endDay").val('');

        return;
    }
});

// Watch dropdown change
$('#reportDate').on('change', function () {
    let reportDate = $(this).val();

    // Hide all filters first
    $('#customDate, #vehicleFilter, #partsFilter').addClass('d-none');

    // Show the relevant one based on selection
    if (reportDate === 'custom') {
        $('#customDate').removeClass('d-none');
    } else if (reportDate === 'vehicle') {
        $('#vehicleFilter').removeClass('d-none');
    } else if (reportDate === 'parts') {
        $('#partsFilter').removeClass('d-none');
    }
});



$('#reportLimit').on('submit', function(e){
    e.preventDefault();

    const reportDate = $("#reportDate").val();
    const startDay = $("#startDay").val();
    const endDay = $("#endDay").val();
    const vehicleId = $("#vehicleId").val();
    const partId = $("#partId").val();


    // If only one date is selected
    if ((startDay && !endDay) || (!startDay && endDay)) {
        Swal.fire({
            icon: 'warning',
            title: 'Date',
            text: 'Please select both start and end dates.'
        });

        let submitBtn = $this.find('button[type="submit"]');
        submitBtn.removeAttr('disabled');
        submitBtn.find('.spinner-border').remove();
        return;
    }

    // Vehicle validation (if required)
    if (reportDate === 'vehicle' && !vehicleId) {
        Swal.fire({
            icon: 'warning',
            title: 'Vehicle',
            text: 'Please select a vehicl.'
        });

        let submitBtn = $this.find('button[type="submit"]');
        submitBtn.removeAttr('disabled');
        submitBtn.find('.spinner-border').remove();
        return;
    }

     if (reportDate === 'parts' && !partId) {
        Swal.fire({
            icon: 'warning',
            title: 'Vehicle',
            text: 'Please select a parts.'
        });

        let submitBtn = $this.find('button[type="submit"]');
        submitBtn.removeAttr('disabled');
        submitBtn.find('.spinner-border').remove();
        return;
    }

    // All good, submit the form
    this.submit();
});



$('#supplierAddForm').on('submit', function(e) {
    e.preventDefault();
    const data = $(this).serialize();
    let submitBtn = $(this).find('button[type="submit"]');
    let url = $(this).attr('action');
    $.ajax({
        url: url,
        type: "POST",
        data: data,
        complete: function() {
            submitBtn.removeAttr('disabled');
            submitBtn.find('.spinner-border').remove();
        },
        success: function(res) {
            if (res.status === 'success') {
                $('#supplier_id').append(
                    `<option value="${res.supplier.id}" selected>${res.supplier.name}</option>`
                ).trigger('change');
                $('#inlineSupplierForm').modal('hide');
                $('#supplierAddForm')[0].reset();
            }
        },
        error: function(xhr) {

            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function(fieldKey, messages) {
                    // Laravel uses "quantity.0" for array; convert to bracket form: quantity[0]
                    const bracketName = fieldKey.replace(/\.(\d+)/g, '[$1]');

                    // try to find exact input (quantity[0])
                    let $input = $('#supplierAddForm').find(`[name="${bracketName}"]`);

                    // fallback to un-indexed array name (quantity[])
                    if (!$input.length) {
                        const arrayName = bracketName.replace(/\[\d+\]/, '[]');
                        $input = $('#supplierAddForm').find(`[name="${arrayName}"]`);
                    }

                    let message = messages.join(' '); // combine messages if multiple

                    if ($input.length) {
                        // determine the actual name attribute (could be quantity[] or quantity[0])
                        const actualName = $input.first().attr('name');

                        // remove any existing error attached to this input name
                        $('#supplierAddForm').find(`.text-danger[data-field="${actualName}"]`).remove();

                        // insert error after the last element in case of grouped inputs (checkboxes/radios/arrays)
                        $input.last().after(`<span class="text-danger d-block mt-1" data-field="${actualName}">${message}</span>`);
                    } else {
                        // if no matching input was found, show it at the top of the form (fallback)
                        $('#supplierAddForm').prepend(`<div class="text-danger mb-2" data-field="${bracketName}">${message}</div>`);
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

