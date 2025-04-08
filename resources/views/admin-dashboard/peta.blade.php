@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Peta Sebaran Mahasiswa USNI</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header mb-2">
            <p class="mb-2">Filter Data</p>
            <form action="#" id="mapFilterForm" class="d-flex gap-2">
                @csrf
                <div class="form-group">
                    <select class="form-select" id="tahun_masuk" name="tahun_masuk">
                        <option value="" selected disabled>Tahun Masuk</option>

                    </select>
                </div>
                <div class="form-group">
                    <select class="form-select" id="jurusan" name="jurusan">
                        <option value="" selected disabled>Jurusan</option>

                    </select>
                </div>
                <div class="form-group">
                    <select class="form-select" id="status_mahasiswa" name="status_mahasiswa">
                        <option value="" selected disabled>Status Mahasiswa</option>

                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class='bx bx-search'></i> Cari</button>
                <button type="button" id="resetFilterBtn" class="btn btn-danger">Reset</button>
            </form>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <div id="map" class="mb-3"></div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
    <script>
        var standard = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });

        var humanitarian = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });

        var topomap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '&copy; <a href="https://opentopomap.org">OpenTopoMap</a>'
        });

        var catroPorsitron = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; Carto, OpenStreetMap',
            maxZoom: 20
        });

        var catroDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; Carto, OpenStreetMap',
            maxZoom: 20
        });

        var map = L.map('map', {
            center: [-2.5, 118],
            zoom: 5,
            layers: [standard]
        });

        var baseMaps = {
            "OpenStreetMap - Standard": standard,
            "OpenStreetMap - Humanitarian": humanitarian,
            "OpenStreetMap - TopoMap": topomap,
            "Carto - Positron": catroPorsitron,
            "Carto - Dark": catroDark
        };

        var layerControl = L.control.layers(baseMaps).addTo(map);

        $(document).ready(function() {
            showAllMahasiswa();
            mapFilter();
        });

        function showAllMahasiswa() {
            var mahasiswa = @json($mahasiswas); // Assuming response contains mahasiswa
            var groupedData = {};

            // Group data by city
            mahasiswa.forEach((data) => {
                var city = data.daerah_asal;

                if (!groupedData[city]) {
                    groupedData[city] = {
                        count: 0,
                        city: city
                    };
                }
                groupedData[city].count++;
            });

            fetch('/storage/geojson/daftar-nama-daerah.geojson')
                .then(response => response.json())
                .then(geojsonData => {
                    var validCities = geojsonData.features.map(feature => feature.properties.name);
                    var validGroupedData = {};

                    Object.keys(groupedData).forEach(city => {
                        if (validCities.includes(city)) {
                            validGroupedData[city] = groupedData[city];
                        } else {
                            console.log("Kota tidak ditemukan di GeoJSON:", city);
                        }
                    });

                    // Loop through the GeoJSON features and add markers to the map
                    geojsonData.features.forEach(feature => {
                        var cityName = feature.properties.name;
                        if (validGroupedData[cityName]) {
                            var count = validGroupedData[cityName].count;
                            var coordinates = feature.geometry.coordinates;
                            var latlng = L.latLng(coordinates[1], coordinates[
                                0]); // [lat, lng]

                            // Add circle marker
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
                                .bindPopup("<strong>" + cityName +
                                    "</strong><br>Jumlah Mahasiswa: " + count)
                                .addTo(map); // Ensure 'map' is defined
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading GeoJSON:', error);
                });
        }

        function mapFilter() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('grafik.peta.filter') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status == 200) {
                        // Clear existing options (except placeholder)
                        $('#tahun_masuk').find('option:gt(0)').remove();
                        $('#jurusan').find('option:gt(0)').remove();
                        $('#status_mahasiswa').find('option:gt(0)').remove();

                        // Populate Tahun Masuk
                        response.tahun_masuk.forEach(function(item) {
                            $('#tahun_masuk').append(`<option value="${item}">${item}</option>`);
                        });

                        // Populate Jurusan
                        response.jurusan.forEach(function(item) {
                            $('#jurusan').append(`<option value="${item}">${item}</option>`);
                        });

                        // Populate Status Mahasiswa
                        response.status_mahasiswa.forEach(function(item) {
                            $('#status_mahasiswa').append(`<option value="${item}">${item}</option>`);
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
            let url = '{{ route('grafik.peta.filter.show') }}';
            let httpMethod = 'POST';

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
                        var groupedData = {};

                        //use response data
                        response.mahasiswa.forEach((data) => {
                            var city = data.daerah_asal;

                            if (!groupedData[city]) {
                                groupedData[city] = {
                                    count: 0,
                                    city: city
                                };
                            }
                            groupedData[city].count += data.total ||
                                1; // use data.total if available, fallback to 1
                        });

                        fetch('/storage/geojson/daftar-nama-daerah.geojson')
                            .then(response => response.json())
                            .then(geojsonData => {
                                var validCities = geojsonData.features.map(feature => feature
                                    .properties.name);

                                var validGroupedData = {};
                                Object.keys(groupedData).forEach(city => {
                                    if (validCities.includes(city)) {
                                        validGroupedData[city] = groupedData[city];
                                    } else {
                                        console.log("Kota tidak ditemukan di GeoJSON:",
                                            city);
                                    }
                                });

                                // Clear existing markers
                                map.eachLayer(function(layer) {
                                    if (layer instanceof L.CircleMarker) {
                                        map.removeLayer(layer);
                                    }
                                });

                                // Loop through the GeoJSON features and add markers to the map
                                geojsonData.features.forEach(feature => {
                                    var cityName = feature.properties.name;
                                    if (validGroupedData[cityName]) {
                                        var count = validGroupedData[cityName].count;

                                        // Ambil koordinat tengah kota
                                        var coordinates = feature.geometry.coordinates;
                                        var latlng = L.latLng(coordinates[1], coordinates[
                                            0]); // [lat, lng]

                                        // Tambahkan marker lingkaran
                                        L.circleMarker(latlng, {
                                                radius: 8,
                                                fillColor: count > 20 ? "#800026" :
                                                    count > 15 ? "#BD0026" : count >
                                                    10 ? "#E31A1C" : count > 5 ?
                                                    "#FC4E2A" : "#FD8D3C",
                                                color: "white",
                                                weight: 1,
                                                opacity: 1,
                                                fillOpacity: 0.7
                                            })
                                            .bindPopup("<strong>" + cityName +
                                                "</strong><br>Jumlah Mahasiswa: " + count)
                                            .addTo(map);
                                    }
                                });
                            })
                            .catch(error => {
                                console.error('Error loading GeoJSON:', error);
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
