<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>
    <nav class="navbar navbar-expand-md bg-primary sticky-top shadow-sm p-2" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="/">
                Gisapp
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-2 mb-2 mb-lg-0">
                        <a class="btn btn-sm btn-light rounded-5 px-4" href="{{ route('login') }}">Masuk</a>
                    </li>
                </ul>
            </div>
    </div>
</nav>

<div class="container min-vh-100">
    @yield('jumbotron')
</div>

<div class="container">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3">
        <div class="col-md-4 d-flex align-items-center">
            <span class="mb-3 mb-md-0 text-dark">&copy; <span id="year"></span> Made With &hearts; By Apx
            </span>
        </div>

        <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
            <li class="ms-3">
                <a href="" class="text-reset text-decoration-none fs-5">
                    <i class="fa-brands fa-instagram"></i>
                </a>
            </li>
            <li class="ms-3">
                <a href="" class="text-reset text-decoration-none fs-5">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
            </li>
            <li class="ms-3">
                <a href="" class="text-reset text-decoration-none fs-5">
                    <i class="fa-brands fa-facebook"></i>
                </a>
            </li>
        </ul>
    </footer>
</div>

<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>
</body>

</html>