<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>
    <div class="main-wrapper p-3">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-1 p-3 m-2 bg-white shadow box-area">
                <div class="col-md-6 rounded-1 d-flex justify-content-center align-items-center flex-column left-box p-3"
                    style="background-image: url({{ asset('img/logo-usni.png') }}); 
                            background-size: cover; 
                            background-position: center; 
                            background-repeat: no-repeat;">
                    {{-- <p class="text-white fs-2">Be verified!</p>
                    <small class="text-white text-wrap text-center">Join experienced platform</small> --}}
                </div>
                <div class="col-md-6 right-box py-2 px-4">
                    <div class="row align-items-center">
                        @yield('child-auth')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('script');
</body>

</html>
