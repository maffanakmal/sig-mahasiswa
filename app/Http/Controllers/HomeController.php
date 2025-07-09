<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

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

            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username,' . $user->user_uuid . ',user_uuid',
                'email' => 'nullable|email|max:100|unique:users,email,' . $user->user_uuid . ',user_uuid',
            ]);

            // 💡 Cek apakah data tidak berubah
            if (
                $user->nama_lengkap === $validated['nama_lengkap'] &&
                $user->username === $validated['username'] &&
                $user->email === $validated['email']
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
