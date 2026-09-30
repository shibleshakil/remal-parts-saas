@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
    </div>
    <div class="content-body">
        @include('partials.session_message')
        <!-- <div id="stock-card" class="d-flex flex-wrap justify-content-between">
            <div class="card flex-fill m-1" style="min-width: 180px;">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="danger font-weight-bold" id="parts-stock-quantity"></h3>
                                <span>Parts Stock Quantity</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-social-dropbox danger font-large-1 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card flex-fill m-1" style="min-width: 180px;">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="success font-weight-bold" id="parts-stock-balance"></h3>
                                <span>Parts Stock Balance</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-credit-card success font-large-1 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card flex-fill m-1" style="min-width: 180px;">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 id="vehicle-stock-quantity" class="info font-weight-bold"></h3>
                                <span>Vehicle Stock Quantity</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-social-dropbox info font-large-1 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card flex-fill m-1" style="min-width: 180px;">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 id="vehicle-stock-balance" class="warning font-weight-bold"></h3>
                                <span>Vehicle Stock Balance</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-credit-card warning font-large-1 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card flex-fill m-1" style="min-width: 180px;">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 id="total-stock-balance" class="primary font-weight-bold"></h3>
                                <span>Total Stock Balance</span>
                            </div>
                            <div class="align-self-center">
                                <i class="icon-credit-card primary font-large-1 float-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- @if(\App\Helpers\CommonHelper::userHasAccess())
            <div>
                <div class="row">
                    <div class="col-12">
                        <div id="profitReport" class="d-flex flex-wrap justify-content-between">
                            <div class="flex-fill mx-1" style="min-width: 100%;">
                                <form id="reportLimit"  class="row gx-3 align-items-center">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-auto">
                                                <select id="reportDate" name="gross_profit_report_date" class="form-control">
                                                    <option value="today">Today</option>
                                                    <option value="last_7_days">Last 7 Days</option>
                                                    <option value="this_month">This Month</option>
                                                    <option value="this_year">This Year</option>
                                                    <option value="custom">Custom</option>
                                                </select>
                                            </div>

                                            <div id="customDate" class="col-auto d-none">
                                                <fieldset class="form-group mb-0 d-inline">
                                                    <input id="startDay" type="date" name="gross_profit_start_day" class="form-control" placeholder="{{__('Start Day')}}" value="{{  session('gross_profit_start_day') ?? '' }}">
                                                </fieldset>

                                                <fieldset class="form-group mb-0 d-inline">
                                                    <input id="endDay" type="date" name="gross_profit_end_day" class="form-control" placeholder="{{__('End Day')}}" value="{{  session('gross_profit_end_day') ?? '' }}">
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="card flex-fill m-1" style="min-width: 180px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="scrapSell" class="danger font-weight-bold">00</h3>
                                                <span>Scrap Sell Amount</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card flex-fill m-1" style="min-width: 180px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="partsSell" class="success font-weight-bold">00</h3>
                                                <span>Parts Sell Amount</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card flex-fill m-1" style="min-width: 180px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="totalSell" class="info font-weight-bold">00</h3>
                                                <span>Total Sell Amount</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card flex-fill m-1" style="min-width: 150px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="purchaseAmount" class="warning font-weight-bold">00</h3>
                                                <span>Total Purchase Amount</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card flex-fill m-1" style="min-width: 150px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="grossProfit" class="primary font-weight-bold">00</h3>
                                                <span>Gross Profit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card flex-fill m-1" style="min-width: 180px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="totalExpense" class="success font-weight-bold">00</h3>
                                                <span>Total Expense</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card flex-fill m-1" style="min-width: 180px;">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-left">
                                                <h3 id="netProfit" class="primary font-weight-bold">00</h3>
                                                <span>Net Profit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif -->
    </div>
</div>

@endsection

