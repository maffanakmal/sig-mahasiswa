@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4">Peta Persebaran Mahasiswa Berdasarkan Sekolah</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header mb-2">
            @if (session('loggedInUser')['role'] === 'Warek 3')
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

                <div>
                    <button type="submit" class="btn btn-primary"><i class='bx bx-search'></i> Cari</button>
                </div>

                <div>
                    <button type="button" id="resetFilterBtn" class="btn btn-danger">Reset</button>
                </div>
            </form>
            @endif
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

        // Koordinat Kampus USNI
        const usniALatLng = L.latLng(-6.241724, 106.783435);
        const usniBLatLng = L.latLng(-6.2738302, 107.0200002);

        // Custom icon (pakai logo kampus)
        const usniIcon = L.icon({
            iconUrl: '{{ asset('img/icon-usni.png') }}',
            iconSize: [50, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -35]
        });

        // Marker untuk Kampus USNI A
        const usniAMarker = L.marker(usniALatLng, {
                icon: usniIcon
            })
            .bindPopup(
                '<strong>Kampus USNI A</strong><br>Jl Arteri Pondok Indah No.11 Kebayoran Lama, Kota Jakarta Selatan')
            .addTo(map);
        usniAMarker.on('mouseover', function() {
            this.openPopup();
        });
        usniAMarker.on('mouseout', function() {
            this.closePopup();
        });

        // Marker untuk Kampus USNI B
        const usniBMarker = L.marker(usniBLatLng, {
                icon: usniIcon
            })
            .bindPopup('<strong>Kampus USNI B</strong><br>Jl. H. Jampang No.91 Jatimulya, Kabupaten Bekasi')
            .addTo(map);
        usniBMarker.on('mouseover', function() {
            this.openPopup();
        });
        usniBMarker.on('mouseout', function() {
            this.closePopup();
        });


        $(document).ready(function() {
            mapFilter();
            showSekolah();
        });

        function clearCircleMarkers() {
            map.eachLayer(function(layer) {
                if (layer instanceof L.CircleMarker) {
                    map.removeLayer(layer);
                }
            });
        }

        function renderMarkers(groupedData) {
            Object.keys(groupedData).forEach(label => {
                const data = groupedData[label];
                const count = data.count;
                const latlng = L.latLng(data.latitude, data.longitude);

                const jurusanList = Object.entries(data.jurusan)
                    .map(([jurusan, jumlah]) => `<li>${jurusan}: ${jumlah}</li>`)
                    .join('');

                L.circleMarker(latlng, {
                        radius: 10,
                        fillColor: count > 20 ? "#800026" : count > 15 ? "#003366" : count > 10 ? "#004225" :
                            count > 5 ? "#522546" : "#FF9F00",
                        color: "white",
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.7
                    })
                    .bindPopup(`
                <strong>${label}</strong><br>
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

        function showSekolah() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('dashboard.peta.show') }}",
                type: "GET",
                success: function(response) {
                    if (response.status == 200) {
                        const mahasiswa = response.mahasiswa;

                        // Hapus marker lama
                        clearCircleMarkers();

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            if (!data.sekolah) return;
                            const sekolah = data.sekolah.nama_sekolah;
                            const lat = data.sekolah.latitude_sekolah;
                            const lng = data.sekolah.longitude_sekolah;

                            if (!groupedData[sekolah]) {
                                groupedData[sekolah] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }
                            groupedData[sekolah].count++;

                            const jurusanName = data.jurusan ? data.jurusan.nama_jurusan :
                                'Tidak diketahui';
                            if (!groupedData[sekolah].jurusan[jurusanName]) {
                                groupedData[sekolah].jurusan[jurusanName] = 0;
                            }
                            groupedData[sekolah].jurusan[jurusanName]++;
                        });

                        renderMarkers(groupedData);
                    }
                },
                error: function(xhr) {
                    console.error("Gagal memuat data sekolah:", xhr);
                }
            });
        }


        function mapFilter() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('dashboard.peta.filter') }}',
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
                        $('#jurusan').append(`<option value="${item.kode_jurusan}">${item.nama_jurusan}</option>`);
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
            const url = '{{ route('dashboard.peta.filter.show.sekolah') }}';

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

                        clearCircleMarkers();

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            if (!data.nama_sekolah) return;
                            const school = data.nama_sekolah;
                            const lat = data.latitude;
                            const lng = data.longitude;

                            if (!groupedData[school]) {
                                groupedData[school] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }
                            groupedData[school].count += data
                            .total; // karena sudah grouped di SQL

                            const jurusanName = data.nama_jurusan ?? 'Tidak diketahui';
                            if (!groupedData[school].jurusan[jurusanName]) {
                                groupedData[school].jurusan[jurusanName] = 0;
                            }
                            groupedData[school].jurusan[jurusanName] += data.total;
                        });

                        renderMarkers(groupedData);

                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                }
            });
        });

        $('#resetFilterBtn').on('click', function() {
            $('#mapFilterForm')[0].reset();

            showSekolah();
        });
    </script>
@endsection
