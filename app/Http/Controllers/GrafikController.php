<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrafikController extends Controller
{
    public function mapIndex()
    {
        $data = [
            'title' => 'Peta Page',
            'mahasiswas' => Mahasiswa::select(
                'mahasiswa_uuid',
                'daerah_asal',
            )->get()
        ];

        return view('admin-dashboard.peta', $data);
    }

    public function mapFilter()
    {
        try {
            $tahunMasuk = Mahasiswa::distinct()->orderBy('tahun_masuk')->pluck('tahun_masuk');
            $jurusan = Mahasiswa::distinct()->orderBy('jurusan')->pluck('jurusan');
            $status = Mahasiswa::distinct()->orderBy('status_mahasiswa')->pluck('status_mahasiswa');

            return response()->json([
                'status' => 200,
                'tahun_masuk' => $tahunMasuk,
                'jurusan' => $jurusan,
                'status_mahasiswa' => $status,
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
                'status_mahasiswa' => 'nullable|string|max:255',
            ]);

            $query = Mahasiswa::query();

            if ($request->tahun_masuk) {
                $query->where('tahun_masuk', $request->tahun_masuk);
            }

            if ($request->jurusan) {
                $query->where('jurusan', $request->jurusan);
            }

            if ($request->status_mahasiswa) {
                $query->where('status_mahasiswa', $request->status_mahasiswa);
            }

            // Group by daerah_asal and count how many students per region
            $mahasiswa = $query->select(
                'daerah_asal',
                DB::raw('count(*) as total')
            )->groupBy('daerah_asal')->get();

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
