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
        $prodi = Jurusan::all();
        $mahasiswa = Mahasiswa::with(['daerah', 'prodi', 'sekolah'])->get();

        return view('admin-dashboard.peta-daerah', [
            'title' => 'USNIGIS | Peta Daerah Mahasiswa',
            'daerah' => $daerah,
            'prodi' => $prodi,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function mapSekolah()
    {
        $sekolah = Sekolah::with('daerah')->get();
        $prodi = Jurusan::all();
        $mahasiswa = Mahasiswa::with(['daerah', 'prodi', 'sekolah'])->get();

        return view('admin-dashboard.peta-sekolah', [
            'title' => 'USNIGIS | Peta Sekolah Mahasiswa',
            'sekolah' => $sekolah,
            'prodi' => $prodi,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function mapShow()
    {
        try {
            $mahasiswa = Mahasiswa::with(['daerah', 'prodi', 'sekolah.daerah'])->get();

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
            $prodi = Jurusan::select('kode_prodi', 'nama_prodi')->orderBy('nama_prodi')->distinct()->get();
            $sekolah = Sekolah::select('npsn', 'nama_sekolah', 'latitude_sekolah', 'longitude_sekolah')->orderBy('nama_sekolah')->distinct()->get();
            $daerah = Daerah::select('kode_daerah', 'nama_daerah', 'latitude_daerah', 'longitude_daerah')->orderBy('nama_daerah')->distinct()->get();

            return response()->json([
                'status' => 200,
                'tahun_masuk' => $tahunMasuk,
                'prodi' => $prodi,
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
                'prodi' => 'nullable|numeric',
            ]);

            $query = DB::table('mahasiswa')
                ->leftJoin('daerah', 'mahasiswa.kode_daerah', '=', 'daerah.kode_daerah')
                ->leftJoin('prodi', 'mahasiswa.kode_prodi', '=', 'prodi.kode_prodi')
                ->select(
                    'daerah.nama_daerah',
                    'daerah.latitude_daerah',
                    'daerah.longitude_daerah',
                    'prodi.nama_prodi',
                    DB::raw('count(*) as total')
                )
                ->groupBy(
                    'daerah.nama_daerah',
                    'daerah.latitude_daerah',
                    'daerah.longitude_daerah',
                    'prodi.nama_prodi'
                );

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            if ($request->prodi) {
                $query->where('mahasiswa.kode_prodi', $request->prodi);
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

                $namaProdi = null;
                if ($request->prodi) {
                    $prodi = DB::table('prodi')->where('kode_prodi', $request->prodi)->first();
                    $namaProdi = $prodi ? $prodi->nama_prodi : null;
                }

                $tahunMasuk = $request->tahun_masuk;

                $message = 'Tidak ada mahasiswa';
                if ($namaDaerah) $message .= ' dari daerah ' . $namaDaerah;
                if ($namaProdi) $message .= ' dengan prodi ' . $namaProdi;
                if ($tahunMasuk) $message .= ' tahun masuk ' . $tahunMasuk;
                $message .= '.';

                return response()->json([
                    'status' => 204,
                    'title' => 'Perhatian',
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
                'daerah' => 'nullable|numeric',
                'prodi' => 'nullable|numeric',
                'tahun_masuk' => 'nullable|numeric',
            ]);

            $query = DB::table('mahasiswa')
                ->join('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
                ->join('prodi', 'mahasiswa.kode_prodi', '=', 'prodi.kode_prodi')
                ->join('daerah', 'sekolah.kode_daerah', '=', 'daerah.kode_daerah')
                ->select(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'daerah.nama_daerah',
                    'prodi.nama_prodi',
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy(
                    'sekolah.nama_sekolah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                    'daerah.nama_daerah',
                    'prodi.nama_prodi'
                );

            // Apply filters
            if ($request->daerah) {
                $query->where('sekolah.kode_daerah', $request->daerah);
            }

            if ($request->prodi) {
                $query->where('mahasiswa.kode_prodi', $request->prodi);
            }

            if ($request->tahun_masuk) {
                $query->where('mahasiswa.tahun_masuk', $request->tahun_masuk);
            }

            $result = $query->get();

            if ($result->isEmpty()) {
                // Handle pesan kosong
                $daerah = $request->daerah ? DB::table('daerah')->where('kode_daerah', $request->daerah)->value('nama_daerah') : null;
                $prodi = $request->prodi ? DB::table('prodi')->where('kode_prodi', $request->prodi)->value('nama_prodi') : null;
                $tahun = $request->tahun_masuk;

                $message = 'Tidak ada asal sekolah mahasiswa';
                if ($daerah) $message .= ' dari daerah ' . $daerah;
                if ($prodi) $message .= ' dengan prodi ' . $prodi;
                if ($tahun) $message .= ' tahun masuk ' . $tahun;
                $message .= '.';

                return response()->json([
                    'status' => 204,
                    'title' => 'Data Tidak Ditemukan',
                    'icon' => 'info',
                    'message' => $message,
                    'mahasiswa' => [],
                ]);
            }

            return response()->json([
                'status' => 200,
                'mahasiswa' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'title' => 'Kesalahan Server',
                'message' => $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }
}
