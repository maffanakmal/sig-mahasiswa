<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function mapDaerah()
    {
        return view('admin-dashboard.peta-daerah', [
            'title' => 'Peta Daerah Mahasiswa',
        ]);
    }

    public function mapSekolah()
    {
        return view('admin-dashboard.peta-sekolah', [
            'title' => 'Peta Sekolah Mahasiswa',
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
            $jurusan = Jurusan::select('kode_jurusan', 'nama_jurusan')->orderBy('nama_jurusan')->distinct()->get();

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

    public function mapShowFilterDaerah(Request $request)
    {
        try {
            $request->validate([
                'tahun_masuk' => 'nullable|string',
                'jurusan' => 'nullable|string', // Ubah menjadi integer
            ]);

            $query = DB::table('mahasiswa')
                ->leftJoin('daerah', 'mahasiswa.daerah_asal', '=', 'daerah.kode_daerah')
                ->leftJoin('jurusan', 'mahasiswa.jurusan', '=', 'jurusan.kode_jurusan')
                ->select(
                    'daerah.nama_daerah',
                    'daerah.latitude_daerah',
                    'daerah.longitude_daerah',
                    'jurusan.nama_jurusan',
                    DB::raw('count(*) as total')
                )
                ->groupBy(
                    'daerah.nama_daerah',
                    'daerah.latitude_daerah',
                    'daerah.longitude_daerah',
                    'jurusan.nama_jurusan'
                );

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            if ($request->jurusan) {
                $query->where('mahasiswa.jurusan', $request->jurusan);
            }

            $mahasiswa = $query->get();

            return response()->json([
                'status' => 200,
                'mahasiswa' => $mahasiswa,
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

    public function mapShowFilterSekolah(Request $request)
    {
        try {
            $request->validate([
                'tahun_masuk' => 'nullable|string',
                'jurusan' => 'nullable|string', // Ubah menjadi integer
            ]);

            $query = DB::table('mahasiswa')
                ->leftJoin('sekolah', 'mahasiswa.sekolah_asal', '=', 'sekolah.sekolah_id')
                ->leftJoin('jurusan', 'mahasiswa.jurusan', '=', 'jurusan.kode_jurusan')
                ->select(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'jurusan.nama_jurusan',
                    DB::raw('count(*) as total')
                )
                ->groupBy(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'jurusan.nama_jurusan'
                );

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            if ($request->jurusan) {
                $query->where('mahasiswa.jurusan', $request->jurusan);
            }

            $mahasiswa = $query->get();

            return response()->json([
                'status' => 200,
                'mahasiswa' => $mahasiswa,
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
