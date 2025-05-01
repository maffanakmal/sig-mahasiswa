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
                Gisapp
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
            <h1 class="display-6 fw-bold">Selamat datang di Gisapp</h1>
            <p class="lead">Sistem Informasi Geografis Mahasiswa Universitas Satya Negara Indonesia</p>
        </div>
    </div>

    <div class="container min-vh-100">
        <div class="card shadow-sm">
            <div class="card-header mb-2 d-flex justify-content-center align-items-center">
                <h5>Dari mana aja sih mahasiswa USNI berasal?</h5>
            </div>
            <div class="card-body">
                <div class="container-fluid">
                    <div id="map" class="mb-3"></div>
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
        var standard = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });

        var map = L.map('map', {
            center: [-2.5, 118],
            zoom: 5,
            layers: [standard]
        });

        $(document).ready(function() {
            showAllMahasiswa();
        });

        function showAllMahasiswa() {
            var mahasiswa = @json($mahasiswa);
            var groupedData = {};

            // Kelompokkan berdasarkan asal daerah
            mahasiswa.forEach((data) => {
                var city = data.daerah_asal;
                if (!groupedData[city]) {
                    groupedData[city] = {
                        count: 0,
                        city: city,
                        jurusan: []
                    };
                }
                groupedData[city].count++;
                if (data.jurusan) {
                    groupedData[city].jurusan.push(data.jurusan);
                }
            });

            var daerah = @json($daerah);

            const invalidCities = [];
            const validGroupedData = {};

            daerah.forEach(d => {
                const city = d.nama_daerah;

                if (groupedData[city]) {
                    validGroupedData[city] = groupedData[city];
                    validGroupedData[city].latitude = d.latitude;
                    validGroupedData[city].longitude = d.longitude;
                }
            });

            Object.keys(groupedData).forEach(city => {
                if (!validGroupedData[city]) {
                    invalidCities.push(city);
                }
            });

            Object.keys(validGroupedData).forEach(city => {
                const data = validGroupedData[city];
                const count = data.count;
                const latlng = L.latLng(data.latitude, data.longitude);

                // Ambil jurusan unik
                const uniqueJurusan = [...new Set(data.jurusan)];

                L.circleMarker(latlng, {
                        radius: 8,
                        fillColor: count > 20 ? "#800026" : count > 15 ? "#BD0026" : count > 10 ? "#E31A1C" :
                            count > 5 ? "#FC4E2A" : "#FD8D3C",
                        color: "white",
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.7
                    })
                    .bindPopup(
                        `<strong>${city}</strong><br>
                Jumlah Mahasiswa: ${count}<br>
                Jurusan:<br>
                <ul style="margin: 0; padding-left: 18px;">
                    ${uniqueJurusan.map(j => `<li>${j}</li>`).join('')}
                </ul>`
                    )
                    .on('mouseover', function(e) {
                        this.openPopup();
                    })
                    .on('mouseout', function(e) {
                        this.closePopup();
                    })
                    .addTo(map);
            });
        }


        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>
