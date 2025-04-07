<?php

namespace App\Http\Controllers;

use App\Models\Kota;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $kotas = Kota::select(
            'kota_uuid',
            'nama_kota',
            'warna_kota',
            'geojson_kota' // Return full GeoJSON object
        )
        ->orderBy('kota_uuid', 'DESC')
        ->get();

        foreach ($kotas as $kota) {
            if ($kota->geojson_kota) {
                // Buat URL lengkap dari file di storage
                $kota->geojson_url = asset('storage/' . $kota->geojson_kota);
            } else {
                $kota->geojson_url = null;
            }
        }

        return view('admin-dashboard.home', [
            'title' => 'Home Page',
            'kotas' => $kotas,
        ]);
    }

    public function dataCount()
    {
        $kelurahanCount = Kelurahan::count();

        return response()->json([
            'status' => 200,
            'kelurahanCount' => $kelurahanCount,
        ], 200);
    }

}
