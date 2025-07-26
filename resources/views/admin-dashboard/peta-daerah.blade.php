@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4">Peta persebaran mahasiswa berdasarkan daerah domisili</h3>
    </div>
    @if (empty($daerah) || $daerah->isEmpty())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Perhatian!</strong> Data <strong>daerah</strong> belum diunggah admin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (empty($prodi) || $prodi->isEmpty())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Perhatian!</strong> Data <strong>prodi</strong> belum diunggah admin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (empty($mahasiswa) || $mahasiswa->isEmpty())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Perhatian!</strong> Data <strong>mahasiswa</strong> belum diunggah admin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header mb-2">
            <p class="mb-2 fw-bolder">Pencarian Data</p>
            <form action="#" id="mapFilterForm" class="row g-2 align-items-center">
                {{-- CSRF Token --}}
                @csrf

                <div class="col-md-4">
                    <select id="daerah" name="daerah" class="form-select">
                        <option value="" selected disabled>Daerah</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="tahun_masuk" name="tahun_masuk" class="form-select">
                        <option value="" selected disabled>Tahun Masuk</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="prodi" name="prodi" class="form-select">
                        <option value="" selected disabled>Program Studi</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary" id="btnSearch"><box-icon name="search" class="icon-crud" color="white"></box-icon> Cari </button>
                    <button type="button" id="resetFilterBtn" class="btn btn-danger"><box-icon name="refresh" class="icon-crud" color="white"></box-icon>
                        Reset </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <div id="map" class="mb-3"></div>
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
            const grades = [1, 5, 10, 15, 20];
            const colors = [
                "#FF9F00",
                "#522546",
                "#004225",
                "#003366",
                "#800026"
            ];

            div.innerHTML += "<h6>Jumlah Mahasiswa</h6>";
            for (let i = 0; i < grades.length; i++) {
                div.innerHTML +=
                    `<i style="background:${colors[i]}; width:15px; height:15px; display:inline-block; margin-right:6px;"></i> ` +
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
            selectProdi();
            selectTahunMasuk();
            selectDaerah();
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

                const prodiList = Object.entries(data.prodi)
                    .map(([prodi, jumlah]) => `<li>${prodi}: ${jumlah}</li>`)
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
    <div style="font-family: Arial, sans-serif; font-size: 13px; line-height: 1.4;">
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">${label}</div>
        <div><strong>Jumlah Mahasiswa:</strong> ${count}</div>
        <div style="margin-top: 5px;"><strong>Program Studi:</strong></div>
        <ul style="margin: 4px 0 0 16px; padding: 0; list-style-type: disc;">
            ${prodiList}
        </ul>
    </div>
`)
                    .on('mouseover', function() {
                        this.openPopup();
                    })
                    .addTo(map);
            });
        }

        function showDaerah() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('dashboard.peta.show') }}",
                type: "GET",
                success: function(response) {
                    if (response.status == 200) {
                        const mahasiswa = response.mahasiswa;

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
                                    prodi: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }

                            groupedData[city].count++;

                            const prodiName = data.prodi ? data.prodi.nama_prodi :
                                'Tidak diketahui';
                            if (!groupedData[city].prodi[prodiName]) {
                                groupedData[city].prodi[prodiName] = 0;
                            }
                            groupedData[city].prodi[prodiName]++;

                        });

                        renderMarkers(groupedData);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal mengambil data daerah.',
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

        const user = @json(session('loggedInUser'));

        if (user && user.role === "Warek 3") {

            let isDaerahVisible = false;

            var toggleDaerahControl = L.control({
                position: 'topright'
            });

            toggleDaerahControl.onAdd = function(map) {
                var div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');

                var button = document.createElement('button');
                button.innerHTML = '👨‍🎓';
                button.title = 'Tampilkan Data Mahasiswa berdasarkan Domisili';
                button.style.backgroundColor = 'white';
                button.style.border = 'none';
                button.style.width = '42px';
                button.style.height = '42px';
                button.style.fontSize = '22px';

                L.DomEvent.disableClickPropagation(div);

                div.appendChild(button);

                button.onclick = function() {
                    if (!isDaerahVisible) {
                        showDaerah();
                        $('#mapFilterForm')[0].reset();
                        $('#daerah').val('').trigger('change');
                        $('#tahun_masuk').val('').trigger('change');
                        $('#prodi').val('').trigger('change');
                        button.style.backgroundColor = '#007bff';
                        button.style.color = 'white';
                        button.title = 'Sembunyikan Data Mahasiswa berdasarkan Domisili';
                    } else {
                        $('#mapFilterForm')[0].reset();
                        $('#daerah').val('').trigger('change');
                        $('#tahun_masuk').val('').trigger('change');
                        $('#prodi').val('').trigger('change');
                        clearCircleMarkers();
                        button.style.backgroundColor = 'white';
                        button.style.color = 'black';
                        button.title = 'Tampilkan Data Mahasiswa berdasarkan Domisili';
                    }

                    isDaerahVisible = !isDaerahVisible;
                };

                return div;
            };

            toggleDaerahControl.addTo(map);
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
                        $('#daerah').find('option:gt(0)').remove();
                        $('#tahun_masuk').find('option:gt(0)').remove();
                        $('#prodi').find('option:gt(0)').remove();

                        response.tahun_masuk.forEach(function(item) {
                            $('#tahun_masuk').append(`<option value="${item}">${item}</option>`);
                        });

                        response.prodi.forEach(function(item) {
                            $('#prodi').append(
                                `<option value="${item.kode_prodi}">${item.nama_prodi}</option>`
                            );
                        });

                        response.daerah.forEach(function(item) {
                            $('#daerah').append(
                                `<option value="${item.kode_daerah}">${item.nama_daerah}</option>`
                            );
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

        function selectProdi() {
            $('#prodi').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#prodi').parent(), // Ensures proper z-index handling
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

        function selectTahunMasuk() {
            $('#tahun_masuk').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#tahun_masuk').parent(), // Ensures proper z-index handling
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

        function selectDaerah() {
            $('#daerah').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#daerah').parent(), // Ensures proper z-index handling
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

        $('#mapFilterForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btnSearch');

            btn.prop('disabled', false).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const tahunMasuk = $('#tahun_masuk option:selected').val();
            const prodi = $('#prodi option:selected').val();
            const daerah = $('#daerah option:selected').val();

            if (!daerah && !prodi && !tahunMasuk) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Anda harus memilih minimal satu kolom pencarian.',
                });

                btn.prop('disabled', false).html('<box-icon name="search" class="icon-crud" color="white"></box-icon> Cari');

                return;
            }

            const formData = new FormData(this);
            const url = '{{ route('dashboard.peta.filter.show.daerah') }}';

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
                    btn.prop('disabled', false).html('<box-icon name="search" class="icon-crud" color="white"></box-icon> Cari');

                    if (response.status == 200) {
                        const mahasiswa = response.mahasiswa;

                        clearCircleMarkers();

                        const groupedData = {};

                        mahasiswa.forEach(data => {
                            if (!data.nama_daerah) return;

                            const city = data.nama_daerah;
                            const lat = data.latitude_daerah;
                            const lng = data.longitude_daerah;

                            if (!groupedData[city]) {
                                groupedData[city] = {
                                    count: 0,
                                    prodi: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }

                            groupedData[city].count += data.total;

                            const prodiName = data.nama_prodi ?? 'Tidak diketahui';
                            if (!groupedData[city].prodi[prodiName]) {
                                groupedData[city].prodi[prodiName] = 0;
                            }
                            groupedData[city].prodi[prodiName] += data.total;
                        });

                        // Render marker
                        renderMarkers(groupedData);

                        // Ambil satu kota untuk zoom
                        const firstCity = Object.keys(groupedData)[0];
                        if (firstCity) {
                            const lat = groupedData[firstCity].latitude;
                            const lng = groupedData[firstCity].longitude;

                            // Zoom ke lokasi
                            map.setView([lat, lng], 12);
                        }
                    } else if (response.status == 204) {
                        clearCircleMarkers();

                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal mengambil data daerah.',
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<box-icon name="search" class="icon-crud" color="white"></box-icon> Cari');
                    
                    let errorResponse = xhr.responseJSON; // Ambil data JSON error
                    
                    Swal.fire({
                        icon: errorResponse.icon || "error",
                        title: errorResponse.title || "Error",
                        text: errorResponse.message ||
                        "Terjadi kesalahan yang tidak diketahui.",
                    });

                }
            });
        });


        $('#resetFilterBtn').on('click', function() {
            const tahun_masuk = $('#tahun_masuk option:selected').val();
            const prodi = $('#prodi option:selected').val();
            const daerah = $('#daerah option:selected').val();

            if (!tahun_masuk && !prodi && !daerah) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Anda belum melakukan pencarian data.',
                });
                return;
            }

            map.setView(defaultCenter, defaultZoom);

            $('#mapFilterForm')[0].reset();
            $('#daerah').val('').trigger('change');
            $('#tahun_masuk').val('').trigger('change');
            $('#prodi').val('').trigger('change');

            // Hapus semua marker lingkaran
            clearCircleMarkers();
        });
    </script>
@endsection
