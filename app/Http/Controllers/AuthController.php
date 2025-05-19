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
            'title' => 'Login Page',
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

            // Gunakan data tervalidasi dari $validatedData
            $user = User::where('username', $validatedData['username'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                return response()->json([
                    "status" => 401,
                    "title" => "Login Gagal",
                    "message" => "Username atau password salah.",
                    "icon" => "error"
                ], 401);
            }

            // Simpan data user ke session jika login berhasil
            $request->session()->put('loggedInUser', [
                'user_id' => $user->user_id,
                'nama_user' => $user->nama_user,
                'role' => $user->role
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
        session()->forget('loggedInUser');

        return response()->json([
            'status' => 200
        ]);
    }
}
