<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{env('APP_NAME') }} | @yield('title')</title>
    <link rel="apple-touch-icon" href="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/vendors/css/forms/selects/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/vendors/css/charts/jquery-jvectormap-2.0.3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/vendors/css/charts/morris.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/vendors/css/extensions/unslider.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/vendors/css/weather-icons/climacons.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/css/plugins/forms/form-inputs-groups.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/fonts/simple-line-icons/style.min.css') }}">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/bootstrap-extended.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/colors.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/components.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/vendors/css/extensions/sweetalert2.min.css')}}">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/core/menu/menu-types/vertical-menu.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/app-assets/css/core/colors/palette-gradient.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/assets/css/dropify.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/vendors/css/pickers/datetime/bootstrap-datetimepicker.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('public/app-assets/vendors/css/pickers/pickadate/pickadate.css')}}">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/style.css?v7') }}">
    <!-- END: Custom CSS-->

    @yield('css')

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu 2-columns fixed-navbar" data-open="click" data-menu="vertical-menu"
    data-col="2-columns">
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <!-- BEGIN: Header-->
    @include('layouts.header')
    <!-- END: Header-->


    <!-- BEGIN: Main Menu-->
    @include('layouts.sidebar')
    <!-- END: Main Menu-->

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        @yield('content')
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- BEGIN: Footer-->
    @include('layouts.footer')
    <!-- END: Footer-->


    <!-- BEGIN: Vendor JS-->
    <script src=" {{ asset('public/app-assets/vendors/js/vendors.min.js') }}"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->

    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src=" {{ asset('public/app-assets/js/core/app-menu.js') }}"></script>
    <script src=" {{ asset('public/app-assets/js/core/app.js') }}"></script>
    <script src="{{ asset ('public/app-assets/vendors/js/extensions/sweetalert2.all.min.js')}}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/datatable/datatables.min.js') }}"></script>

    <script src="{{ asset('public/app-assets/vendors/js/tables/datatable/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/jszip.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('public/app-assets/vendors/js/tables/buttons.print.min.js') }}"></script>

    <script src="{{ asset ('public/app-assets/vendors/js/pickers/dateTime/moment-with-locales.min.js')}}"></script>
    <script src="{{ asset ('public/app-assets/vendors/js/pickers/dateTime/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="{{ asset ('public/app-assets/vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{ asset ('public/app-assets/vendors/js/pickers/pickadate/picker.date.js')}}"></script>

    <script src="{{ asset ('public/assets/js/dropify-init.js')}}"></script>
    <script src="{{ asset ('public/assets/js/dropify.min.js')}}"></script>
    <script src="{{ asset ('public/assets/js/scripts.js?v8')}}"></script>
    <script src="{{ asset ('public/assets/js/datatable.js')}}"></script>
    <script src="{{ asset('public/app-assets/js/scripts/pickers/dateTime/bootstrap-datetime.js') }}"></script>
    <!-- END: Theme JS-->
    <script type="text/javascript">
        $(function () {

            $(".select2").select2();
            $('.datetime').datetimepicker({
                // format: 'DD-MM-YYYY HH:mm',
                format: 'YYYY-MM-DD',
            });
        })

        $('form').on('submit', function (e) {
            let formEl = this;
            let submitBtn = $(this).find('button[type="submit"]');
            submitBtn.attr('disabled', 'disabled');
            submitBtn.append(' <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        })
    </script>


    @yield('script')

</body>
<!-- END: Body-->

</html>
