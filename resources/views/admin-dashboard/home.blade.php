@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Dashboard</h3>
        @if (session('loggedInUser')['role'] === 'BAAKPSI')    
            <div class="dropdown">
            <button class="btn btn-sm btn-warning dropdown-toggle d-inline-flex align-items-center gap-1"
                    type="button" id="dropdownBackupBtn"
                    data-bs-toggle="dropdown" aria-expanded="false">
                <box-icon name="cloud-download"></box-icon>
                Backup Data
            </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownBackupBtn">     
                    <li><a class="dropdown-item" id="exportDaerahBtn" onclick="backupDaerah()">Data Daerah</a></li>
                    <li><a class="dropdown-item" id="exportSekolahBtn" onclick="backupSekolah()">Data Sekolah</a></li> 
                    <li><a class="dropdown-item" id="exportProdiBtn" onclick="backupProdi()">Data Program Studi</a></li>
                    <li><a class="dropdown-item" id="exportMahasiswaBtn" onclick="backupMahasiswa()">Data Mahasiswa</a></li>
                </ul>
            </div>
        @endif
    </div>
    @if ($user && empty($user->email))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Perhatian!</strong> Anda belum memiliki email. Silakan lengkapi data di <a
                href="{{ route('home.pengaturan') }}">Pengaturan Akun</a>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        @if (session('loggedInUser')['role'] === 'BAAKPSI')
            <div class="col-md-3">
                <div class="card mb-3 shadow-sm ">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-2">
                            <div class="icon-card-wrapper p-2 rounded-circle">
                                <img src="{{ asset('img/programmer.png') }}" alt="#">
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Pengguna</h6>
                            <p class="card-text mb-0 fw-bold fs-5" id="penggunaCount">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3 shadow-sm ">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-2">
                            <div class="icon-card-wrapper p-2 rounded-circle">
                                <img src="{{ asset('img/map.png') }}" alt="#">
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Daerah</h6>
                            <p class="card-text mb-0 fw-bold fs-5" id="daerahCount">0</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="card mb-3 shadow-sm ">
                <div class="card-body d-flex align-items-center">
                    <div class="me-2">
                        <div class="icon-card-wrapper p-2 rounded-circle">
                            <img src="{{ asset('img/school.png') }}" alt="#">
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Sekolah</h6>
                        <p class="card-text mb-0 fw-bold fs-5" id="sekolahAsalCount">0</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3 shadow-sm ">
                <div class="card-body d-flex align-items-center">
                    <div class="me-2">
                        <div class="icon-card-wrapper p-2 rounded-circle">
                            <img src="{{ asset('img/education.png') }}" alt="#">
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Program Studi</h6>
                        <p class="card-text mb-0 fw-bold fs-5" id="prodiCount">0</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3 shadow-sm ">
                <div class="card-body d-flex align-items-center">
                    <div class="me-2">
                        <div class="icon-card-wrapper p-2 rounded-circle">
                            <img src="{{ asset('img/graduating-student.png') }}" alt="#">
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Mahasiswa</h6>
                        <p class="card-text mb-0 fw-bold fs-5" id="mahasiswaCount">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Distribusi Mahasiswa per Daerah dan Program Studi</h6>
                    @if (session('loggedInUser')['role'] === 'Warek 3' || session('loggedInUser')['role'] === 'PMB')
                        <a href="{{ route('dashboard.peta.daerah') }}" class="btn btn-sm btn-primary">
                            Lihat Selengkapnya
                        </a>
                    @endif
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="daerahJurusanChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Distribusi Mahasiswa Berdasarkan Jenis Sekolah Asal</h6>
                    @if (session('loggedInUser')['role'] === 'Warek 3' || session('loggedInUser')['role'] === 'PMB')
                        <a href="{{ route('dashboard.peta.sekolah') }}" class="btn btn-sm btn-primary">
                            Lihat Selengkapnya
                        </a>
                    @endif
                </div>
                <div class="card-body text-center">
                    <canvas id="sekolahChart" style="max-width: 300px; margin: auto;"></canvas>
                </div>
            </div>
        </div>
    </div>


    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            dataCount();
        });

        let sekolahChartInstance;
        let daerahJurusanChartInstance;

        function dataCount() {
            $.ajax({
                url: "{{ route('home.count') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === 200) {
                        $('#mahasiswaCount').text(response.jumlah_mahasiswa);
                        $('#prodiCount').text(response.jumlah_prodi);
                        $('#sekolahAsalCount').text(response.jumlah_sekolah);
                        $('#daerahCount').text(response.jumlah_daerah);
                        $('#penggunaCount').text(response.jumlah_pengguna);

                        if (response.jumlah_daerah === 0) {
                            $('#exportDaerahBtn')
                                .addClass('disabled')
                                .css({ color: '#ccc', pointerEvents: 'none' });
                        }
                        if (response.jumlah_prodi === 0) {
                            $('#exportProdiBtn')
                                .addClass('disabled')
                                .css({ color: '#ccc', pointerEvents: 'none' });
                        }
                        if (response.jumlah_sekolah === 0) {
                            $('#exportSekolahBtn')
                                .addClass('disabled')
                                .css({ color: '#ccc', pointerEvents: 'none' });
                        }
                        if (response.jumlah_mahasiswa === 0) {
                            $('#exportMahasiswaBtn')
                                .addClass('disabled')
                                .css({ color: '#ccc', pointerEvents: 'none' });
                        }

                        if (sekolahChartInstance) sekolahChartInstance.destroy();
                        if (daerahJurusanChartInstance) daerahJurusanChartInstance.destroy();

                        // Chart 2: Doughnut sekolah
                        const daerahPieChart = document.getElementById('sekolahChart').getContext('2d');
                        sekolahChartInstance = new Chart(daerahPieChart, {
                            type: 'doughnut',
                            data: {
                                labels: response.sekolahChart.labels,
                                datasets: [{
                                    data: response.sekolahChart.values,
                                    backgroundColor: ['#007bff', '#28a745', '#ffc107',
                                        '#dc3545', '#6c757d'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Distribusi Mahasiswa Berdasarkan Jenis Sekolah',
                                        font: {
                                            size: 14
                                        }
                                    },
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 12
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Chart 3: Bar chart daerah + jurusan
                        const programStudiBarChart = document.getElementById('daerahJurusanChart').getContext(
                            '2d');
                        const warna = [
                            '#007bff', // biru
                            '#28a745', // hijau
                            '#ffc107', // kuning
                            '#dc3545', // merah
                            '#6f42c1', // ungu
                            '#20c997', // teal
                            '#fd7e14', // oranye
                            '#6610f2', // ungu gelap
                            '#e83e8c', // pink
                            '#343a40', // abu-abu gelap
                            '#17a2b8', // biru muda
                            '#8e44ad', // ungu keunguan
                            '#2ecc71', // hijau terang
                            '#f39c12', // kuning oranye
                            '#c0392b' // merah tua
                        ];

                        daerahJurusanChartInstance = new Chart(programStudiBarChart, {
                            type: 'bar',
                            data: {
                                labels: response.daerahJurusanChart.labels,
                                datasets: response.daerahJurusanChart.datasets.map((ds, i) => ({
                                    ...ds,
                                    backgroundColor: warna[i % warna.length],
                                    borderWidth: 1
                                }))
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Jumlah Mahasiswa per Daerah dan Program Studi',
                                        font: {
                                            size: 14
                                        }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.dataset.label || '';
                                                const value = context.raw;

                                                if (value === 0)
                                                    return ''; // Jangan tampilkan tooltip untuk data 0
                                                return `${label}: ${value}`;
                                            }
                                        }
                                    },

                                    legend: {
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true
                                    },
                                    y: {
                                        stacked: true,
                                        title: {
                                            display: true,
                                            text: 'Jumlah Mahasiswa'
                                        },
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                },
                error: function(xhr) {
                    let errorResponse = xhr.responseJSON;
                    Swal.fire({
                        icon: errorResponse.icon || "error",
                        title: errorResponse.title || "Error",
                        text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                    });
                }
            });
        }

        function backupDaerah() {
            Swal.fire({
                title: "Ingin Backup Data Daerah?",
                html: 'File akan diunduh dalam format Excel.',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Backup!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('daerah.export') }}";
                }
            });
        }
        function backupSekolah() {
            Swal.fire({
                title: "Ingin Backup Data Sekolah?",
                html: 'File akan diunduh dalam format Excel.',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Backup!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('sekolah.export') }}";
                }
            });
        }
        function backupProdi() {
            Swal.fire({
                title: "Ingin Backup Data Prodi?",
                html: 'File akan diunduh dalam format Excel.',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Backup!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('prodi.export') }}";
                }
            });
        }
        function backupMahasiswa() {
            Swal.fire({
                title: "Ingin Backup Data Mahasiswa?",
                html: 'File akan diunduh dalam format Excel.',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Backup!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('mahasiswa.export') }}";
                }
            });
        }
    </script>
@endsection
