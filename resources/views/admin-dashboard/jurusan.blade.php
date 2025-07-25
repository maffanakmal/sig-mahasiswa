@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Program Studi</h3>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info!</strong> Untuk mengimpor data program studi, silakan unduh template <a
                href="{{ asset('template/jurusan_template.xlsx') }}" class="text-decoration-none">di sini</a>. Pastikan
            untuk mengisi data sesuai dengan format yang telah ditentukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <p class="mb-2">Import Excel Program Studi</p>
        <form action="#" id="importProdiForm" enctype="multipart/form-data"
            class="d-flex align-items-end gap-2 mb-3">
            @csrf
            <div class="input-group w-50">
                <input type="file" class="form-control" name="import_prodi" id="import_prodi"
                    aria-describedby="btnImport" aria-label="Upload">
                <button class="btn btn-success" type="submit" id="btnImport"><box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon>
                    Import</button>
                <div class="invalid-feedback" id="error-import_prodi"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" onclick="prodiDeleteAll()"><box-icon type="solid" name="trash" class="icon-crud" color="white"></box-icon> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="prodiModal()"><box-icon name="plus" class="icon-crud" color="white"></box-icon> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="prodiTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Kode Program Studi</th>
                            <th>Nama Program Studi</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="prodiModal" tabindex="-1" aria-labelledby="prodiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="prodiModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="prodiForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="prodi_edit" id="prodi_edit">
                        <div class="form-group mb-3">
                            <label for="kode_prodi" class="form-label">Kode Program Studi</label>
                            <input type="text" class="form-control" id="kode_prodi" placeholder="Masukkan Kode Program Studi"
                                name="kode_prodi" value="{{ old('kode_prodi') }}" maxlength="10" required>
                            <div class="invalid-feedback" id="error-kode_prodi"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="nama_prodi" class="form-label">Nama Program Studi</label>
                            <input type="text" class="form-control" id="nama_prodi" placeholder="Masukkan Nama Program Studi"
                                name="nama_prodi" value="{{ old('nama_prodi') }}" maxlength="50" required>
                            <div class="invalid-feedback" id="error-nama_prodi"></div>
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
        let kode_prodi = null;

        $(document).ready(function() {
            prodiTable();
        });

        function prodiTable() {
            var table = $('#prodiTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('prodi.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'kode_prodi',
                        name: 'kode_prodi'
                    },
                    {
                        data: 'nama_prodi',
                        name: 'nama_prodi',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false,
                    }
                ]
            });
        }

        function prodiModal() {
            $('#prodiForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            method = 'create';
            kode_prodi;

            $('#prodiModal').modal('show');
            $('#prodiModalLabel').text('Tambah Data Program Studi');
            $('#saveBtn').text('Simpan');
        }

        $('#prodiForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#saveBtn');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('prodi.store') }}';
            let httpMethod = 'POST';

            if (method === 'update') {
                if (!kode_prodi) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Program Studi tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('prodi.update', '') }}/' + kode_prodi;
                formData.append('_method', 'PUT');
                httpMethod = 'POST';
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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    if (response.status == 200) {
                        $('#prodiModal').modal('hide');
                        $('#prodiForm').trigger('reset');

                        $('#prodiTable').DataTable().ajax.reload();

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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('input, select, textarea').on('input change', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });


                    } else if (xhr.status === 400) {
                        Swal.fire({
                            icon: xhr.responseJSON.icon,
                            title: xhr.responseJSON.title,
                            text: xhr.responseJSON.message
                        });
                        return;
                    
                    } else {
                        let errorResponse = xhr.responseJSON;

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

        $('#importProdiForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btnImport');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('prodi.import') }}';
            let httpMethod = 'POST';

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
                    btn.prop('disabled', false).html('<box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon> Import');

                    if (response.status == 200) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $('#importProdiForm')[0].reset();
                        $('#prodiTable').DataTable().ajax.reload();

                        setTimeout(() => {
                            Swal.fire({
                                icon: response.icon,
                                title: response.title,
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon> Import');

                    if (xhr.status === 422) {
                        let errorResponse = xhr.responseJSON;

                        let allErrors = Object.values(errorResponse.errors).flat().join('\n');

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.message || "Import Gagal",
                            text: allErrors,
                            showConfirmButton: true
                        });

                    } else {
                        let errorResponse = xhr.responseJSON;

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });
        });

        function editProdi(e) {
            kode_prodi = e.getAttribute('data-id');
            method = 'update';

            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let btn = $('#saveBtn');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('prodi.show', '') }}/" + kode_prodi,
                type: "GET",
                success: function(response) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    $('#prodi_edit').val(response.prodi.prodi_uuid);
                    $('#kode_prodi').val(response.prodi.kode_prodi);
                    $('#nama_prodi').val(response.prodi.nama_prodi);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('input, select, textarea').on('input change', function() {
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

            $('#prodiModal').modal('show');
            $('#prodiModalLabel').text('Edit Data Program Studi');
            $('#saveBtn').text('Ubah');
        }

        function deleteProdi(e) {
            let kode_prodi = e.getAttribute('data-id');

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
                        url: "{{ route('prodi.destroy', '') }}/" + kode_prodi,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#prodiTable').DataTable().ajax.reload();
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

        function prodiDeleteAll() {
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
                        url: "{{ route('prodi.destroyAll') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                $('#prodiTable').DataTable().ajax.reload();
                                Swal.fire({
                                    icon: response.icon,
                                    title: response.title,
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    icon: response.icon || "info",
                                    title: response.title || "Info",
                                    text: response.message || "Tidak ada data untuk dihapus.",
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorResponse = xhr.responseJSON || {};

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
    </script>
@endsection
