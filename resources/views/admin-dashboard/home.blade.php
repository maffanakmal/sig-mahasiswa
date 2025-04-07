@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4 mb-3">Dashboard</h3>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="icon-card-wrapper">

                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="card-title">Kelurahan</h6>
                            <p class="card-text" id="kelurahanCount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Peta</h5>
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
        $(document).ready(function() {
            dataCount();
        });

        function dataCount() {
            $.ajax({
                url: "{{ route('home.count') }}", // Ensure this is the correct API route
                type: "GET",
                dataType: "json", // Ensure the response is parsed as JSON
                success: function(response) {
                    if (response.status === 200) {
                        // ✅ Update count display
                        $('#kelurahanCount').text(response.kelurahanCount);
                    }
                },
                error: function(xhr) {
                    console.error("Failed to fetch kelurahan data:", xhr);
                }
            });
        }

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
            center: [-2.357071571963471, 120.66128377655523],
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

        var kotaData = @json($kotas); // Convert Laravel variable to JSON

        kotaData.forEach(function(kota) {
            if (kota.geojson_url) {
                fetch(kota.geojson_url) // Ambil data GeoJSON dari URL
                    .then(response => response.json()) // Konversi ke JSON
                    .then(data => {
                        var geojsonLayer = L.geoJSON(data, {
                            style: function(feature) {
                                return {
                                    color: kota.warna_kota || "blue",
                                    weight: 1,
                                    fillOpacity: 0.4
                                };
                            }
                        }).addTo(map);

                        layerControl.addOverlay(geojsonLayer, kota
                        .nama_kota); // Tambahkan ke Layer Control
                    })
                    .catch(error => console.error("Error loading GeoJSON for:", kota.nama_kota, error));
            }
        });
    </script>
@endsection
