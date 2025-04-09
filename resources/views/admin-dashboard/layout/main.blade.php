<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}" />
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/maps.css') }}" />
</head>

<body>
    <div class="main-wrapper">
        <aside id="dashboard-sidebar">
            <div class="d-flex justify-content-between p-3">
                <div class="sidebar-logo poppins-bold">
                    <a href="#">APX</a>
                </div>
                <button class="toggle-btn border-0" type="button">
                    <i class="bx bx-menu"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="{{ route('home.index') }}" class="sidebar-link">
                        <i class='bx bx-home'></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('mahasiswa.index') }}" class="sidebar-link">
                        <i class='bx bxs-graduation'></i>
                        <span>Mahasiswa</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href=""
                    class="sidebar-link collapsed has-dropdown d-flex align-items-center justify-content-between"
                    data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"
                    aria-controls="collapseOne">
                        <div>
                            <i class='bx bx-map-alt'></i>
                            <span>Peta</span>
                        </div>
                        <i class="caret-nav bx bx-caret-down rotate"></i>
                    </a>
                    <ul id="collapseOne" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('kelurahan.index') }}" class="sidebar-link"> Kelurahan </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('daerah.index') }}" class="sidebar-link"> Daerah </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href=""
                    class="sidebar-link collapsed has-dropdown d-flex align-items-center justify-content-between"
                    data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                    aria-controls="collapseTwo">
                        <div>
                            <i class='bx bx-grid-horizontal'></i>
                            <span>Klasterisasi</span>
                        </div>
                        <i class="caret-nav bx bx-caret-down rotate"></i>
                    </a>
                    <ul id="collapseTwo" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="" class="sidebar-link"> Dataset </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href=""
                    class="sidebar-link collapsed has-dropdown d-flex align-items-center justify-content-between"
                    data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false"
                    aria-controls="collapseThree">
                        <div>
                            <i class='bx bx-bar-chart-alt-2'></i>
                            <span>Grafik</span>
                        </div>
                        <i class="caret-nav bx bx-caret-down rotate"></i>
                    </a>
                    <ul id="collapseThree" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ route('grafik.peta') }}" class="sidebar-link"> Peta Sebaran </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="" class="sidebar-link"> Bar Chart </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </aside>
        <div class="main">
            <nav class="navbar dashboard-navbar navbar-expand sticky-top shadow-sm">
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown d-flex align-items-center">
                            <div class="user-info me-2 text-end">
                                <span class="user-name d-block fw-bold">
                                    {{-- {{ session('loggedInUser')['nama_lengkap'] ?? 'Guest' }} --}}
                                </span>
                                <span class="user-title text-muted">
                                    {{-- {{ session('loggedInUser')['nip'] ?? 'User' }} --}}
                                </span>
                            </div>
                            <a href="#" data-bs-toggle="dropdown" type="button" aria-expanded="false" class="nav-icon pe-md-0">
                                {{-- <img src="storage/img/{{ session('loggedInUser')['foto_user'] }}" 
                                     alt="Profile" 
                                     class="avatar img-fluid rounded-circle dropdown-toggle" 
                                     width="40" 
                                     id="navbar-avatar" /> --}}
                            </a>                            
                            <div class="caret-icon mx-1">
                                <i class="bx bx-caret-down"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-end rounded-1 border-0 shadow mt-3">
                                <a href="" class="dropdown-item">
                                    <i class="bx bx-bell"></i>
                                    <span>Notification</span>
                                </a>
                                <a href="" class="dropdown-item">
                                    <i class="bx bx-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="#" method="POST" id="logout-form">
                                    @csrf
                                    <a href="javascript:void(0)" class="dropdown-item" id="btn-logout">
                                        <i class="bx bx-log-out"></i>
                                        <span>Logout</span>
                                    </a>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content min-vh-100 px-3 py-4">
                <div class="container-fluid">
                    @yield('child-content')
                </div>
            </main>
        </div>
    </div>


    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/esri-leaflet/dist/esri-leaflet.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>

    <script>
        // $('#btn-logout').click(function(e) {
        //     e.preventDefault();
        //     Swal.fire({
        //         title: 'Apakah Anda yakin ingin logout?',
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonText: 'Ya, Logout',
        //         cancelButtonText: 'Batal'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             $.ajax({
        //                 url: "", // Pastikan route sesuai
        //                 method: 'POST',
        //                 data: $('#logout-form').serialize(), // Kirim CSRF form
        //                 dataType: 'json',
        //                 success: function(res) {
        //                     if (res.status === 200) {
        //                         Swal.fire({
        //                             icon: 'success',
        //                             title: 'Logout Berhasil!',
        //                             showConfirmButton: false,
        //                             timer: 1500
        //                         }).then(() => {
        //                             window.location.href =
        //                                 "";
        //                         });
        //                     }
        //                 },
        //                 error: function(xhr) {
        //                     Swal.fire({
        //                         icon: 'error',
        //                         title: 'Terjadi kesalahan!',
        //                         text: 'Gagal logout, silakan coba lagi.'
        //                     });
        //                 }
        //             });
        //         }
        //     });
        // });
    </script>
    @yield('script');
</body>

</html>