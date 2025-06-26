@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold fs-4">Peta Persebaran Mahasiswa Berdasarkan Sekolah</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header mb-2">
            @if (session('loggedInUser')['role'] === 'Warek 3')
                <div class="form-filter mb-3">
                    <p class="mb-2">Filter Data</p>
                    <form action="#" id="mapFilterForm" class="d-flex align-items-end gap-2 flex-wrap">
                        @csrf

                        <div class="form-group" style="min-width: 180px;">
                            <select id="tahun_masuk" name="tahun_masuk" class="form-control">
                                <option value="" selected disabled>Pilih Tahun Masuk</option>
                            </select>
                        </div>

                        <div class="form-group" style="min-width: 180px;">
                            <select id="jurusan" name="jurusan" class="form-control">
                                <option value="" selected disabled>Pilih Jurusan</option>
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

                <div class="form-jarak">
                    <p class="mb-2">Hitung Jarak Sekolah</p>
                    <form action="#" id="hitungJarakForm" class="d-flex align-items-end gap-2 flex-wrap">
                        @csrf
                        <div class="form-group" style="min-width: 180px;">
                            <select id="titik_awal" name="titik_awal" class="form-control">
                                <option value="" selected disabled>Pilih Kampus USNI</option>
                                <option value="USNI A">USNI A</option>
                                <option value="USNI B">USNI B</option>
                            </select>
                        </div>

                        <div class="form-group" style="min-width: 180px;">
                            <select id="titik_akhir" name="titik_akhir" class="form-control">
                                <option value="" selected disabled>Pilih Sekolah</option>
                            </select>
                        </div>

                        <div>
                            <button type="button" id="hitungJarakBtn" class="btn btn-primary">Hitung</button>
                        </div>
                        <div>
                            <button type="button" id="resetJarakBtn" class="btn btn-danger">Reset</button>
                        </div>
                    </form>
                </div>
            @elseif (session('loggedInUser')['role'] === 'PMB')
                <div class="form-jarak">
                    <p class="mb-2">Hitung Jarak Sekolah</p>
                    <form action="#" id="hitungJarakForm" class="d-flex align-items-end gap-2 flex-wrap">
                        @csrf
                        <div class="form-group" style="min-width: 180px;">
                            <select id="titik_awal" name="titik_awal" class="form-control">
                                <option value="" selected disabled>Pilih Kampus USNI</option>
                                <option value="USNI A">USNI A</option>
                                <option value="USNI B">USNI B</option>
                            </select>
                        </div>

                        <div class="form-group" style="min-width: 180px;">
                            <select id="titik_akhir" name="titik_akhir" class="form-control">
                                <option value="" selected disabled>Pilih Sekolah</option>
                            </select>
                        </div>

                        <div>
                            <button type="button" id="hitungJarakBtn" class="btn btn-primary">Hitung</button>
                        </div>
                        <div>
                            <button type="button" id="resetJarakBtn" class="btn btn-danger">Reset</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <div id="map" class="mb-3"></div>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-3">
        <div class="row text-center">
            <div class="col-md-4 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Mahasiswa di Peta</h6>
                        <h3 class="text-primary" id="jumlah-tampil">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Sekolah Tidak Ada</h6>
                        <h3 class="text-warning" id="jumlah-sekolah-null">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Jurusan Tidak Ada</h6>
                        <h3 class="text-danger" id="jumlah-jurusan-null">0</h3>
                    </div>
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
            const grades = [1, 5, 10, 15, 20];
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
            selectSekolah();
            selectJurusan();
            selectTahunMasuk();
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
    <div style="font-family: Arial, sans-serif; font-size: 13px; line-height: 1.4;">
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">${label}</div>
        <div style="margin-bottom: 4px;">
            <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(label + ' ' + data.daerah)}"
   target="_blank"
   style="color: #007bff; text-decoration: none;">
   Lihat detail lokasi via Google Maps
</a>

        </div>
        <div><strong>Jumlah Mahasiswa:</strong> ${count}</div>
        <div style="margin-top: 5px;"><strong>Jurusan:</strong></div>
        <ul style="margin: 4px 0 0 16px; padding: 0; list-style-type: disc;">
            ${jurusanList}
        </ul>
    </div>
