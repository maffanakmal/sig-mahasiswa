@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Daerah</h3>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info!</strong> Untuk mengimpor data daerah, silakan unduh template <a
                href="{{ asset('template/daerah_template.xlsx') }}" class="text-decoration-none">di sini</a>. Pastikan
            untuk mengisi data sesuai dengan format yang telah ditentukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <p class="mb-2">Import Excel Daerah</p>
        <form action="#" id="importDaerahForm" enctype="multipart/form-data" class="d-flex align-items-end gap-2 mb-3">
            @csrf
            <div class="input-group w-50">
                <input type="file" class="form-control" name="import_daerah" id="import_daerah"
                    aria-describedby="btnImport" aria-label="Upload">
                <button class="btn btn-success" type="submit" id="btnImport"><i class='bx bx-spreadsheet'></i>
                    Import</button>
                <div class="invalid-feedback" id="error-import_daerah"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" onclick="daerahDeleteAll()"><i class='bx bx-trash'></i> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="daerahModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="daerahTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Kode Daerah</th>
                            <th>Nama Daerah</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="daerahModal" tabindex="-1" aria-labelledby="daerahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="daerahModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="daerahForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="daerah_id" id="daerah_id">
                        <div class="form-group mb-3">
                            <label for="kode_daerah" class="form-label">Kode Daerah</label>
                            <input type="text" class="form-control" id="kode_daerah" placeholder="Masukkan Kode Daerah"
                                name="kode_daerah" value="{{ old('kode_daerah') }}" autofocus required>
                            <div class="invalid-feedback" id="error-kode_daerah"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="nama_daerah" class="form-label">Nama Daerah</label>
                            <input type="text" class="form-control" id="nama_daerah" placeholder="Masukkan Nama Daerah"
                                name="nama_daerah" value="{{ old('nama_daerah') }}"required>
                            <div class="invalid-feedback" id="error-nama_daerah"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" placeholder="Masukkan Latitude"
                                name="latitude" value="{{ old('latitude') }}" required>
                            <div class="invalid-feedback" id="error-latitude"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" placeholder="Masukkan Longitude"
                                name="longitude" value="{{ old('longitude') }}" required>
                            <div class="invalid-feedback" id="error-longitude"></div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">Simpan</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let method;
        let daerah_id = null;

        $(document).ready(function() {
            daerahTable();
        });

        function daerahTable() {
            var table = $('#daerahTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('daerah.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'kode_daerah',
                        name: 'kode_daerah',
                    },
                    {
                        data: 'nama_daerah',
                        name: 'nama_daerah',
                    },
                    {
                        data: 'latitude',
                        name: 'latitude',
                    },
                    {
                        data: 'longitude',
                        name: 'longitude',
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function daerahModal() {
            $('#daerahForm')[0].reset();
            method = 'create';
            daerah_id;

            $('#daerahModal').modal('show');
            $('#daerahModalLabel').text('Tambah Data Daerah');
            $('#saveBtn').text('Simpan');
        }

        $('#daerahForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('daerah.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!daerah_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Daerah ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('daerah.update', '') }}/' + daerah_id;
                formData.append('_method', 'PUT'); // Laravel expects PUT for updates
                httpMethod = 'POST'; // FormData does not support PUT, so use POST with `_method`
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: httpMethod,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 200) {
                        $('#daerahModal').modal('hide');
                        $('#daerahForm').trigger('reset');

                        $('#daerahTable').DataTable().ajax.reload();

                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // 422 = Error Validasi Laravel
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('.form-control').on('input', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else {
                        let errorResponse = xhr.responseJSON; // Ambil data JSON error

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message ||
                                "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });
        });

        $('#importDaerahForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('daerah.import') }}';
            let httpMethod = 'POST'; // Default method for create

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: httpMethod,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 200) {
                        $('#importDaerahForm')[0].reset();
                        $('#daerahTable').DataTable().ajax.reload();

                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // 422 = Validation Error
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('.form-control').on('input', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else {
                        let errorResponse = xhr.responseJSON; // Get JSON error data

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message || "An unknown error occurred.",
                        });
                    }
                }
            });
        });

        function editDaerah(e) {
            daerah_id = e.getAttribute('data-id');
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('daerah.show', '') }}/" + daerah_id,
                type: "GET",
                success: function(response) {
                    $('#daerah_id').val(response.daerah.daerah_uuid);
                    $('#kode_daerah').val(response.daerah.kode_daerah);
                    $('#nama_daerah').val(response.daerah.nama_daerah);
                    $('#latitude').val(response.daerah.latitude);
                    $('#longitude').val(response.daerah.longitude);
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // 422 = Error Validasi Laravel
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('.form-control').on('input', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else {
                        let errorResponse = xhr.responseJSON; // Ambil data JSON error

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });

            $('#daerahModal').modal('show');
            $('#daerahModalLabel').text('Edit Data Daerah');
            $('#saveBtn').text('Ubah');
        }

        function deleteDaerah(e) {
            let daerah_id = e.getAttribute('data-id');

            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Menghapus data secara permanen",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('daerah.destroy', '') }}/" + daerah_id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#daerahTable').DataTable().ajax.reload();
                                Swal.fire({
                                    icon: response.icon,
                                    title: response.title,
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
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
            });

            function daerahDeleteAll() {
                Swal.fire({
                    title: "Apakah anda yakin?",
                    text: "Menghapus semua data secara permanen",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('daerah.destroyAll') }}",
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}", // Kirim token dalam body
                            },
                            success: function(response) {
                                if (response.status == 200) {
                                    $('#daerahTable').DataTable().ajax.reload();
                                    Swal.fire({
                                        icon: response.icon,
                                        title: response.title,
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
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
                });
            }

        }
    </script>
@endsection
