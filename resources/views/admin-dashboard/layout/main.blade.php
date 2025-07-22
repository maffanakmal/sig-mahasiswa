<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" />
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
                    <a href="#">USNIGIS</a>
                </div>
                <button class="toggle-btn border-0" type="button">
                    <box-icon name='menu' color='white' size='lg'></box-icon>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item {{ Request::is('dashboard/home') ? 'status-active' : '' }}">
                    <a href="{{ route('home.index') }}" class="sidebar-link">
                        <box-icon name='home' color='white'></box-icon>
                        <span>Beranda</span>
                    </a>
                </li>
                @if (session('loggedInUser')['role'] === 'BAAKPSI')
                    <li class="sidebar-item {{ Request::is('dashboard/pengguna') ? 'status-active' : '' }}">
                        <a href="{{ route('pengguna.index') }}" class="sidebar-link">
                            <box-icon name='user' color='white'></box-icon>
                            <span>Pengguna</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('dashboard/daerah') ? 'status-active' : '' }}">
                        <a href="{{ route('daerah.index') }}" class="sidebar-link">
                            <box-icon name='map' color='white'></box-icon>
                            <span>Daerah</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('dashboard/sekolah') ? 'status-active' : '' }}">
                        <a href="{{ route('sekolah.index') }}" class="sidebar-link">
                            <box-icon type='solid' name='school' color='white'></box-icon>
                            <span>Sekolah</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('dashboard/prodi') ? 'status-active' : '' }}">
                        <a href="{{ route('prodi.index') }}" class="sidebar-link">
                            <box-icon name='book' color='white'></box-icon>
                            <span>Program Studi</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('dashboard/mahasiswa') ? 'status-active' : '' }}">
                        <a href="{{ route('mahasiswa.index') }}" class="sidebar-link">
                            <box-icon type='solid' name='graduation' color='white'></box-icon>
                            <span>Mahasiswa</span>
                        </a>
                    </li>
                @endif
                @if (session('loggedInUser')['role'] === 'Warek 3' || session('loggedInUser')['role'] === 'PMB')
                    <li class="sidebar-item">
                        <a href=""
                            class="sidebar-link collapsed has-dropdown d-flex align-items-center justify-content-between"
                            data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"
                            aria-controls="collapseOne">
                            <div>
                                <box-icon name='map-alt' color='white'></box-icon>
                                <span>Peta</span>
                            </div>
                            <box-icon name='caret-down' class="caret-nav rotate" color='white'></box-icon>
                        </a>
                        <ul id="collapseOne" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item {{ Request::is('dashboard/peta/daerah') ? 'status-active' : '' }}">
                                <a href="{{ route('dashboard.peta.daerah') }}" class="sidebar-link"> Daerah </a>
                            </li>
                            <li
                                class="sidebar-item  {{ Request::is('dashboard/peta/sekolah') ? 'status-active' : '' }}">
                                <a href="{{ route('dashboard.peta.sekolah') }}" class="sidebar-link"> Sekolah </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </aside>
        <div class="main">
            <nav class="navbar dashboard-navbar navbar-expand sticky-top shadow-sm">
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown d-flex align-items-center">
                            <div class="user-info me-2 text-end">
                                <span class="user-name d-block fw-bold text-white">
                                    {{ session('loggedInUser')['nama_lengkap'] ?? 'Guest' }}
                                </span>
                                <span class="user-title text-white">
                                    {{ session('loggedInUser')['role'] ?? 'User' }}
                                </span>
                            </div>
                            <div class="caret-icon" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                                <box-icon name='caret-down' class="caret-nav rotate" color='white' style="width: 34px"></box-icon>
                            </div>
                            <div class="dropdown-menu dropdown-menu-end rounded-1 border-0 shadow mt-3">
                                <a href="{{ route('home.pengaturan') }}" class="dropdown-item mb-2">
                                    <box-icon name='cog' color='black'></box-icon>
                                    <span>Pengaturan</span>
                                </a>
                                <form action="#" id="logout-form">
                                    @csrf
                                    <a href="javascript:void(0)" class="dropdown-item" id="btn-logout">
                                        </i><box-icon name='log-out' color='black'></box-icon>
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
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('js/esri-leaflet.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/boxicons.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>

    <script>
        $('#btn-logout').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin ingin logout?',
                text: 'Anda akan keluar dari sesi saat ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('logout') }}",
                        method: 'POST',
                        data: $('#logout-form').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 200) {
                                Swal.fire({
                                    icon: res.icon || 'success',
                                    title: res.title || 'Logout berhasil!',
                                    text: res.message || '',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.replace("/");
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi kesalahan!',
                                text: xhr.responseJSON?.message ||
                                    'Gagal logout, silakan coba lagi.'
                            });
                        }
                    });
                }
            });
        });
    </script>
    @yield('script');
</body>

</html>
