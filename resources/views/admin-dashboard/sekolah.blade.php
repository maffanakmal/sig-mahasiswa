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
                @if ($jumlahTidakLengkap > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Terdapat <span class="fw-bold">{{ $jumlahTidakLengkap }}</span> data sekolah yang belum memiliki data
                daerah yang lengkap. ketik <span class="fw-bold">Tidak ada</span> di kolom
                pencarian untuk melihatnya.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <p class="mb-2">Import Excel Sekolah</p>
        <form action="#" id="importSekolahForm" enctype="multipart/form-data" class="d-flex align-items-end gap-2 mb-3">
            @csrf
            <div class="input-group w-50">
                <input type="file" class="form-control" name="import_sekolah" id="import_sekolah"
                    aria-describedby="btnImport" aria-label="Upload">
                <button class="btn btn-success" type="submit" id="btnImport"><box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon>
                    Import</button>
                <div class="invalid-feedback" id="error-import_sekolah"></div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-danger" onclick="sekolahDeleteAll()"><box-icon type="solid" name="trash" class="icon-crud" color="white"></box-icon> Hapus</button>
            <button class="btn btn-sm btn-primary" onclick="sekolahModal()"><box-icon name="plus" class="icon-crud" color="white"></box-icon> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="sekolahTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>NPSN</th>
                            <th>Nama Sekolah</th>
                            <th>Alamat Sekolah</th>
                            <th>Daerah Sekolah</th>
                            <th>Latitude Sekolah</th>
                            <th>Longitude Sekolah</th>
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
                                <input type="hidden" name="npsn_edit" id="npsn_edit">
                                <div class="form-group mb-3">
                                    <label for="npsn" class="form-label">Nomor Pokok Sekolah Nasional</label>
                                    <input type="text" class="form-control" id="npsn"
                                        placeholder="Masukkan NPSN" name="npsn"
                                        value="{{ old('npsn') }}" maxlength="10" required>
                                    <div class="invalid-feedback" id="error-npsn"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="nama_sekolah"
                                        placeholder="Masukkan Nama Sekolah" name="nama_sekolah"
                                        value="{{ old('nama_sekolah') }}" maxlength="100" required>
                                    <div class="invalid-feedback" id="error-nama_sekolah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="alamat_sekolah" class="form-label">Alamat Sekolah</label>
                                    <textarea  class="form-control" name="alamat_sekolah" id="alamat_sekolah" cols="10" rows="5" placeholder="Masukkan Alamat Sekolah"  value="{{ old('alamat_sekolah') }}" required maxlength="1000"></textarea>
                                    <div class="invalid-feedback" id="error-alamat_sekolah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="kode_daerah" class="form-label">Daerah Sekolah</label>
                                    <select id="kode_daerah" name="kode_daerah" class="form-control select-daerah" required>
                                        <option value="" selected disabled>Pilih Daerah Sekolah</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="latitude_sekolah" class="form-label">Latitude Sekolah</label>
                                    <input type="text" class="form-control" id="latitude_sekolah"
                                        placeholder="Masukkan Latitude Sekolah" name="latitude_sekolah" value="{{ old('latitude_sekolah') }}"
                                        required>
                                    <div class="invalid-feedback" id="error-latitude_sekolah"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="longitude_sekolah" class="form-label">Longitude Sekolah</label>
                                    <input type="text" class="form-control" id="longitude_sekolah"
                                        placeholder="Masukkan Longitude Sekolah" name="longitude_sekolah" value="{{ old('longitude_sekolah') }}"
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

                    L.Control.geocoder({
                            defaultMarkGeocode: false,
                            placeholder: 'Jalan/Kelurahan/Kecamatan',
                        })
                        .on('markgeocode', function(e) {
                            var latlng = e.geocode.center;
                            // var namaLokasi = e.geocode.name;

                            map.setView(latlng, 14);
                            marker.setLatLng(latlng);

                            document.getElementById('latitude_sekolah').value = latlng.lat;
                            document.getElementById('longitude_sekolah').value = latlng.lng;
                            // document.getElementById('alamat_sekolah').value = namaLokasi;
                        })
                        .addTo(map);

                    marker = L.marker(defaultCenter, {
                        draggable: true
                    });

                    marker.on('dragend', function(e) {
                        var latlng = marker.getLatLng();
                        document.getElementById('latitude_sekolah').value = latlng.lat;
                        document.getElementById('longitude_sekolah').value = latlng.lng;
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
                    document.getElementById('latitude_sekolah').value = '';
                    document.getElementById('longitude_sekolah').value = '';
                    marker.setLatLng(defaultCenter);
                    map.setView(defaultCenter, defaultZoom);
                }
            });

            sekolahModal.addEventListener('hidden.bs.modal', function() {
                // Reset pendingLatLng dan pendingZoom saat modal ditutup
                pendingLatLng = null;
                pendingZoom = defaultZoom;
            });

            sekolahTable();
            showSelect();
            selectDaerahSekolah();
        });

        let method;
        let npsn = null;

        function sekolahTable() {
            var table = $('#sekolahTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('sekolah.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'npsn',
                        name: 'npsn'
                    },
                    {
                        data: 'nama_sekolah',
                        name: 'nama_sekolah'
                    },
                    {
                        data: 'alamat_sekolah',
                        name: 'alamat_sekolah'
                    },
                    {
                        data: 'kode_daerah',
                        name: 'kode_daerah',
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
                        name: 'action',
                        searchable: false,
                        orderable: false,
                    }
                ]
            });
        }

        function sekolahModal() {
            $('#sekolahForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            method = 'create';
            npsn;

            $('#sekolahModal').modal('show');
            $('#sekolahModalLabel').text('Tambah Data Sekolah');
            $('#saveBtn').text('Simpan');
        }

        function selectDaerahSekolah() {
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
                            $('#kode_daerah').append(
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

            let btn = $('#saveBtn');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('sekolah.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!npsn) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Sekolah ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('sekolah.update', '') }}/' + npsn;
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

        $('#importSekolahForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btnImport');
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

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
                    btn.prop('disabled', false).html('<box-icon type="solid" name="spreadsheet" class="icon-crud" color="white"></box-icon> Import');

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

        function editSekolah(e) {
            npsn = e.getAttribute('data-id');
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
                url: "{{ route('sekolah.show', '') }}/" + npsn,
                type: "GET",
                success: function(response) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    $('#npsn').val(response.sekolah.npsn);
                    $('#nama_sekolah').val(response.sekolah.nama_sekolah);
                    $('#alamat_sekolah').val(response.sekolah.alamat_sekolah);
                    $('#kode_daerah').val(response.sekolah.kode_daerah);
                    $('#latitude_sekolah').val(response.sekolah.latitude_sekolah);
                    $('#longitude_sekolah').val(response.sekolah.longitude_sekolah);

                    
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
