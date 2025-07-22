<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}" />
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/maps.css') }}" />
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-landing sticky-top shadow-sm p-2">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="/">
                USNIGIS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-2 mb-2 mb-lg-0">
                        <a class="btn btn-sm btn-light rounded-5 px-4" href="{{ route('auth.login') }}">Masuk</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="jumbotron text-center mb-4">
            <h1 class="display-6 fw-bold">Selamat datang di USNIGIS</h1>
            <p class="lead">Sistem Informasi Geografis Persebaran Mahasiswa Universitas Satya Negara Indonesia</p>
        </div>
    </div>

    <div class="container-fluid footer-landing">
        <footer class="d-flex justify-content-center align-items-center py-3">
            <span class="mb-3 mb-md-0 text-white">&copy; <span id="year"></span> USNIGIS. </span>
        </footer>
    </div>

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('js/esri-leaflet.js') }}"></script>
    {{-- <script>
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

        function showDaerah() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('landing.show') }}",
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
                            const lat = data.daerah.latitude_daerah;
                            const lng = data.daerah.longitude_daerah;

                            if (!groupedData[city]) {
                                groupedData[city] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude_daerah: lat,
                                    longitude_daerah: lng
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
                url: "{{ route('landing.show') }}",
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
                                    latitude_sekolah: lat,
                                    longitude_sekolah: lng
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
    </script>--}}
    <script>
        $(document).ready(function() {
            document.getElementById("year").textContent = new Date().getFullYear();
        });
    </script>
</body>

</html>