@section('script')
<script>
    // $(document).ready(function() {
    //     $.ajax({
    //         url: `{{ route('stockSummary') }}`,
    //         type: 'GET',
    //         dataType: 'json',
    //         cache: false,
    //         beforeSend: function() {
    //             // Show loader before sending the request
    //             blockStatsCard();
    //         },
    //         success: function(resp) {
    //             // try common response shapes safely:
    //             var total = 0;
    //             if (resp) {
    //                 if (resp.status === 'success' && resp.data) {
    //                     totalStockBalance = parseFloat(resp.data[0].parts_stock_balance ?? 0) + parseFloat(resp.data[0].vehicle_stock_balance ?? 0);
    //                     $('#vehicle-stock-balance').text(resp.data[0].vehicle_stock_balance ?? 0);
    //                     $('#vehicle-stock-quantity').text(resp.data[0].vehicle_stock_quantity ?? 0);
    //                     $('#parts-stock-balance').text(resp.data[0].parts_stock_balance ?? 0);
    //                     $('#parts-stock-quantity').text(resp.data[0].parts_stock_quantity ?? 0);
    //                     $('#total-stock-balance').text(totalStockBalance);
    //                 } else {
    //                     console.warn('response structure unexpected, check network tab.');
    //                 }
    //             }

    //         },
    //         error: function(xhr, status, err) {
    //             // window.location.reload();
    //             console.error(status, err, xhr.responseText);
    //             alert('An error occurred while fetching stock summary. Please refresh the page.');
    //             window.location.reload();
    //         },
    //         complete: function() {
    //             // always hide loader and show body
    //             unblockStatsCard();
    //         }
    //     });
    // });


    // $(document).ready(function () {
    //     const $reportDate = $('#reportDate');
    //     const $customDate = $('#customDate');
    //     const $form = $('#reportLimit');

    //     function fetchProfitSummary() {
    //         $.ajax({
    //             url: `{{ route('profitSummary') }}`,
    //             type: 'GET',
    //             data: $form.serialize(),
    //             dataType: 'json',
    //             headers: { 'X-Requested-With': 'XMLHttpRequest' },
    //             beforeSend: function () {
    //                 blockProfitReportCard();
    //             },
    //             success: function (response) {
    //                 if (response.status) {
    //                     $('#scrapSell').text((response.scrapSell ?? 0).toFixed(2));
    //                     $('#partsSell').text((response.partsSell ?? 0).toFixed(2));
    //                     $('#totalSell').text((response.totalSell ?? 0).toFixed(2));
    //                     $('#purchaseAmount').text((response.purchaseAmount ?? 0).toFixed(2));
    //                     $('#totalExpense').text((response.totalExpense ?? 0).toFixed(2));
    //                     $('#grossProfit').text((response.grossProfit ?? 0).toFixed(2));

    //                     // calculate net profit
    //                     const netProfit = parseFloat(response.grossProfit ?? 0) - parseFloat(response.totalExpense ?? 0);
    //                     $('#netProfit').text(netProfit.toFixed(2));
    //                 }
    //             },
    //             error: function (xhr) {
    //                 console.error('Error:', xhr.responseText);
    //             },
    //             complete: function () {
    //                 unblockProfitReportCard();
    //             }
    //         });
    //     }

    //     // 🔹 Handle report date change
    //     $reportDate.on('change', function () {
    //         const selected = $(this).val();

    //         if (selected === 'custom') {
    //             $customDate.removeClass('d-none');
    //         } else {
    //             $customDate.addClass('d-none');
    //             $('#startDay, #endDay').val(''); // clear custom dates
    //             fetchProfitSummary(); // auto-fetch for non-custom selections
    //         }
    //     });

    //     // 🔹 Handle custom date change (trigger only when both dates are filled)
    //     $('#startDay, #endDay').on('change', function () {
    //         if ($('#reportDate').val() === 'custom') {
    //             const start = $('#startDay').val();
    //             const end = $('#endDay').val();
    //             if (start && end) {
    //                 fetchProfitSummary();
    //             }
    //         }
    //     });

    //     // 🔹 Auto-load when page opens
    //     $reportDate.trigger('change');
    // });


    // function blockProfitReportCard() {
    //     // Select all cards under #stock-card
    //     var $profitReportCards = $("#profitReport .card");

    //     $profitReportCards.each(function() {
    //         $(this).block({
    //             message: '<div class="feather icon-refresh-cw icon-spin font-medium-2"></div>',
    //             overlayCSS: {
    //                 backgroundColor: '#fff',
    //                 opacity: 0.8,
    //                 cursor: 'wait'
    //             },
    //             css: {
    //                 border: 0,
    //                 padding: 0,
    //                 backgroundColor: 'transparent'
    //             }
    //         });
    //     });
    // }

    // // To unblock later:
    // function unblockProfitReportCard() {
    //     $(" #profitReport .card").each(function() {
    //         $(this).unblock();
    //     });
    // }


    // function blockStatsCard() {
    //     // Select all cards under #stock-card
    //     var $stockCards = $("#stock-card .card");

    //     // Loop through each card and block it individually
    //     $stockCards.each(function() {
    //         $(this).block({
    //             message: '<div class="feather icon-refresh-cw icon-spin font-medium-2"></div>',
    //             overlayCSS: {
    //                 backgroundColor: '#fff',
    //                 opacity: 0.8,
    //                 cursor: 'wait'
    //             },
    //             css: {
    //                 border: 0,
    //                 padding: 0,
    //                 backgroundColor: 'transparent'
    //             }
    //         });
    //     });
    // }


    // function unblockStatsCard() {
    //     $("#stock-card .card").each(function() {
    //         $(this).unblock();
    //     });
    // }

</script>
@endsection
