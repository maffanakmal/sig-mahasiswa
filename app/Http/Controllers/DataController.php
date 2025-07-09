<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function mapDaerah()
    {
        $daerah = Daerah::all();
        $jurusan = Jurusan::all();
        $mahasiswa = Mahasiswa::with(['daerah', 'jurusan', 'sekolah'])->get();

        return view('admin-dashboard.peta-daerah', [
            'title' => 'USNIGIS | Peta Daerah Mahasiswa',
            'daerah' => $daerah,
            'jurusan' => $jurusan,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function mapSekolah()
    {
        $sekolah = Sekolah::with('daerah')->get();
        $jurusan = Jurusan::all();
        $mahasiswa = Mahasiswa::with(['daerah', 'jurusan', 'sekolah'])->get();

        return view('admin-dashboard.peta-sekolah', [
            'title' => 'USNIGIS | Peta Sekolah Mahasiswa',
            'sekolah' => $sekolah,
            'jurusan' => $jurusan,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function mapShow()
    {
        try {
            $mahasiswa = Mahasiswa::with(['daerah', 'jurusan', 'sekolah.daerah'])->get();

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
            $sekolah = Sekolah::select('npsn', 'nama_sekolah', 'latitude_sekolah', 'longitude_sekolah')->orderBy('nama_sekolah')->distinct()->get();
            $daerah = Daerah::select('kode_daerah', 'nama_daerah', 'latitude_daerah', 'longitude_daerah')->orderBy('nama_daerah')->distinct()->get();

            return response()->json([
                'status' => 200,
                'tahun_masuk' => $tahunMasuk,
                'jurusan' => $jurusan,
                'sekolah' => $sekolah,
                'daerah' => $daerah,
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
                'daerah' => 'nullable|numeric',
                'tahun_masuk' => 'nullable|numeric',
                'jurusan' => 'nullable|numeric',
            ]);

            $query = DB::table('mahasiswa')
                ->leftJoin('daerah', 'mahasiswa.kode_daerah', '=', 'daerah.kode_daerah')
                ->leftJoin('jurusan', 'mahasiswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
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
                $query->where('mahasiswa.kode_jurusan', $request->jurusan);
            }

            if ($request->daerah) {
                $query->where('mahasiswa.kode_daerah', $request->daerah);
            }

            $mahasiswa = $query->get();

            if ($mahasiswa->isEmpty()) {
                $namaDaerah = null;
                if ($request->daerah) {
                    $daerah = DB::table('daerah')->where('kode_daerah', $request->daerah)->first();
                    $namaDaerah = $daerah ? $daerah->nama_daerah : null;
                }

                $namaJurusan = null;
                if ($request->jurusan) {
                    $jurusan = DB::table('jurusan')->where('kode_jurusan', $request->jurusan)->first();
                    $namaJurusan = $jurusan ? $jurusan->nama_jurusan : null;
                }

                $tahunMasuk = $request->tahun_masuk;

                $message = 'Tidak ada mahasiswa';
                if ($namaDaerah) $message .= ' dari daerah ' . $namaDaerah;
                if ($namaJurusan) $message .= ' dengan jurusan ' . $namaJurusan;
                if ($tahunMasuk) $message .= ' tahun masuk ' . $tahunMasuk;
                $message .= '.';

                return response()->json([
                    'status' => 204,
                    'title' => 'Tidak Ada Mahasiswa',
                    'icon' => 'info',
                    'message' => $message,
                    'mahasiswa' => [],
                ]);
            }

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
                'sekolah' => 'nullable|numeric',
                'tahun_masuk' => 'nullable|numeric',
                'jurusan' => 'nullable|numeric',
            ]);

            $query = DB::table('mahasiswa')
                ->leftJoin('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
                ->leftJoin('jurusan', 'mahasiswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
                ->leftJoin('daerah', 'sekolah.kode_daerah', '=', 'daerah.kode_daerah')
                ->select(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'sekolah.kode_daerah',
                    'daerah.nama_daerah',
                    'jurusan.nama_jurusan',
                    DB::raw('count(*) as total')
                )
                ->groupBy(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'sekolah.kode_daerah',
                    'daerah.nama_daerah',
                    'jurusan.nama_jurusan'
                );


            if ($request->sekolah) {
                $query->where('mahasiswa.npsn', $request->sekolah);
            }

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            if ($request->jurusan) {
                $query->where('mahasiswa.kode_jurusan', $request->jurusan);
            }

            $mahasiswa = $query->get();

            if ($mahasiswa->isEmpty()) {
                $namaSekolah = null;
                if ($request->sekolah) {
                    $sekolah = DB::table('sekolah')->where('npsn', $request->sekolah)->first();
                    $namaSekolah = $sekolah ? $sekolah->nama_sekolah : null;
                }

                $namaJurusan = null;
                if ($request->jurusan) {
                    $jurusan = DB::table('jurusan')->where('kode_jurusan', $request->jurusan)->first();
                    $namaJurusan = $jurusan ? $jurusan->nama_jurusan : null;
                }

                $tahunMasuk = $request->tahun_masuk;

                $message = 'Tidak ada mahasiswa';
                if ($namaSekolah) $message .= ' dari sekolah ' . $namaSekolah;
                if ($namaJurusan) $message .= ' dengan jurusan ' . $namaJurusan;
                if ($tahunMasuk) $message .= ' tahun masuk ' . $tahunMasuk;
                $message .= '.';

                return response()->json([
                    'status' => 204,
                    'title' => 'Tidak Ada Data',
                    'icon' => 'info',
                    'message' => $message,
                    'mahasiswa' => [],
                ]);
            }

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