`)
                    .on('mouseover', function() {
                        this.openPopup();
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
                        let countTampil = 0;
                        let countSekolahNull = 0;
                        let countJurusanNull = 0;

                        mahasiswa.forEach(data => {
                            if (!data.sekolah) {
                                countSekolahNull++;
                            }
                            if (!data.jurusan) {
                                countJurusanNull++;
                            }

                            if (!data.sekolah) return;
                            const sekolah = data.sekolah.nama_sekolah;
                            const lat = data.sekolah.latitude_sekolah;
                            const lng = data.sekolah.longitude_sekolah;
                            const daerah = data.sekolah.daerah?.nama_daerah || '';

                            if (!groupedData[sekolah]) {
                                groupedData[sekolah] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude: lat,
                                    longitude: lng,
                                    daerah: daerah,
                                };
                            }
                            groupedData[sekolah].count++;

                            const jurusanName = data.jurusan ? data.jurusan.nama_jurusan :
                                'Tidak diketahui';
                            if (!groupedData[sekolah].jurusan[jurusanName]) {
                                groupedData[sekolah].jurusan[jurusanName] = 0;
                            }
                            groupedData[sekolah].jurusan[jurusanName]++;

                            countTampil++;
                        });

                        $('#jumlah-tampil').text(countTampil);
                        $('#jumlah-sekolah-null').text(countSekolahNull);
                        $('#jumlah-jurusan-null').text(countJurusanNull);


                        renderMarkers(groupedData);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan saat mengambil data.',
                        });
                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                    });
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

                        $('#tahun_masuk').find('option:gt(0)').remove();
                        $('#jurusan').find('option:gt(0)').remove();
                        $('#titik_akhir').find('option:gt(0)').remove();

                        response.tahun_masuk.forEach(function(item) {
                            $('#tahun_masuk').append(`<option value="${item}">${item}</option>`);
                        });

                        response.jurusan.forEach(function(item) {
                            $('#jurusan').append(
                                `<option value="${item.kode_jurusan}">${item.nama_jurusan}</option>`
                            );
                        });

                        response.sekolah.forEach(function(item) {
                            $('#titik_akhir').append(
                                `<option value="${item.latitude_sekolah} ${item.longitude_sekolah}">${item.nama_sekolah}</option>`
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

        function selectSekolah() {
            $('#titik_akhir').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#titik_akhir').parent(), // Ensures proper z-index handling
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

        function selectJurusan() {
            $('#jurusan').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumInputLength: 0, // Allow search immediately
                dropdownParent: $('#jurusan').parent(), // Ensures proper z-index handling
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

        $('#mapFilterForm').on('submit', function(e) {
            e.preventDefault();

            const tahunMasuk = $('#tahun_masuk option:selected').val();
            const jurusan = $('#jurusan option:selected').val();

            // Validasi jika titik awal atau akhir kosong
            if (!tahunMasuk || !jurusan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih Tahun Masuk dan Jurusan terlebih dahulu.',
                });
                return;
            }

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
                            const lat = data.latitude_sekolah;
                            const lng = data.longitude_sekolah;

                            if (!groupedData[school]) {
                                groupedData[school] = {
                                    count: 0,
                                    jurusan: {},
                                    latitude: lat,
                                    longitude: lng
                                };
                            }

                            groupedData[school].count += data.total;

                            const jurusanName = data.nama_jurusan ?? 'Tidak diketahui';
                            if (!groupedData[school].jurusan[jurusanName]) {
                                groupedData[school].jurusan[jurusanName] = 0;
                            }

                            groupedData[school].jurusan[jurusanName] += data.total;
                        });

                        renderMarkers(groupedData);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan saat mengambil data.',
                        });
                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                    });
                }
            });
        });

        $('#resetFilterBtn').on('click', function() {
            const tahunMasuk = $('#tahun_masuk option:selected').val();
            const jurusan = $('#jurusan option:selected').val();

            // Validasi jika titik awal atau akhir kosong
            if (!tahunMasuk || !jurusan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Anda belum melakukan filter data.',
                });
                return;
            }

            $('#mapFilterForm')[0].reset();

            $('#jurusan').val('').trigger('change');
            $('#tahun_masuk').val('').trigger('change');

            showSekolah();
        });

        let distanceLine = null;
        let distanceLegendControl = null;

        function showDistanceLegend(asalLabel, tujuanLabel, jarakKm) {
            if (distanceLegendControl) {
                map.removeControl(distanceLegendControl);
            }

            distanceLegendControl = L.control({
                position: 'bottomleft'
            });
            distanceLegendControl.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'info legend');
                div.style.backgroundColor = 'white';
                div.style.padding = '8px';
                div.style.border = '1px solid #ccc';
                div.style.boxShadow = '2px 2px 6px rgba(0,0,0,0.1)';
                div.innerHTML = `
            <h6 class="mb-1">Info Jarak</h6>
            <strong>Asal:</strong> ${asalLabel}<br>
            <strong>Tujuan:</strong> ${tujuanLabel}<br>
            <strong>Jarak:</strong> ${jarakKm} km
        `;
                return div;
            };
            distanceLegendControl.addTo(map);
        }

        // Event tombol Hitung
        $('#hitungJarakBtn').on('click', function() {
            const asal = $('#titik_awal').val();
            const tujuanOption = $('#titik_akhir option:selected');

            if (!asal || !tujuanOption.val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih kedua titik terlebih dahulu.',
                });
                return;
            }

            const tujuanLabel = tujuanOption.text();
            const tujuanCoords = tujuanOption.val().split(' ');
            const tujuanLatLng = L.latLng(parseFloat(tujuanCoords[0]), parseFloat(tujuanCoords[1]));

            const asalLatLng = (asal === 'USNI A') ? usniALatLng : usniBLatLng;

            // Hapus garis sebelumnya jika ada
            if (distanceLine) {
                map.removeLayer(distanceLine);
            }

            // Hitung dan tampilkan garis
            distanceLine = L.polyline([asalLatLng, tujuanLatLng], {
                color: 'red',
                weight: 2
            }).addTo(map);

            const distanceInMeters = asalLatLng.distanceTo(tujuanLatLng);
            const distanceInKm = (distanceInMeters / 1000).toFixed(2);

            showDistanceLegend(asal, tujuanLabel, distanceInKm);
        });

        // Event tombol Reset
        $('#resetJarakBtn').on('click', function() {
            const asal = $('#titik_awal').val();
            const tujuanOption = $('#titik_akhir option:selected');

            if (!asal || !tujuanOption.val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Anda belum melakukan perhitungan jarak.',
                });
                return;
            }

            $('#hitungJarakForm')[0].reset();

            $('#titik_awal').val('').trigger('change');
            $('#titik_akhir').val('').trigger('change');

            if (distanceLine) {
                map.removeLayer(distanceLine);
                distanceLine = null;
            }

            if (distanceLegendControl) {
                map.removeControl(distanceLegendControl);
                distanceLegendControl = null;
            }
        });

        // function haversineDistanceManual(lat1, lon1, lat2, lon2) {
        //     const R = 6371000; // Jari-jari bumi dalam meter

        //     // Ubah ke radian
        //     const toRad = deg => deg * Math.PI / 180;

        //     const φ1 = toRad(lat1);
        //     const φ2 = toRad(lat2);
        //     const Δφ = toRad(lat2 - lat1);
        //     const Δλ = toRad(lon2 - lon1);

        //     const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        //         Math.cos(φ1) * Math.cos(φ2) *
        //         Math.sin(Δλ / 2) * Math.sin(Δλ / 2);

        //     const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        //     return R * c; // Hasil dalam meter
        // }

        // $('#hitungJarakBtn').on('click', function() {
        //     const titikAwal = $('#titik_awal').val();
        //     const titikAkhir = $('#titik_akhir').val();

        //     if (!titikAwal || !titikAkhir) {
        //         Swal.fire({
        //             icon: 'warning',
        //             title: 'Peringatan',
        //             text: 'Silakan pilih kedua titik terlebih dahulu.'
        //         });
        //         return;
        //     }

        //     // Ambil koordinat titik awal berdasarkan kampus
        //     let asalLat, asalLng;
        //     if (titikAwal === "USNI A") {
        //         asalLat = -6.241724;
        //         asalLng = 106.783435;
        //     } else if (titikAwal === "USNI B") {
        //         asalLat = -6.2738302;
        //         asalLng = 107.0200002;
        //     }

        //     // Ambil koordinat titik akhir dari value <option>
        //     const [akhirLat, akhirLng] = titikAkhir.split(' ').map(Number);

        //     if (isNaN(akhirLat) || isNaN(akhirLng)) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Kesalahan',
        //             text: 'Koordinat titik akhir tidak valid.'
        //         });
        //         return;
        //     }

        //     // Hitung jarak manual
        //     const jarakMeter = haversineDistanceManual(asalLat, asalLng, akhirLat, akhirLng);
        //     const jarakKm = (jarakMeter / 1000).toFixed(2);

        //     // Tampilkan ke pengguna melalui legenda khusus
        //     if (window.jarakLegend) {
        //         map.removeControl(window.jarakLegend);
        //     }

        //     const legend = L.control({
        //         position: 'bottomleft'
        //     });

        //     legend.onAdd = function(map) {
        //         const div = L.DomUtil.create('div', 'info legend bg-white p-2 shadow-sm rounded');
        //         div.innerHTML = `
    //     <h6 class="mb-1">Info Jarak</h6>
    //     <strong>Dari:</strong> ${titikAwal}<br>
    //     <strong>Ke:</strong> ${$("#titik_akhir option:selected").text()}<br>
    //     <strong>Jarak:</strong> ${jarakKm} km
    // `;
        //         return div;
        //     };

        //     legend.addTo(map);
        //     window.jarakLegend = legend;

        //     // Tambahkan garis penghubung
        //     if (window.jarakLine) {
        //         map.removeLayer(window.jarakLine);
        //     }

        //     window.jarakLine = L.polyline([
        //         [asalLat, asalLng],
        //         [akhirLat, akhirLng]
        //     ], {
        //         color: 'blue',
        //         weight: 3,
        //         opacity: 0.7,
        //         dashArray: '5, 5'
        //     }).addTo(map);

        //     // Zoom agar kedua titik terlihat
        //     const bounds = L.latLngBounds([
        //         [asalLat, asalLng],
        //         [akhirLat, akhirLng]
        //     ]);
        //     map.fitBounds(bounds);
        // });
    </script>
@endsection
