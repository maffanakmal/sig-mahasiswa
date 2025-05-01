@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Dashboard</h3>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">

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
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">

                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Jurusan</h6>
                            <p class="card-text" id="mahasiswaCount"></p>
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

                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Sekolah Asal</h6>
                            <p class="card-text" id="mahasiswaCount"></p>
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

                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Daerah</h6>
                            <p class="card-text" id="mahasiswaCount"></p>
                        </div>
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
                        // ✅ Update count display
                        $('#mahasiswaCount').text(response.mahasiswaCount);
                    }
                },
                error: function(xhr) {
                    console.error("Failed to fetch mahasiswa data:", xhr);
                }
            });
        }
    </script>
@endsection
