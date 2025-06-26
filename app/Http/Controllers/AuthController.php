<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth-page.login', [
            'title' => 'USNIGIS | Halaman Login',
        ]);
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:50',
                'password' => 'required|string|min:5|max:60',
            ], [
                'username.required' => 'Username wajib diisi.',
                'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 5 karakter.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',
            ]);

            $user = User::where('username', $validatedData['username'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                return response()->json([
                    "status" => 401,
                    "title" => "Login Gagal",
                    "message" => "Username atau password salah.",
                    "icon" => "error"
                ], 401);
            }

            if ($user->is_active) {
                return response()->json([
                    "status" => 403,
                    "title" => "Akun Sedang Online",
                    "message" => "Akun ini sedang digunakan di perangkat lain.",
                    "icon" => "warning"
                ], 403);
            }

            $user->is_active = 1;
            $user->save();

            $request->session()->put('loggedInUser', [
                'user_uuid' => $user->user_uuid,
                'nama_user' => $user->nama_user,
                'role' => $user->role,
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Login Berhasil",
                "message" => "Selamat datang, $user->username!",
                "icon" => "success"
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "title" => "Internal Server Error",
                "message" => $e->getMessage(),
                "icon" => "error"
            ], 500);
        }
    }

    public function logout()
    {
        $loggedInUser = session('loggedInUser');

        if ($loggedInUser) {
            $user = User::where('user_uuid', $loggedInUser['user_uuid'])->first();
            if ($user) {
                $user->is_active = 0;
                $user->save();
            }
        }

        session()->forget('loggedInUser');

        return response()->json([
            'status' => 200
        ]);
    }

    public function resetPassword()
    {
        return view('auth-page.reset-password', [
            'title' => 'USNIGIS | Halaman Reset Password',
        ]);
    }

    public function formResetPassword()
    {
        return view('auth-page.form-reset', [
            'title' => 'USNIGIS | Halaman Reset Password',
        ]);
    }
}
