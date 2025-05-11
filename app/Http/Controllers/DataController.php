<?php

namespace App\Http\Controllers;

use App\Models\Daerah;
use Exception;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function mapIndex()
    {
        return view('admin-dashboard.peta', [
            'title' => 'Peta Mahasiswa',
        ]);
    }

    public function mapShow()
    {
        try {
            $mahasiswa = Mahasiswa::with(['daerah', 'jurusan', 'sekolah'])->get();

            return response()->json([
                'status' => 200,
                'mahasiswa' => $mahasiswa,
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

    public function mapFilter()
    {
        try {
            $tahunMasuk = Mahasiswa::distinct()->orderBy('tahun_masuk')->pluck('tahun_masuk');
            $jurusan = Mahasiswa::distinct()->orderBy('jurusan')->pluck('jurusan');
            $daerah = Mahasiswa::distinct()->orderBy('daerah_asal')->pluck('daerah_asal');
            $sekolah = Mahasiswa::distinct()->orderBy('sekolah_asal')->pluck('sekolah_asal');

            return response()->json([
                'status' => 200,
                'tahun_masuk' => $tahunMasuk,
                'jurusan' => $jurusan,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "status" => 500,
                "title" => "Internal Server Error",
                "message" => $e->getMessage(),
                "icon" => "error"
            ], 500);
        }
    }

    public function mapFilterShow(Request $request)
    {
        try {
            $request->validate([
                'tahun_masuk' => 'nullable|integer',
                'jurusan' => 'nullable|string|max:255',
            ]);

            $query = DB::table('mahasiswa')
                ->join('daerah', 'mahasiswa.daerah_asal', '=', 'daerah.nama_daerah')
                ->select(
                    'daerah.nama_daerah',
                    'daerah.latitude',
                    'daerah.longitude',
                    DB::raw('count(*) as total')
                )
                ->groupBy('daerah.nama_daerah', 'daerah.latitude', 'daerah.longitude', 'mahasiswa.jurusan');

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            if ($request->jurusan) {
                $query->where('mahasiswa.jurusan', $request->jurusan);
            }

            $mahasiswa = $query->get();

            return response()->json([
                'status' => 200,
                'mahasiswa' => $mahasiswa
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'title' => 'Internal Server Error',
                'message' => $e->getMessage(),
                'icon' => 'error'
            ], 500);
        }
    }
}
