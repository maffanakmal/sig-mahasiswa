@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Daftar Kelurahan</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-end align-items-center">
            <button class="btn btn-sm btn-primary" onclick="kelurahanModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="kelurahanTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama Kelurahan</th>
                            <th>Warna Kelurahan</th>
                            <th>Koordinat Kelurahan</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kelurahanModal" tabindex="-1" aria-labelledby="kelurahanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="kelurahanModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="kelurahanForm">
                        @csrf
                        <input type="hidden" name="kelurahan_id" id="kelurahan_id">
                        <div class="form-group mb-3">
                            <label for="nama_kelurahan" class="form-label">Nama Kelurahan</label>
                            <input type="text" class="form-control" id="nama_kelurahan"
                                placeholder="Masukkan Nama Kelurahan" name="nama_kelurahan"
                                value="{{ old('nama_kelurahan') }}" autofocus required>
                            <div class="invalid-feedback" id="error-nama_kelurahan"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="warna_kelurahan" class="form-label">Warna Kelurahan</label>
                            <div class="d-flex align-items-center">
                                <input type="text" id="warna_kelurahan_text" class="form-control me-2"
                                    value="{{ old('warna_kelurahan', '#000000') }}" readonly>
                                <input type="color" class="form-control form-control-color" id="warna_kelurahan"
                                    name="warna_kelurahan" value="{{ old('warna_kelurahan', '#000000') }}" required
                                    onchange="updateColorCode(this.value)">
                            </div>
                            <div class="invalid-feedback" id="error-warna_kelurahan"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="geojson_kelurahan" class="form-label">GeoJSON Kelurahan</label>
                            <textarea class="form-control" id="geojson_kelurahan" name="geojson_kelurahan" placeholder="Masukkan GeoJSON Kelurahan"
                                rows="7">{{ old('geojson_kelurahan') }}</textarea>
                            <div class="invalid-feedback" id="error-geojson_kelurahan"></div>
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
        let kelurahan_id = null;

        $(document).ready(function() {
            kelurahanTable();
        });

        function updateColorCode(color) {
            document.getElementById('warna_kelurahan_text').value = color;
        }

        function kelurahanTable() {
            var table = $('#kelurahanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('kelurahan.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama_kelurahan',
                        name: 'nama_kelurahan'
                    },
                    {
                        data: 'warna_kelurahan',
                        name: 'warna_kelurahan',
                        render: function(data, type, row) {
                            return `<div style="background-color:${data}; padding: 10px 30px; display: inline-block;"></div>`;
                        }
                    },
                    {
                        data: 'koordinat_kelurahan',
                        name: 'koordinat_kelurahan',
                        render: function(data, type, row) {
                            if (!data) return 'No Data'; // Handle null values
                            let maxLength = 50; // Set max characters to display
                            return data.length > maxLength ?
                                data.substring(0, maxLength) + '...' :
                                data;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function kelurahanModal() {
            $('#kelurahanForm')[0].reset();
            method = 'create';
            kelurahan_id;

            $('#kelurahanModal').modal('show');
            $('#kelurahanModalLabel').text('Tambah Data Kelurahan');
            $('#saveBtn').text('Simpan');
        }

        $('#kelurahanForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('kelurahan.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!kelurahan_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Kelurahan ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('kelurahan.update', '') }}/' + kelurahan_id;
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
                        $('#kelurahanModal').modal('hide');
                        $('#kelurahanForm').trigger('reset');

                        $('#kelurahanTable').DataTable().ajax.reload();

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

        function editKelurahan(e) {
            kelurahan_id = e.getAttribute('data-id');
            console.log("Kelurahan ID:", kelurahan_id); // pastikan tidak undefined
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('kelurahan.show', '') }}/" + kelurahan_id,
                type: "GET",
                success: function(response) {
                    $('#kelurahan_id').val(response.kelurahan.kelurahan_uuid);
                    $('#nama_kelurahan').val(response.kelurahan.nama_kelurahan);
                    $('#warna_kelurahan').val(response.kelurahan.warna_kelurahan);
                    $('#warna_kelurahan_text').val(response.kelurahan.warna_kelurahan);
                    $('#geojson_kelurahan').val(JSON.stringify(response.kelurahan.geojson_kelurahan, null, 2));
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

            $('#kelurahanModal').modal('show');
            $('#kelurahanModalLabel').text('Edit Data Kelurahan');
            $('#saveBtn').text('Ubah');
        }

        function deleteKelurahan(e) {
            let kelurahan_id = e.getAttribute('data-id');

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
                        url: "{{ route('kelurahan.destroy', '') }}/" + kelurahan_id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#kelurahanTable').DataTable().ajax.reload();
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
