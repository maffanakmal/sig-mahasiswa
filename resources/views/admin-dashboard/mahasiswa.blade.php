@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Mahasiswa</h3>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info!</strong> Untuk mengimpor data mahasiswa, silakan unduh template <a
                href="{{ asset('template/mahasiswa_template.xlsx') }}" class="text-decoration-none">di sini</a>. Pastikan
            untuk mengisi data sesuai dengan format yang telah ditentukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <p class="mb-2">Import Excel Mahasiswa</p>
        <form action="#" id="importMahasiswaForm" enctype="multipart/form-data"
            class="d-flex align-items-end gap-2 mb-3">
            @csrf
            <div class="input-group w-50">
                <input type="file" class="form-control" name="import_mahasiswa" id="import_mahasiswa"
                    aria-describedby="btnImport" aria-label="Upload">
                <button class="btn btn-success" type="submit" id="btnImport"><i class='bx bx-spreadsheet'></i>
                    Import</button>
                <div class="invalid-feedback" id="error-import_mahasiswa"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" onclick="mahasiswaDeleteAll()"><i class='bx bx-trash'></i> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="mahasiswaModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mahasiswaTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>NIM</th>
                            <th>Tahun Masuk</th>
                            <th>Program Studi</th>
                            <th>Sekolah Asal</th>
                            <th>Daerah Asal</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <p><strong>Note:</strong> Ketik <strong>"Tidak ada"</strong> di kolom search untuk melihat mahasiswa yang belum lengkap datanya.</p>
    </div>

    <div class="modal fade" id="mahasiswaModal" tabindex="-1" aria-labelledby="mahasiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mahasiswaModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="mahasiswaForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="nim_edit" id="nim_edit">
                        <div class="form-group mb-3">
                            <label for="nim" class="form-label">Nomor Induk Mahasiswa</label>
                            <input type="text" class="form-control" id="nim" placeholder="Masukkan NIM"
                                name="nim" value="{{ old('nim') }}" maxlength="10" required>
                            <div class="invalid-feedback" id="error-nim"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                            <input type="text" class="form-control" id="tahun_masuk" placeholder="Masukkan Tahun Masuk"
                                name="tahun_masuk" value="{{ old('tahun_masuk') }}" maxlength="4" required>
                            <div class="invalid-feedback" id="error-tahun_masuk"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kode_prodi" class="form-label">Program Studi</label>
                            <select id="kode_prodi" name="kode_prodi" class="form-control select-kode_prodi" required>
                                <option value="" selected disabled>Pilih Program Studi</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="npsn" class="form-label">Sekolah Asal</label>
                            <select id="npsn" name="npsn" class="form-control select-npsn" required>
                                <option value="" selected disabled>Pilih Sekolah Asal</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kode_daerah" class="form-label">Daerah Asal</label>
                            <select id="kode_daerah" name="kode_daerah" class="form-control select-kode_daerah" required>
                                <option value="" selected disabled>Pilih Daerah Asal</option>
                            </select>
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
        let nim = null;

        $(document).ready(function() {
            mahasiswaTable();
            showSelect();
            selectDaerahAsal();
            selectSekolahAsal();
            selectProdi();
        });

        function mahasiswaTable() {
            var table = $('#mahasiswaTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('mahasiswa.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nim',
                        name: 'nim',
                    },
                    {
                        data: 'tahun_masuk',
                        name: 'tahun_masuk',
                    },
                    {
                        data: 'kode_prodi',
                        name: 'kode_prodi',
                    },
                    {
                        data: 'npsn',
                        name: 'npsn',
                    },
                    {
                        data: 'kode_daerah',
                        name: 'kode_daerah',
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function mahasiswaModal() {
            $('#mahasiswaForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            method = 'create';
            nim;

            $('#mahasiswaModal').modal('show');
            $('#mahasiswaModalLabel').text('Tambah Data Mahasiswa');
            $('#saveBtn').text('Simpan');
        }

        function selectDaerahAsal() {
            $('#kode_daerah').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#kode_daerah').parent(), // Ensures proper z-index handling
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            })
        }

        function selectSekolahAsal() {
            $('#npsn').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#npsn').parent(), // Ensures proper z-index handling
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            })
        }

        function selectProdi() {
            $('#kode_prodi').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#kode_prodi').parent(), // Ensures proper z-index handling
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            })
        }

        function showSelect() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('mahasiswa.create') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status == 200) {

                        response.daerah.forEach(function(item) {
                            $('#kode_daerah').append(
                                `<option value="${item.kode_daerah}">${item.nama_daerah}</option>`);
                        });

                        response.sekolah.forEach(function(item) {
                            $('#npsn').append(
                                `<option value="${item.npsn}">${item.nama_sekolah}</option>`);
                        });

                        response.prodi.forEach(function(item) {
                            $('#kode_prodi').append(
                                `<option value="${item.kode_prodi}">${item.nama_prodi}</option>`);
                        });

                        selectDaerahAsal();
                        selectSekolahAsal();
                        selectProdi();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 500) {
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
        }

        $('#mahasiswaForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#saveBtn');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('mahasiswa.store') }}';
            let httpMethod = 'POST';

            if (method === 'update') {
                if (!nim) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Mahasiswa ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('mahasiswa.update', '') }}/' + nim;
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
                    btn.prop('disabled', false).html('Simpan');

                    if (response.status == 200) {
                        $('#mahasiswaModal').modal('hide');
                        $('#mahasiswaForm').trigger('reset');

                        $('#mahasiswaTable').DataTable().ajax.reload();

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
                    btn.prop('disabled', false).html('Simpan');

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

        $('#importMahasiswaForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('mahasiswa.import') }}';
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
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $('#importMahasiswaForm')[0].reset();
                        $('#mahasiswaTable').DataTable().ajax.reload();

                        // Setelah sedikit delay agar spinner sempat terlihat
                        setTimeout(() => {
                            Swal.fire({
                                icon: response.icon,
                                title: response.title,
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }, 1000); // delay 0.8 detik sebelum tampil hasil
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // 422 = Validation Error
                        let errorResponse = xhr.responseJSON;

                        let allErrors = Object.values(errorResponse.errors).flat().join('\n');

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.message || "Import Gagal",
                            text: allErrors,
                            showConfirmButton: true
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

        function editMahasiswa(e) {
            nim = e.getAttribute('data-id');
            method = 'update';

            let btn = $('#saveBtn');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('mahasiswa.show', '') }}/" + nim,
                type: "GET",
                success: function(response) {
                    btn.prop('disabled', false).html('Ubah');

                    $('#nim_edit').val(response.mahasiswa.mahasiswa_uuid);
                    $('#nim').val(response.mahasiswa.nim);
                    $('#tahun_masuk').val(response.mahasiswa.tahun_masuk);
                    $('#kode_prodi').val(response.mahasiswa.kode_prodi);
                    $('#npsn').val(response.mahasiswa.npsn);
                    $('#kode_daerah').val(response.mahasiswa.kode_daerah);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Ubah');

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

            $('#mahasiswaModal').modal('show');
            $('#mahasiswaModalLabel').text('Edit Data Mahasiswa');
            $('#saveBtn').text('Ubah');
        }

        function deleteMahasiswa(e) {
            let nim = e.getAttribute('data-id');

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
                        url: "{{ route('mahasiswa.destroy', '') }}/" + nim,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#mahasiswaTable').DataTable().ajax.reload();
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

        function mahasiswaDeleteAll() {
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
                        url: "{{ route('mahasiswa.destroyAll') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                $('#mahasiswaTable').DataTable().ajax.reload();
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
