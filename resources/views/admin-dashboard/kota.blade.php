@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Daftar Kota</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-end align-items-center">
            <button class="btn btn-sm btn-primary" onclick="kotaModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="kotaTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama Kota</th>
                            <th>Warna Kota</th>
                            <th>GeoJSON Kota</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kotaModal" tabindex="-1" aria-labelledby="kotaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="kotaModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="kotaForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="kota_id" id="kota_id">
                        <div class="form-group mb-3">
                            <label for="nama_kota" class="form-label">Nama Kota</label>
                            <input type="text" class="form-control" id="nama_kota"
                                placeholder="Masukkan Nama Kota" name="nama_kota"
                                value="{{ old('nama_kota') }}" autofocus required>
                            <div class="invalid-feedback" id="error-nama_kota"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="warna_kota" class="form-label">Warna Kota</label>
                            <div class="d-flex align-items-center">
                                <input type="text" id="warna_kota_text" class="form-control me-2"
                                    value="{{ old('warna_kota', '#000000') }}" readonly>
                                <input type="color" class="form-control form-control-color" id="warna_kota"
                                    name="warna_kota" value="{{ old('warna_kota', '#000000') }}" required
                                    onchange="updateColorCode(this.value)">
                            </div>
                            <div class="invalid-feedback" id="error-warna_kota"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="geojson_kota" class="form-label">GeoJSON Kota</label>
                            <input type="file" class="form-control" id="geojson_kota"
                                placeholder="Masukkan GeoJSON Kota" name="geojson_kota"
                                value="{{ old('geojson_kota') }}" required>
                            <div class="invalid-feedback" id="error-geojson_kota"></div>
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
        let kota_id = null;

        $(document).ready(function() {
            kotaTable();
        });

        function updateColorCode(color) {
            document.getElementById('warna_kota_text').value = color;
        }

        function kotaTable() {
            var table = $('#kotaTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('kota.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama_kota',
                        name: 'nama_kota'
                    },
                    {
                        data: 'warna_kota',
                        name: 'warna_kota',
                        render: function(data, type, row) {
                            return `<div style="background-color:${data}; padding: 10px 30px; display: inline-block;"></div>`;
                        }
                    },
                    {
                        data: 'geojson_kota',
                        name: 'geojson_kota',
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function kotaModal() {
            $('#kotaForm')[0].reset();
            method = 'create';
            kota_id;

            $('#kotaModal').modal('show');
            $('#kotaModalLabel').text('Tambah Data Kota');
            $('#saveBtn').text('Simpan');
        }

        $('#kotaForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('kota.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!kota_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Kota ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('kota.update', '') }}/' + kota_id;
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
                        $('#kotaModal').modal('hide');
                        $('#kotaForm').trigger('reset');

                        $('#kotaTable').DataTable().ajax.reload();

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

        function editKota(e) {
            kota_id = e.getAttribute('data-id');
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('kota.show', '') }}/" + kota_id,
                type: "GET",
                success: function(response) {
                    $('#kota_id').val(response.kota.kota_uuid);
                    $('#nama_kota').val(response.kota.nama_kota);
                    $('#warna_kota').val(response.kota.warna_kota);
                    $('#warna_kota_text').val(response.kota.warna_kota);
                    $('#geojson_kota').attr('placeholder', response.kota.geojson_kota);
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

            $('#kotaModal').modal('show');
            $('#kotaModalLabel').text('Edit Data Kota');
            $('#saveBtn').text('Ubah');
        }

        function deleteKota(e) {
            let kota_id = e.getAttribute('data-id');

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
                        url: "{{ route('kota.destroy', '') }}/" + kota_id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#kotaTable').DataTable().ajax.reload();
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
                                text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
