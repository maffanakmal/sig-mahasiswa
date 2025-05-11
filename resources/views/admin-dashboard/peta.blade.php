@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Peta Sebaran Mahasiswa</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header mb-2">
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                <strong>Perhatian!</strong> Nama daerah <strong id="cityValidate"></strong> tidak ditemukan di GeoJSON
                <a href="{{ route('mahasiswa.index') }}" class="alert-link">Klik disini</a> untuk menambah data daerah.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <p class="mb-2">Filter Data</p>
            <form action="#" id="mapFilterForm" class="d-flex align-items-end gap-2 flex-wrap">
                @csrf

                <div class="form-group" style="min-width: 180px;">
                    <select id="tahun_masuk" name="tahun_masuk" class="form-control">
                        <option value="" selected disabled>Tahun Masuk</option>
                    </select>
                </div>

                <div class="form-group" style="min-width: 180px;">
                    <select id="jurusan" name="jurusan" class="form-control">
                        <option value="" selected disabled>Jurusan</option>
                    </select>
                </div>

                <div class="form-group" style="min-width: 180px;">
                    <select id="daerah_asal" name="daerah_asal" class="form-control">
                        <option value="" selected disabled>Daerah Asal</option>
                    </select>
                </div>

                <div class="form-group" style="min-width: 180px;">
                    <select id="sekolah_asal" name="sekolah_asal" class="form-control">
                        <option value="" selected disabled>Sekolah Asal</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-search'></i> Cari</button>
                </div>

                <div>
                    <button type="button" id="resetFilterBtn" class="btn btn-danger">Reset</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <div id="map" class="mb-3"></div>
                <p>Jumlah mahasiswa yang tampil: <span>0</span></p>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
    <script>
        // Buat koordinat awal dan zoom default
        var defaultCenter = [-2.3, 121]; // contoh koordinat
        var defaultZoom = 5;

        // Inisialisasi peta
        var map = L.map('map').setView(defaultCenter, defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Tambahkan kontrol reset
        var resetControl = L.control({
            position: 'topleft'
        });

        resetControl.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
            div.innerHTML =
                '<button title="Reset View" style="background-color:white; border:none; width:28px; height:28px; font-size:18px;">📍</button>';

            div.onclick = function() {
                map.setView(defaultCenter, defaultZoom);
            };

            return div;
        };

        resetControl.addTo(map);

        // Hapus legenda lama jika ada
        if (window.legendControl) {
            map.removeControl(window.legendControl);
        }

        // Buat legenda baru
        const legend = L.control({
            position: 'bottomright'
        });

        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend');
            const grades = [0, 5, 10, 15, 20];
            const colors = [
                "#FF9F00",
                "#522546",
                "#004225",
                "#003366",
                "#800026"
            ];

            div.innerHTML += "<h4>Jumlah Mahasiswa</h4>";
            for (let i = 0; i < grades.length; i++) {
                div.innerHTML +=
                    `<i style="background:${colors[i]}; width:18px; height:18px; display:inline-block; margin-right:6px;"></i> ` +
                    `${grades[i]}${(grades[i + 1]) ? '&ndash;' + grades[i + 1] + '<br>' : '+'}`;
            }

            return div;
        };

        // Tambahkan ke peta
        legend.addTo(map);

        // Simpan referensi agar bisa dihapus nanti
        window.legendControl = legend;


        $(document).ready(function() {
            showAllMahasiswa();
            mapFilter();
        });

        function showAllMahasiswa() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('dashboard.petaDaerah.show') }}",
                type: "GET",
                success: function(response) {
                    if (response.status == 200) {
                        const mahasiswa = response.mahasiswa;

                        // Hapus marker lama
                        map.eachLayer(function(layer) {
                            if (layer instanceof L.CircleMarker) {
                                map.removeLayer(layer);
                            }
                        });

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            if (!data.daerah) return;
                            const city = data.daerah.nama_daerah;
                            const lat = data.daerah.latitude;
                            const lng = data.daerah.longitude;
                            if (!groupedData[city]) {
                                groupedData[city] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }
                            groupedData[city].count++;
                            // Mengakses nama jurusan dengan benar
                            const jurusanName = data.jurusan ? data.jurusan.nama_jurusan :
                                'Tidak diketahui';
                            if (!groupedData[city].jurusan[jurusanName]) {
                                groupedData[city].jurusan[jurusanName] = 0;
                            }
                            groupedData[city].jurusan[jurusanName]++;
                        });

                        Object.keys(groupedData).forEach(city => {
                            const data = groupedData[city];
                            const count = data.count;
                            const latlng = L.latLng(data.latitude, data.longitude);

                            // langsung mengakses data.jurusan
                            const jurusanList = Object.entries(data.jurusan)
                                .map(([jurusan, jumlah]) => `<li>${jurusan}: ${jumlah}</li>`)
                                .join('');

                            L.circleMarker(latlng, {
                                    radius: 8,
                                    fillColor: count > 20 ? "#800026" : count > 15 ? "#003366" :
                                        count > 10 ? "#004225" : count > 5 ? "#522546" : "#FF9F00",
                                    color: "white",
                                    weight: 1,
                                    opacity: 1,
                                    fillOpacity: 0.7
                                })
                                .bindPopup(`
                    <strong>${city}</strong><br>
                    Jumlah Mahasiswa: ${count}<br>
                    Jurusan:<br>
                    <ul style="margin: 0; padding-left: 18px;">
                        ${jurusanList}
                    </ul>
                `)
                                .on('mouseover', function() {
                                    this.openPopup();
                                })
                                .on('mouseout', function() {
                                    this.closePopup();
                                })
                                .addTo(map);
                        });
                    }
                },
                error: function(xhr) {
                    console.error("Gagal memuat data:", xhr);
                }
            });
        }


        function mapFilter() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('dashboard.petaDaerah.filter') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status == 200) {
                        // Clear existing options (except placeholder)
                        $('#tahun_masuk').find('option:gt(0)').remove();
                        $('#jurusan').find('option:gt(0)').remove();

                        // Populate Tahun Masuk
                        response.tahun_masuk.forEach(function(item) {
                            $('#tahun_masuk').append(`<option value="${item}">${item}</option>`);
                        });

                        // Populate Jurusan
                        response.jurusan.forEach(function(item) {
                            $('#jurusan').append(`<option value="${item}">${item}</option>`);
                        });
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

        $('#mapFilterForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = '{{ route('dashboard.petaDaerah.filter.show') }}';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 200) {
                        const mahasiswa = response.mahasiswa;

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            const daerah = data.daerah;
                            if (!daerah) return; // skip jika tidak ada relasi

                            const city = daerah.nama_daerah;

                            if (!groupedData[city]) {
                                groupedData[city] = {
                                    count: 0,
                                    latitude: daerah.latitude,
                                    longitude: daerah.longitude
                                };
                            }

                            // Jika ada data.total (hasil query agregat), pakai itu, jika tidak tambah 1
                            groupedData[city].count += data.total || 1;
                        });

                        // Hapus marker lama
                        map.eachLayer(function(layer) {
                            if (layer instanceof L.CircleMarker) {
                                map.removeLayer(layer);
                            }
                        });

                        // Tambahkan marker baru
                        Object.keys(groupedData).forEach(city => {
                            const data = groupedData[city];
                            const latlng = L.latLng(data.latitude, data.longitude);
                            const count = data.count;

                            L.circleMarker(latlng, {
                                    radius: 8,
                                    fillColor: count > 20 ? "#800026" : count > 15 ?
                                        "#BD0026" : count > 10 ? "#E31A1C" : count > 5 ?
                                        "#FC4E2A" : "#FD8D3C",
                                    color: "white",
                                    weight: 1,
                                    opacity: 1,
                                    fillOpacity: 0.7
                                })
                                .bindPopup(
                                    `<strong>${city}</strong><br>Jumlah Mahasiswa: ${count}`
                                )
                                .on('mouseover', function() {
                                    this.openPopup();
                                })
                                .on('mouseout', function() {
                                    this.closePopup();
                                })
                                .addTo(map);
                        });
                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                }
            });
        });



        $('#resetFilterBtn').on('click', function() {
            $('#mapFilterForm')[0].reset();

            showAllMahasiswa();
        });
    </script>
@endsection
