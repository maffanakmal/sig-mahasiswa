@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Dashboard</h3>
    </div>
    @if ($user && empty($user->email))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Perhatian!</strong> Anda belum memiliki email. Silakan lengkapi data di <a href="{{ route('home.pengaturan') }}">Pengaturan Akun</a>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div class="row">
        @if (session('loggedInUser')['role'] === 'BAAKPSI')
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">
                                <img src="{{ asset('img/programmer.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Pengguna</h6>
                            <p class="card-text" id="penggunaCount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">
                                <img src="{{ asset('img/map.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Daerah</h6>
                            <p class="card-text" id="daerahCount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">
                                <img src="{{ asset('img/school.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Sekolah</h6>
                            <p class="card-text" id="sekolahAsalCount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">
                                <img src="{{ asset('img/education.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Jurusan</h6>
                            <p class="card-text" id="jurusanCount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">
                                <img src="{{ asset('img/graduating-student.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Mahasiswa</h6>
                            <p class="card-text" id="mahasiswaCount"></p>
                        </div>
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
                        $('#jurusanCount').text(response.jurusan);
                        $('#sekolahAsalCount').text(response.asal_sekolah);
                        $('#daerahCount').text(response.daerah);
                        $('#penggunaCount').text(response.pengguna);
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
