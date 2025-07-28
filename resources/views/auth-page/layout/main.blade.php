<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>
    <div class="main-wrapper p-3">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-1 p-3 m-2 bg-white shadow box-area">
                <div class="col-md-6 rounded-1 d-flex justify-content-center align-items-center flex-column left-box p-3">
                    <span class="fw-bold d-block text-center fs-1 fs-md-1 fs-lg-1 fs-xl-display-1" style="color: #27548A;">
                        USNIGIS
                    </span>
                </div>
                <div class="col-md-6 right-box py-2 px-4">
                    <div class="row align-items-center">
                        @yield('child-auth')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/boxicons.js') }}"></script>
    @yield('script');
</body>

</html>
