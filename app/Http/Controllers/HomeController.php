<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kota;
use App\Models\User;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
use App\Models\Kelurahan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('admin-dashboard.home', [
            'title' => 'Home Page',
            'status' => 'active',
        ]);
    }

    public function dataCount()
    {
        try {
            $pengguna = User::count();
            $mahasiswa = Mahasiswa::count();
            $asal_sekolah = Sekolah::count();
            $jurusan = Jurusan::count();
            $daerah = Daerah::count();

            return response()->json([
                'status' => 200,
                'pengguna' => $pengguna,
                'daerah' => $daerah,
                'mahasiswa' => $mahasiswa,
                'asal_sekolah' => $asal_sekolah,
                'jurusan' => $jurusan,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => 500,
                "title" => "Internal Server Error",
                "message" => $e->getMessage(),
                "icon" => "error"
            ], 500);
        }
    }
}
