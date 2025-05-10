<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kota;
use App\Models\Daerah;
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
        ]);
    }

    public function dataCount()
    {
        try {
            $mahasiswa = Mahasiswa::count();
            $asal_sekolah = DB::table('mahasiswa')->distinct('sekolah_asal')->count('sekolah_asal');
            $jurusan = DB::table('mahasiswa')->distinct('jurusan')->count('jurusan');
            $daerah = Daerah::count();

            return response()->json([
                'status' => 200,
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
