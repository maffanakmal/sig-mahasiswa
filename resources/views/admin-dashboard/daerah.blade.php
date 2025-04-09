@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">GeoJSON Daerah</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-end align-items-center">
            <button class="btn btn-sm btn-primary" onclick="daerahModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="daerahTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama GeoJSON Daerah</th>
                            <th>File GeoJSON Daerah</th>
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
                            <label for="nama_geojson_daerah" class="form-label">Nama GeoJSON Daerah</label>
                            <input type="text" class="form-control" id="nama_geojson_daerah"
                                placeholder="Masukkan Nama Daerah" name="nama_geojson_daerah"
                                value="{{ old('nama_geojson_daerah') }}" autofocus required>
                            <div class="invalid-feedback" id="error-nama_geojson_daerah"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="file_geojson_daerah" class="form-label">File GeoJSON Daerah</label>
                            <input type="file" class="form-control" id="file_geojson_daerah"
                                placeholder="Masukkan GeoJSON Daerah" name="file_geojson_daerah"
                                value="{{ old('file_geojson_daerah') }}" required>
                            <div class="mt-2" id="nama_file_geojson"></div>
                            <div class="invalid-feedback" id="error-file_geojson_daerah"></div>
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
                        data: 'nama_geojson_daerah',
                        name: 'nama_geojson_daerah',
                    },
                    {
                        data: 'file_geojson_daerah',
                        name: 'file_geojson_daerah',
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
                    $('#nama_geojson_daerah').val(response.daerah.nama_geojson_daerah);

                    // ini hanya menampilkan nama file, bukan mengisi input file
                    $('#nama_file_geojson').text("File saat ini: " + response.daerah.file_geojson_daerah.split('/').pop());
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
                                text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
