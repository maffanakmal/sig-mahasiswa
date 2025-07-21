@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Dashboard</h3>
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
    @if (session('loggedInUser')['role'] === 'BAAKPSI')
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3 shadow-sm ">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <div class="icon-card-wrapper p-3 rounded-circle">
                                <i class="bi bi-exclamation-triangle-fill text-white fs-3"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Data Tidak Lengkap</h6>
                            <p class="card-text mb-0 fw-bold fs-5" id="mahasiswaIncompleteCount">0</p>
                            <small class="text-muted">Mahasiswa</small>
                            <p class="card-text mb-0"><a href="{{ route('mahasiswa.index') }}">Lihat data</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Distribusi Mahasiswa Berdasarkan Daerah Domisili</h6>
                    @if (session('loggedInUser')['role'] === 'Warek 3' || session('loggedInUser')['role'] === 'PMB')
                        <a href="{{ route('dashboard.peta.daerah') }}" class="btn btn-sm btn-primary">
                            Lihat Selengkapnya
                        </a>
                    @endif
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="daerahChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
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

        function dataCount() {
            $.ajax({
                url: "{{ route('home.count') }}", // Ensure this is the correct API route
                type: "GET",
                dataType: "json", // Ensure the response is parsed as JSON
                success: function(response) {
                    if (response.status === 200) {

                        $('#mahasiswaCount').text(response.mahasiswa);
                        $('#prodiCount').text(response.prodi);
                        $('#sekolahAsalCount').text(response.asal_sekolah);
                        $('#daerahCount').text(response.daerah);
                        $('#penggunaCount').text(response.pengguna);
                        $('#mahasiswaIncompleteCount').text(response.mahasiswaIncompleteCount);

                        // Render Chart.js
                        const daerahBarChart = document.getElementById('daerahChart').getContext('2d');
                        new Chart(daerahBarChart, {
                            type: 'bar',
                            data: {
                                labels: response.daerahChart.labels,
                                datasets: [{
                                    label: 'Jumlah Mahasiswa',
                                    data: response.daerahChart.values,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Daerah dengan Mahasiswa Terbanyak',
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });

                        const daerahPieChart = document.getElementById('sekolahChart').getContext('2d');
                        new Chart(daerahPieChart, {
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


                    }
                },
                error: function(xhr) {
                    let errorResponse = xhr.responseJSON; // Ambil data JSON error

                    Swal.fire({
                        icon: errorResponse.icon || "error",
                        title: errorResponse.title || "Error",
                        text: errorResponse.message ||
                            "Terjadi kesalahan yang tidak diketahui.",
                    });
                }
            });
        }
    </script>
@endsection
