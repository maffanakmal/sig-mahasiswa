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
                            <th>Jurusan</th>
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
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" placeholder="Masukkan NIM"
                                name="nim" value="{{ old('nim') }}" required>
                            <div class="invalid-feedback" id="error-nim"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                            <input type="text" class="form-control" id="tahun_masuk" placeholder="Masukkan Tahun Masuk"
                                name="tahun_masuk" value="{{ old('tahun_masuk') }}" required>
                            <div class="invalid-feedback" id="error-tahun_masuk"></div>
                        </div>
                        <div class="form-group mb-3">
                            <select id="jurusan" name="jurusan" class="form-control select-jurusan">
                                <option value="" selected disabled>Program Studi</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <select id="sekolah_asal" name="sekolah_asal" class="form-control select-sekolah">
                                <option value="" selected disabled>Sekolah Asal</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <select id="daerah_asal" name="daerah_asal" class="form-control select-daerah">
                                <option value="" selected disabled>Daerah Asal</option>
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
            selectJurusan();
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
                        data: 'jurusan',
                        name: 'jurusan',
                    },
                    {
                        data: 'sekolah_asal',
                        name: 'sekolah_asal',
                    },
                    {
                        data: 'daerah_asal',
                        name: 'daerah_asal',
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
            method = 'create';
            nim;

            $('#mahasiswaModal').modal('show');
            $('#mahasiswaModalLabel').text('Tambah Data Mahasiswa');
            $('#saveBtn').text('Simpan');
        }

        function selectDaerahAsal() {
            $('#daerah_asal').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#daerah_asal').parent(), // Ensures proper z-index handling
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
            $('#sekolah_asal').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#sekolah_asal').parent(), // Ensures proper z-index handling
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

        function selectJurusan() {
            $('#jurusan').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#jurusan').parent(), // Ensures proper z-index handling
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
                            $('#daerah_asal').append(
                                `<option value="${item.kode_daerah}">${item.nama_daerah}</option>`);
                        });

                        response.sekolah.forEach(function(item) {
                            $('#sekolah_asal').append(
                                `<option value="${item.sekolah_id}">${item.nama_sekolah}</option>`);
                        });

                        response.jurusan.forEach(function(item) {
                            $('#jurusan').append(
                                `<option value="${item.kode_jurusan}">${item.nama_jurusan}</option>`);
                        });

                        selectDaerahAsal(); // Initialize select2 after appending options
                        selectSekolahAsal(); // Initialize select2 after appending options
                        selectJurusan(); // Initialize select2 after appending options
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 500) {
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
        }

        $('#mahasiswaForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('mahasiswa.store') }}';
            let httpMethod = 'POST'; // Default method for create

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

        function editMahasiswa(e) {
            nim = e.getAttribute('data-id');
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('mahasiswa.show', '') }}/" + nim,
                type: "GET",
                success: function(response) {
                    $('#nim').val(response.mahasiswa.mahasiswa_uuid);
                    $('#nim').val(response.mahasiswa.nim);
                    $('#tahun_masuk').val(response.mahasiswa.tahun_masuk);
                    $('#jurusan').val(response.mahasiswa.jurusan);
                    $('#sekolah_asal').val(response.mahasiswa.sekolah_asal);
                    $('#daerah_asal').val(response.mahasiswa.daerah_asal);
                    $('#status_mahasiswa').val(response.mahasiswa.status_mahasiswa);
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
                                // Untuk response custom selain 200 (misal 404 dari backend)
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
