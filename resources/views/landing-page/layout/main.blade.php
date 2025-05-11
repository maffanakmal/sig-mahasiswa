<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}" />
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/maps.css') }}" />
</head>

<body>
    <nav class="navbar navbar-expand-md bg-white sticky-top shadow-sm p-2" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand text-dark fw-bold" href="/">
                USNIGIS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-2 mb-2 mb-lg-0">
                        <a class="btn btn-sm btn-warning rounded-5 px-4" href="{{ route('login') }}">Masuk</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="jumbotron text-center mb-4">
            <h1 class="display-6 fw-bold">Selamat datang di USNIGIS</h1>
            <p class="lead">Sistem Informasi Geografis Mahasiswa Universitas Satya Negara Indonesia</p>
        </div>
    </div>

    <div class="container min-vh-100">
        <div class="card shadow-sm">
            <div class="card-header mb-2 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0 text-center flex-grow-1">Dari mana aja sih mahasiswa USNI berasal?</h5>

                <form action="#" class="ms-auto">
                    <button type="button" class="btn btn-sm rounded-5 btn-outline-primary px-4"
                        onclick="showDaerah()">Daerah</button>
                    <button type="button" class="btn btn-sm rounded-5 btn-outline-primary px-4"
                        onclick="showSekolah()">Sekolah</button>
                </form>
            </div>

            <div class="card-body">
                <div class="container-fluid">
                    <div id="map" class="mb-3"></div>

                    <p class="mahasiswa-alert text-danger mb-0" style="display: none;">
                        Data mahasiswa belum dimasukkan.
                    </p>

                    <p class="daerah-alert text-danger mb-0" style="display: none;">
                        Data daerah belum dimasukkan.
                    </p>
                    <small>Data daerah berasal dari Badan Pusat Statistik <a
                            href="https://sig.bps.go.id/">www.sig.bps.go.id</a></small>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <footer class="d-flex justify-content-center align-items-center py-3">
            <span class="mb-3 mb-md-0 text-dark">&copy; <span id="year"></span> akmal. </span>
        </footer>
    </div>

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/esri-leaflet/dist/esri-leaflet.js"></script>
    <script>
        // Buat koordinat awal dan zoom default
        var defaultCenter = [-2.3, 120]; // contoh koordinat
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
        const usniALatLng = L.latLng(-6.241583056276483, 106.78289180186107);

        // Custom icon (gunakan jika ada ikon khusus)
        const usniIcon = L.icon({
            iconUrl: '{{ asset('img/logo-usni.png') }}', // Ganti path ke ikonmu
            iconSize: [60, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -35]
        });

        // Tambahkan marker dengan ikon (atau hapus opsi icon jika tidak pakai)
        const usniAMarker = L.marker(usniALatLng, {
                icon: usniIcon // jika tidak pakai custom icon, hapus baris ini
            })
            .bindPopup('<strong>Kampus USNI</strong><br>Jl. Rawamangun Muka, Jakarta')
            .addTo(map);

        // Tampilkan popup saat hover
        usniMarker.on('mouseover', function() {
            this.openPopup();
        });

        // Tutup popup saat mouse keluar
        usniMarker.on('mouseout', function() {
            this.closePopup();
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
                        radius: 8,
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

        function showDaerah() {
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
                        clearCircleMarkers();

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

                            const jurusanName = data.jurusan ? data.jurusan.nama_jurusan :
                                'Tidak diketahui';
                            if (!groupedData[city].jurusan[jurusanName]) {
                                groupedData[city].jurusan[jurusanName] = 0;
                            }
                            groupedData[city].jurusan[jurusanName]++;
                        });

                        renderMarkers(groupedData);
                    }
                },
                error: function(xhr) {
                    console.error("Gagal memuat data daerah:", xhr);
                }
            });
        }

        function showSekolah() {
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
                        clearCircleMarkers();

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            if (!data.sekolah) return;
                            const sekolah = data.sekolah.nama_sekolah;
                            const lat = data.sekolah.latitude;
                            const lng = data.sekolah.longitude;

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

        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>
