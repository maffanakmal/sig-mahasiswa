@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Sekolah</h3>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info!</strong> Untuk mengimpor data sekolah, silakan unduh template <a
                href="{{ asset('template/sekolah_template.xlsx') }}" class="text-decoration-none">di sini</a>. Pastikan
            untuk mengisi data sesuai dengan format yang telah ditentukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <p class="mb-2">Import Excel Sekolah</p>
        <form action="#" id="importSekolahForm" enctype="multipart/form-data" class="d-flex align-items-end gap-2 mb-3">
            @csrf
            <div class="input-group w-50">
                <input type="file" class="form-control" name="import_sekolah" id="import_sekolah"
                    aria-describedby="btnImport" aria-label="Upload">
                <button class="btn btn-success" type="submit" id="btnImport"><i class='bx bx-spreadsheet'></i>
                    Import</button>
                <div class="invalid-feedback" id="error-import_sekolah"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" onclick="sekolahDeleteAll()"><i class='bx bx-trash'></i> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="sekolahModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="sekolahTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama Sekolah</th>
                            <th>Daerah Sekolah</th>
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

    <div class="modal fade" id="sekolahModal" tabindex="-1" aria-labelledby="sekolahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="sekolahModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="mapInput" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                        </div>
                        <div class="col-md-4">
                            <form action="#" id="sekolahForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="sekolah_id" id="sekolah_id">
                                <div class="form-group mb-3">
                                    <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="nama_sekolah"
                                        placeholder="Masukkan Nama Sekolah" name="nama_sekolah"
                                        value="{{ old('nama_sekolah') }}" required>
                                    <div class="invalid-feedback" id="error-nama_sekolah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="daerah_sekolah" class="form-label">Daerah Sekolah</label>
                                    <select id="daerah_sekolah" name="daerah_sekolah" class="form-control select-daerah">
                                        <option value="" selected disabled>Pilih Daerah Sekolah</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="latitude_sekolah" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude_sekolah"
                                        placeholder="Masukkan Latitude" name="latitude_sekolah" value="{{ old('latitude_sekolah') }}"
                                        required>
                                    <div class="invalid-feedback" id="error-latitude_sekolah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="longitude_sekolah" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude_sekolah"
                                        placeholder="Masukkan Longitude" name="longitude_sekolah" value="{{ old('longitude_sekolah') }}"
                                        required>
                                    <div class="invalid-feedback" id="error-longitude_sekolah"></div>
                                </div>
                                <div class="modal-footer px-0">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">Simpan</button>
                                </div>
                            </form>
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
            var defaultCenter = [-2.5, 118]; // Koordinat tengah Indonesia
            var defaultZoom = 5;
            var map;
            var marker;

            var sekolahModal = document.getElementById('sekolahModal');
            sekolahModal.addEventListener('shown.bs.modal', function() {
                if (!map) {
                    map = L.map('mapInput').setView(defaultCenter, defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    // Tambah marker draggable
                    marker = L.marker(defaultCenter, {
                        draggable: true
                    }).addTo(map);

                    // Update input ketika marker digeser
                    marker.on('dragend', function(e) {
                        var latlng = marker.getLatLng();
                        document.getElementById('latitude_sekolah').value = latlng.lat;
                        document.getElementById('longitude_sekolah').value = latlng.lng;
                    });

                    // Set nilai awal input
                    document.getElementById('latitude_sekolah').value = defaultCenter[0];
                    document.getElementById('longitude_sekolah').value = defaultCenter[1];

                    // Kontrol tombol reset
                    var resetControl = L.control({
                        position: 'topleft'
                    });
                    resetControl.onAdd = function(map) {
                        var div = L.DomUtil.create('div',
                            'leaflet-bar leaflet-control leaflet-control-custom');
                        div.innerHTML =
                            '<button title="Reset View" style="background-color:white; border:none; width:28px; height:28px; font-size:18px;">📍</button>';
                        div.onclick = function() {
                            map.setView(defaultCenter, defaultZoom);
                            marker.setLatLng(defaultCenter);

                            // Update input juga
                            document.getElementById('latitude_sekolah').value = defaultCenter[0];
                            document.getElementById('longitude_sekolah').value = defaultCenter[1];
                        };
                        return div;
                    };
                    resetControl.addTo(map);

                } else {
                    map.invalidateSize();
                }
            });

            sekolahTable();
            showSelect();
            selectDaerahSekolah();
        });

        let method;
        let sekolah_id = null;

        function sekolahTable() {
            var table = $('#sekolahTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('sekolah.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama_sekolah',
                        name: 'nama_sekolah'
                    },
                    {
                        data: 'daerah_sekolah',
                        name: 'daerah_sekolah',
                    },
                    {
                        data: 'latitude_sekolah',
                        name: 'latitude_sekolah',
                    },
                    {
                        data: 'longitude_sekolah',
                        name: 'longitude_sekolah',
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function sekolahModal() {
            $('#sekolahForm')[0].reset();
            method = 'create';
            sekolah_id;

            $('#sekolahModal').modal('show');
            $('#sekolahModalLabel').text('Tambah Data Sekolah');
            $('#saveBtn').text('Simpan');
        }

        function selectDaerahSekolah() {
            $('#daerah_sekolah').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#daerah_sekolah').parent(), // Ensures proper z-index handling
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            }).on('select2:open', function() {
                // Focus the search field when opened
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 0);
            });
        }

        function showSelect() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('sekolah.create') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status == 200) {

                        response.daerah.forEach(function(item) {
                            $('#daerah_sekolah').append(
                                `<option value="${item.kode_daerah}">${item.nama_daerah}</option>`);
                        });

                        selectDaerahSekolah();
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

        $('#sekolahForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('sekolah.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!sekolah_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Sekolah ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('sekolah.update', '') }}/' + sekolah_id;
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
                        $('#sekolahModal').modal('hide');
                        $('#sekolahForm').trigger('reset');

                        $('#sekolahTable').DataTable().ajax.reload();

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

        $('#importSekolahForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('sekolah.import') }}';
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

                        $('#importSekolahForm')[0].reset();
                        $('#sekolahTable').DataTable().ajax.reload();

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

        function editSekolah(e) {
            sekolah_id = e.getAttribute('data-id');
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('sekolah.show', '') }}/" + sekolah_id,
                type: "GET",
                success: function(response) {
                    $('#sekolah_id').val(response.sekolah.sekolah_uuid);
                    $('#nama_sekolah').val(response.sekolah.nama_sekolah);
                    $('#daerah_sekolah').val(response.sekolah.daerah_sekolah);
                    $('#latitude_sekolah').val(response.sekolah.latitude_sekolah);
                    $('#longitude_sekolah').val(response.sekolah.longitude_sekolah);

                    let lat = response.sekolah.latitude_sekolah;
                    let lng = response.sekolah.longitude_sekolah;

                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    }

                    if (map) {
                        map.setView([lat, lng], 12); // bisa sesuaikan zoom level
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
                            text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });

            $('#sekolahModal').modal('show');
            $('#sekolahModalLabel').text('Edit Data Sekolah');
            $('#saveBtn').text('Ubah');
        }

        function deleteSekolah(e) {
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
                        url: "{{ route('sekolah.destroy', '') }}/" + nim,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#sekolahTable').DataTable().ajax.reload();
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

        function sekolahDeleteAll() {
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
                        url: "{{ route('sekolah.destroyAll') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                $('#sekolahTable').DataTable().ajax.reload();
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
