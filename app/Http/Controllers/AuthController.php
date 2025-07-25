<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\lupaPassword;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
                'credentials' => 'required|string|min:5|max:50',
                'password' => 'required|string|min:5|max:60',
            ], [
                'credentials.required' => 'Username atau email wajib diisi.',
                'credentials.max' => 'Username/email tidak boleh lebih dari 50 karakter.',
                'credentials.min' => 'Username/email minimal 5 karakter.',
                'credentials.string' => 'Username/email harus berupa teks.',

                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 5 karakter.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',
            ]);

            $loginField = filter_var($validatedData['credentials'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $user = User::where($loginField, $validatedData['credentials'])->first();

            if (!$user) {
                return response()->json([
                    "status" => 404,
                    "title" => "Akun Tidak Ditemukan",
                    "message" => "Akun dengan username/email tersebut tidak terdaftar.",
                    "icon" => "error"
                ], 404);
            }

            if (!Hash::check($validatedData['password'], $user->password)) {
                return response()->json([
                    "status" => 401,
                    "title" => "Login Gagal",
                    "message" => "Password yang dimasukkan salah.",
                    "icon" => "error"
                ], 401);
            }

            $timeoutMinutes = 30;

            if ($user->is_active && now()->diffInMinutes($user->last_active) < $timeoutMinutes) {
                return response()->json([
                    "status" => 403,
                    "title" => "Akun Sedang Online",
                    "message" => "Akun ini sedang digunakan di perangkat lain.",
                    "icon" => "warning"
                ], 403);
            }

            // Update status aktif dan last active
            $user->is_active = 1;
            $user->last_active = now();
            $user->timestamps = false;
            $user->save();

            // Simpan session
            $request->session()->put('loggedInUser', [
                'user_uuid' => $user->user_uuid,
                'nama_lengkap' => $user->nama_lengkap,
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


    public function logout(Request $request)
    {
        if ($request->isMethod('post')) {
            $loggedInUser = session('loggedInUser');

            if ($loggedInUser) {
                $user = User::where('user_uuid', $loggedInUser['user_uuid'])->first();
                if ($user) {
                    $user->is_active = 0;
                    $user->last_active = null;
                    $user->timestamps = false;
                    $user->save();
                }
            }

            session()->forget('loggedInUser');
            session()->invalidate();
            session()->regenerateToken();

            return response()->json([
                "status" => 200,
                "title" => "Logout Berhasil",
                "message" => "Anda telah keluar dari sistem.",
                "icon" => "success"
            ]);
        }

        return response()->json([
            "status" => 405,
            "title" => "Metode tidak diizinkan",
            "message" => "Hanya POST yang diperbolehkan.",
            "icon" => "error"
        ], 405);
    }


    public function validateEmail()
    {
        return view('auth-page.reset-password', [
            'title' => 'USNIGIS | Halaman Validasi Email',
        ]);
    }

    public function authEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|max:50',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 50 karakter.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    "status" => 404,
                    "title" => "Email Tidak Ditemukan",
                    "message" => "Email yang Anda masukkan tidak terdaftar.",
                    "icon" => "error"
                ], 404);
            }

            $resetToken = Str::uuid();

            $user->update([
                'reset_token' => $resetToken,
                'token_expire' => Carbon::now()->addMinutes(30)->toDateTimeString()
            ]);

            $details = [
                "body" => route('auth.reset.password', [
                    'email' => $user->email,
                    'reset_token' => $resetToken
                ])
            ];

            Mail::to($user->email)->send(new lupaPassword($details));

            return response()->json([
                "status" => 200,
                "title" => "Validasi Berhasil",
                "message" => "Permintaan reset password telah dikirim ke email Anda.",
                "icon" => "success"
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(),
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

    public function resetPassword($email, $reset_token)
    {
        $user = User::where('email', $email)
            ->where('reset_token', $reset_token)
            ->where('token_expire', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect()->route('auth.validate.email')->withErrors([
                'error' => 'Token reset password tidak valid atau telah kedaluwarsa.'
            ]);
        }

        return view('auth-page.form-reset', [
            'title' => 'USNIGIS | Halaman Reset Password',
            'email' => $email,
            'reset_token' => $reset_token
        ]);
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|max:50',
                'reset_token' => 'required|string|max:36',
                'password' => 'required|string|min:5|max:60',
                'confirm_password' => 'required_with:password|same:password',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 50 karakter.',

                'reset_token.required' => 'Token reset wajib diisi.',
                'reset_token.max' => 'Token reset tidak boleh lebih dari 36 karakter.',

                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 5 karakter.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',
                
                'confirm_password.required_with' => 'Konfirmasi password harus diisi jika password diisi.',
                'confirm_password.same' => 'Konfirmasi password harus sama dengan password.',
            ]);

            $user = User::where('email', $request->email)
                ->where('reset_token', $request->reset_token)
                ->where('token_expire', '>', Carbon::now())
                ->first();

            if (!$user) {
                return response()->json([
                    "status" => 404,
                    "title" => "Token Tidak Valid",
                    "message" => "Token reset password tidak valid atau telah kedaluwarsa.",
                    "icon" => "error"
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->password),
                'reset_token' => null,
                'token_expire' => null
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Reset Password Berhasil",
                "message" => "Password Anda telah berhasil diubah.",
                "icon" => "success"
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(),
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
