<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    public function index()
    {
        $loggedInUser = session('loggedInUser');
        $user = User::where('user_uuid', $loggedInUser['user_uuid'])->first();

        return view('admin-dashboard.home', [
            'title' => 'USNIGIS | Halaman Dashboard',
            'status' => 'active',
            'user' => $user,
        ]);
    }

    public function dataCount()
    {
        try {
            // Mengambil semua hitungan dasar dalam satu query menggunakan DB::select
            $counts = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM users) as jumlah_pengguna,
                (SELECT COUNT(*) FROM mahasiswa) as jumlah_mahasiswa,
                (SELECT COUNT(*) FROM sekolah) as jumlah_sekolah,
                (SELECT COUNT(*) FROM prodi) as jumlah_prodi,
                (SELECT COUNT(*) FROM daerah) as jumlah_daerah
        ")[0];

            // Query untuk daerah dengan mahasiswa terbanyak (digunakan untuk dua tujuan)
            $daerahData = DB::table('mahasiswa')
                ->join('daerah', 'mahasiswa.kode_daerah', '=', 'daerah.kode_daerah')
                ->select('daerah.kode_daerah', 'daerah.nama_daerah', DB::raw('COUNT(*) as total_mahasiswa'))
                ->groupBy('daerah.kode_daerah', 'daerah.nama_daerah')
                ->orderByDesc('total_mahasiswa')
                ->limit(5)
                ->get();

            // Query untuk jenis sekolah
            $sekolahTipe = DB::table('mahasiswa')
                ->join('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
                ->select(
                    DB::raw("
                    CASE 
                        WHEN sekolah.nama_sekolah LIKE '%SMK%' THEN 'SMK'
                        WHEN sekolah.nama_sekolah LIKE '%SMA%' THEN 'SMA'
                        WHEN sekolah.nama_sekolah LIKE '%MAK%' THEN 'MAK'
                        WHEN sekolah.nama_sekolah LIKE '%MA%' THEN 'MA'
                        ELSE 'Lainnya'
                    END as jenis_sekolah
                "),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('jenis_sekolah')
                ->orderByDesc('total')
                ->get();

            // Mengambil kode daerah untuk query berikutnya
            $kodeDaerahList = $daerahData->pluck('kode_daerah');

            // Query untuk detail prodi berdasarkan daerah teratas
            $detailProdi = DB::table('mahasiswa')
                ->join('prodi', 'mahasiswa.kode_prodi', 'prodi.kode_prodi')
                ->select('mahasiswa.kode_daerah', 'prodi.nama_prodi', DB::raw('COUNT(*) as total'))
                ->whereIn('mahasiswa.kode_daerah', $kodeDaerahList)
                ->groupBy('mahasiswa.kode_daerah', 'prodi.nama_prodi')
                ->orderBy('mahasiswa.kode_daerah')
                ->orderByDesc('total')
                ->get();

            // Persiapan data untuk chart
            $prodiList = $detailProdi->pluck('nama_prodi')->unique();
            $datasets = [];

            foreach ($prodiList as $prodiName) {
                $data = [];
                foreach ($daerahData as $d) {
                    $item = $detailProdi->first(fn($row) => $row->kode_daerah == $d->kode_daerah && $row->nama_prodi == $prodiName);
                    $data[] = $item ? $item->total : 0;
                }

                if (array_sum($data) === 0) continue;

                $datasets[] = [
                    'label' => $prodiName,
                    'data' => $data,
                ];
            }

            return response()->json([
                'status' => 200,
                'jumlah_pengguna' => $counts->jumlah_pengguna,
                'jumlah_daerah' => $counts->jumlah_daerah,
                'jumlah_mahasiswa' => $counts->jumlah_mahasiswa,
                'jumlah_sekolah' => $counts->jumlah_sekolah,
                'jumlah_prodi' => $counts->jumlah_prodi,

                'daerahChart' => [
                    'labels' => $daerahData->pluck('nama_daerah'),
                    'values' => $daerahData->pluck('total_mahasiswa'),
                ],
                'sekolahChart' => [
                    'labels' => $sekolahTipe->pluck('jenis_sekolah'),
                    'values' => $sekolahTipe->pluck('total'),
                ],
                'daerahJurusanChart' => [
                    'labels' => $daerahData->pluck('nama_daerah'),
                    'datasets' => $datasets,
                ],
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



    public function pengaturanAkun()
    {
        $userSession = session('loggedInUser');

        if (!$userSession || !isset($userSession['user_uuid'])) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah habis, silakan login kembali.');
        }

        $user = User::where('user_uuid', $userSession['user_uuid'])->first();

        if (!$user) {
            return redirect()->route('auth.login')->with('error', 'User tidak ditemukan.');
        }

        return view('admin-dashboard.pengaturan', [
            'title' => 'USNIGIS | Pengaturan Akun',
            'status' => 'active',
            'user' => $user,
        ]);
    }

    public function pengaturanAkunStore(Request $request)
    {
        try {
            $userSession = session('loggedInUser');

            if (!$userSession || !isset($userSession['user_uuid'])) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Sesi Anda telah habis, silakan login kembali.',
                    'icon' => 'error'
                ], 401);
            }

            $user = User::where('user_uuid', $userSession['user_uuid'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User tidak ditemukan.',
                    'icon' => 'error'
                ], 404);
            }

            $validated = $request->validate(
                [
                    'nama_lengkap' => [
                        'required',
                        'string',
                        'min:5',
                        'max:100',
                        'regex:/^[a-zA-Z0-9\s.,-]+$/'
                    ],
                    'username' => 'required|string|min:5|max:50|regex:/^[a-zA-Z0-9\s.,-]+$/|unique:users,username,' . $user->user_uuid . ',user_uuid',
                    'email' => 'nullable|email|max:50|unique:users,email,' . $user->user_uuid . ',user_uuid',
                ],
                [
                    'nama_lengkap.required' => 'Nama lengkap harus diisi.',
                    'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
                    'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 100 karakter.',
                    'nama_lengkap.min' => 'Nama lengkap minimal 5 karakter.',
                    'nama_lengkap.regex' => 'Nama lengkap hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',

                    'username.required' => 'Username harus diisi.',
                    'username.string' => 'Username harus berupa teks.',
                    'username.regex' => 'Username hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',
                    'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                    'username.min' => 'Username minimal 5 karakter.',
                    'username.unique' => 'Username sudah digunakan.',

                    'email.email' => 'Format email tidak valid.',
                    'email.max' => 'Email tidak boleh lebih dari 50 karakter.',
                    'email.unique' => 'Email sudah digunakan.',
                ]
            );

            $emailSama = $user->email === ($validated['email'] ?? null);

            if (
                $user->nama_lengkap === $validated['nama_lengkap'] &&
                $user->username === $validated['username'] &&
                $emailSama
            ) {
                return response()->json([
                    'status' => 400,
                    'title' => 'Tidak Ada Perubahan',
                    'message' => 'Anda belum mengubah data apapun.',
                    'icon' => 'info'
                ], 400);
            }

            $user->update($validated);
            session()->put('loggedInUser.nama_lengkap', $validated['nama_lengkap']);

            return response()->json([
                'status' => 200,
                'title' => 'Berhasil!',
                'message' => 'Pengaturan akun berhasil diperbarui.',
                'icon' => 'success'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(),
                'icon' => 'error'
            ], 422);
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
