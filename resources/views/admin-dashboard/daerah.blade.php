@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Daerah</h3>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info!</strong> Untuk mengimport data daerah, silakan unduh template <a
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
                <button class="btn btn-success" type="submit" id="btnImport"><box-icon type="solid" name="spreadsheet"
                        class="icon-crud" color="white"></box-icon>
                    Import</button>
                <div class="invalid-feedback" id="error-import_daerah"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" id="btnDeleteSelected" onclick="daerahSelectedDelete()"><box-icon
                    type="solid" name="trash" class="icon-crud" color="white"></box-icon> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="daerahModal()"><box-icon name="plus" class="icon-crud"
                    color="white"></box-icon> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="daerahTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Kode Daerah</th>
                            <th>Nama Daerah</th>
                            <th>Latitude Daerah</th>
                            <th>Longitude Daerah</th>
                            <th class="th-aksi text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    Action
                                    <input type="checkbox" id="checkAll" class="form-check-input" title="Pilih semua">
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="daerahModal" tabindex="-1" aria-labelledby="daerahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="daerahModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Kolom Peta -->
                        <div class="col-md-8 mb-3">
                            <div id="mapInput" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                        </div>
                        <!-- Kolom Form -->
                        <div class="col-md-4">
                            <form action="#" id="daerahForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="kode_daerah_edit" id="kode_daerah_edit">
                                <div class="form-group mb-3">
                                    <label for="kode_daerah" class="form-label">Kode Daerah</label>
                                    <input type="text" class="form-control" id="kode_daerah"
                                        placeholder="Masukkan Kode Daerah" name="kode_daerah"
                                        value="{{ old('kode_daerah') }}" required>
                                    <div class="invalid-feedback" id="error-kode_daerah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nama_daerah" class="form-label">Nama Daerah</label>
                                    <input type="text" class="form-control" id="nama_daerah"
                                        placeholder="Masukkan Nama Daerah" name="nama_daerah"
                                        value="{{ old('nama_daerah') }}" required>
                                    <div class="invalid-feedback" id="error-nama_daerah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="latitude_daerah" class="form-label">Latitude Daerah</label>
                                    <input type="text" class="form-control" id="latitude_daerah"
                                        placeholder="Masukkan Latitude" name="latitude_daerah"
                                        value="{{ old('latitude_daerah') }}" required>
                                    <div class="invalid-feedback" id="error-latitude_daerah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="longitude_daerah" class="form-label">Longitude Daerah</label>
                                    <input type="text" class="form-control" id="longitude_daerah"
                                        placeholder="Masukkan Longitude" name="longitude_daerah"
                                        value="{{ old('longitude_daerah') }}" required>
                                    <div class="invalid-feedback" id="error-longitude_daerah"></div>
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

            var daerahModal = document.getElementById('daerahModal');
            daerahModal.addEventListener('shown.bs.modal', function() {
                if (!map) {
                    map = L.map('mapInput').setView(defaultCenter, defaultZoom);

                    // Inisialisasi beberapa layer basemap
                    var baseLayer_OSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    var baseLayer_ESRI = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri'
                    }); // ini default

                    // Tambahkan control switch basemap
                    var baseMaps = {
                        "ESRI World Imagery": baseLayer_ESRI,
                        "OpenStreetMap": baseLayer_OSM,
                    };

                    L.control.layers(baseMaps).addTo(map);

                    L.Control.geocoder({
                            defaultMarkGeocode: false,
                            placeholder: 'Kabupaten/Kota',
                        })
                        .on('markgeocode', function(e) {
                            var latlng = e.geocode.center;
                            var fullAddress = e.geocode.name;
                            var addressParts = fullAddress.split(',');

                            // Ambil bagian pertama sebagai nama kota/kabupaten
                            var namaKotaKabupaten = addressParts[0].trim();

                            map.setView(latlng, 14);
                            marker.setLatLng(latlng);

                            document.getElementById('latitude_daerah').value = latlng.lat;
                            document.getElementById('longitude_daerah').value = latlng.lng;
                            document.getElementById('nama_daerah').value = namaKotaKabupaten;
                        })

                        .addTo(map);

                    marker = L.marker(defaultCenter, {
                        draggable: true
                    });

                    marker.on('dragend', function(e) {
                        var latlng = marker.getLatLng();
                        document.getElementById('latitude_daerah').value = latlng.lat;
                        document.getElementById('longitude_daerah').value = latlng.lng;
                    });

                    if (!map.hasLayer(marker)) {
                        map.addLayer(marker);
                    }

                    // Tombol reset posisi marker
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
                        };
                        return div;
                    };
                    resetControl.addTo(map);
                } else {
                    map.invalidateSize();
                }

                // Tampilkan marker jika belum
                if (!map.hasLayer(marker)) {
                    map.addLayer(marker);
                }

                if (method === 'create') {
                    document.getElementById('latitude_daerah').value = '';
                    document.getElementById('longitude_daerah').value = '';
                    marker.setLatLng(defaultCenter);
                    map.setView(defaultCenter, defaultZoom);
                }
            });

            daerahModal.addEventListener('hidden.bs.modal', function() {
                // Reset pendingLatLng dan pendingZoom saat modal ditutup
                pendingLatLng = null;
                pendingZoom = defaultZoom;
            });


            daerahTable();
        });

        let method;
        let kode_daerah = null;

        function daerahTable() {
            var table = $('#daerahTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('daerah.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                        data: 'latitude_daerah',
                        name: 'latitude_daerah',
                    },
                    {
                        data: 'longitude_daerah',
                        name: 'longitude_daerah',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }

        function daerahModal() {
            $('#daerahForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            method = 'create';
            kode_daerah;

            $('#daerahModal').modal('show');
            $('#daerahModalLabel').text('Tambah Data Daerah');
            $('#saveBtn').text('Simpan');
        }

        $('#daerahForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#saveBtn');

            btn.prop('disabled', false).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('daerah.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!kode_daerah) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Daerah ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('daerah.update', '') }}/' + kode_daerah;
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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

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

        $('#importDaerahForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btnImport');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

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
                    btn.prop('disabled', false).html(
                        '<box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon> Import'
                    );

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    if (response.status == 200) {

                        $('#importDaerahForm')[0].reset();
                        $('#importDaerahForm').find('input[type="file"]').val(null);
                        $('#daerahTable').DataTable().ajax.reload();

                        // Setelah sedikit delay agar spinner sempat terlihat
                        setTimeout(() => {
                            Swal.fire({
                                icon: response.icon,
                                title: response.title,
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        }, 1000); // delay 0.8 detik sebelum tampil hasil
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(
                        '<box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon> Import'
                    );

                    $('#importDaerahForm')[0].reset();
                    $('#importDaerahForm').find('input[type="file"]').val(null);

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

        function editDaerah(e) {
            kode_daerah = e.getAttribute('data-id');
            method = 'update';

            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let btn = $('#saveBtn');
            btn.prop('disabled', false).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('daerah.show', '') }}/" + kode_daerah,
                type: "GET",
                success: function(response) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');
                    
                    $('#kode_daerah_edit').val(response.daerah.daerah_uuid);
                    $('#kode_daerah').val(response.daerah.kode_daerah);
                    $('#nama_daerah').val(response.daerah.nama_daerah);
                    $('#latitude_daerah').val(response.daerah.latitude_daerah);
                    $('#longitude_daerah').val(response.daerah.longitude_daerah);
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
                        let errorResponse = xhr.responseJSON;

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


        $('#checkAll').on('change', function() {
            $('.delete-checkbox').prop('checked', this.checked);
        });

        function daerahSelectedDelete() {
            const selectedIds = [];
            $('.delete-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tidak ada data yang dipilih.',
                });
                return;
            }

            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Data yang dipilih akan dihapus secara permanen.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('daerah.destroySelected') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: selectedIds
                        },
                        success: function(response) {
                            $('#daerahTable').DataTable().ajax.reload();
                            Swal.fire({
                                icon: response.icon,
                                title: response.title,
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                $('#checkAll').prop('checked', false);
                                $('.delete-checkbox').prop('checked', false);
                                $('#daerahTable').DataTable().ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal",
                                text: "Terjadi kesalahan saat menghapus data.",
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
