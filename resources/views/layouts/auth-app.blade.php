<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{env('APP_NAME')}} | @yield('title')</title>
    <link rel="apple-touch-icon" href="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/vendors/css/forms/icheck/icheck.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/vendors/css/forms/icheck/custom.css') }}">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/bootstrap-extended.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/colors.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/components.css') }}">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/core/menu/menu-types/vertical-menu.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/core/colors/palette-gradient.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/app-assets/css/pages/login-register.css?v3') }}">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href=" {{ asset('public/assets/css/style.css') }}">
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu 1-column   blank-page blank-page" data-open="click" data-menu="vertical-menu" data-col="1-column">
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src=" {{ asset('public/app-assets/vendors/js/vendors.min.js') }}"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src=" {{ asset('public/app-assets/vendors/js/forms/icheck/icheck.min.js') }}"></script>
    <script src=" {{ asset('public/app-assets/vendors/js/forms/validation/jqBootstrapValidation.js') }}"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src=" {{ asset('public/app-assets/js/core/app-menu.js') }}"></script>
    <script src=" {{ asset('public/app-assets/js/core/app.js') }}"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src=" {{ asset('public/app-assets/js/scripts/forms/form-login-register.js') }}"></script>
    <!-- END: Page JS-->

    <script>
        $(document).ready(function() {

            $('#eye').click(function() {

                if ($(this).hasClass('fa-eye-slash')) {

                    $(this).removeClass('fa-eye-slash');

                    $(this).addClass('fa-eye');

                    $('#user-password').attr('type', 'text');

                } else {

                    $(this).removeClass('fa-eye');

                    $(this).addClass('fa-eye-slash');

                    $('#user-password').attr('type', 'password');
                }
            });
        })
    </script>

</body>
<!-- END: Body-->

</html>
