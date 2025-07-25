<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'USNIGIS | Halaman Pengguna',
            'pengguna' => User::where('user_uuid', $request->user_id)->first([
                'user_uuid',
                'nama_lengkap',
                'username',
                'email',
                'role',
                'is_active',
            ]),
        ];

        if ($request->ajax()) {
            $users = User::select(
                'user_uuid',
                'nama_lengkap',
                'username',
                'email',
                'role',
                'is_active'
            )
                ->orderBy('user_uuid', 'DESC')
                ->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->editColumn('status', function ($user) {
                    return $user->is_active
                        ? '<span class="badge bg-success">Online</span>'
                        : '<span class="badge bg-danger">Offline</span>';
                })
                ->editColumn('email', function ($user) {
                    return $user->email ? $user->email : '<span class="text-muted">Tidak ada email</span>';
                })
                ->addColumn('action', function ($user) {
                    $disabled = $user->is_active ? 'disabled' : '';

                    return '<button data-id="' . $user->user_uuid . '" class="btn btn-warning btn-sm" onclick="editUser(this)" ' . $disabled . '>
                                <box-icon type="solid" name="pencil" class="icon-crud" color="white"></box-icon>
                            </button>
                            <button data-id="' . $user->user_uuid . '" class="btn btn-danger btn-sm" onclick="deleteUser(this)" ' . $disabled . '>
                                <box-icon type="solid" name="trash" class="icon-crud" color="white"></box-icon>
                            </button>';
                })
                ->rawColumns(['action', 'email','status'])
                ->make(true);
        }


        return view('admin-dashboard.pengguna', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_lengkap' => 'required|string|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:100',
                'username' => 'required|string|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:50|unique:users,username',
                'email' => 'nullable|email|max:50|unique:users,email',
                'password' => 'required|string|min:5|max:60',
                'confirm_password' => 'required_with:password|same:password',
                'role' => 'required|in:BAAKPSI,Warek 3,PMB',
            ], [
                'nama_lengkap.required' => 'Nama lengkap harus diisi.',
                'nama_lengkap.regex' => 'Nama lengkap hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',
                'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 100 karakter.',
                'nama_lengkap.min' => 'Nama lengkap minimal 5 karakter.',

                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 50 karakter.',
                'email.unique' => 'Email sudah digunakan.',

                'username.required' => 'Username harus diisi.',
                'username.unique' => 'Username sudah digunakan.',
                'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                'username.min' => 'Username minimal 5 karakter.',
                'username.regex' => 'Username hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',

                'password.required' => 'Password harus diisi.',
                'password.min' => 'Password minimal 5 karakter.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',

                'confirm_password.required_with' => 'Konfirmasi password harus diisi jika password diisi.',
                'confirm_password.same' => 'Konfirmasi password harus sama dengan password.',

                'role.required' => 'Role harus dipilih.',
                'role.in' => 'Role yang dipilih tidak valid.',
            ]);

            $user_uuid = Str::uuid();

            DB::insert("
    INSERT INTO users (
        user_uuid, 
        nama_lengkap,
        username,
        email,
        password,
        role,
        updated_at,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
", [
                $user_uuid,
                $validatedData['nama_lengkap'],
                $validatedData['username'],
                $validatedData['email'] ?? null,
                Hash::make($validatedData['password']),
                $validatedData['role'],
                now(),
                now()
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
                "message" => "Data pengguna baru berhasil ditambahkan.",
                "icon" => "success"
            ], 200);
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

    /**
     * Display the specified resource.
     */
    public function show($user_uuid)
    {
        try {
            $user = User::where('user_uuid', $user_uuid)->select(
                'nama_lengkap',
                'username',
                'email',
                'password',
                'role'
            )->firstOrFail();

            return response()->json([
                "status" => 200,
                'user' => $user,
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $user_uuid)
    {
        try {
            $user = User::where('user_uuid', $user_uuid)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate([
                'nama_lengkap' => 'required|string|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:100',
                'username' => 'required|string|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:50|unique:users,username,' . $user->user_uuid . ',user_uuid',
                'email' => 'nullable|email|max:50|unique:users,email,' . $user->user_uuid . ',user_uuid',
                'password' => 'nullable|string|min:5|max:60',
                'confirm_password' => 'required_with:password|same:password',
                'role' => 'required|in:BAAKPSI,Warek 3,PMB',
            ],[
                'nama_lengkap.required' => 'Nama lengkap harus diisi.',
                'nama_lengkap.regex' => 'Nama lengkap hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',
                'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 100 karakter.',
                'nama_lengkap.min' => 'Nama lengkap minimal 5 karakter.',

                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 50 karakter.',
                'email.unique' => 'Email sudah digunakan.',

                'username.required' => 'Username harus diisi.',
                'username.unique' => 'Username sudah digunakan.',
                'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                'username.min' => 'Username minimal 5 karakter.',
                'username.regex' => 'Username hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung',

                'password.min' => 'Password minimal 5 karakter.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',

                'confirm_password.required_with' => 'Konfirmasi password harus diisi jika password diisi.',
                'confirm_password.same' => 'Konfirmasi password harus sama dengan password.',

                'role.required' => 'Role harus dipilih.',
                'role.in' => 'Role yang dipilih tidak valid.',
            ]);

            $password = $validatedData['password'] ?? null;
            $hashedPassword = $password ? Hash::make($password) : $user->password;

            $dataTidakBerubah =
                $user->nama_lengkap === $validatedData['nama_lengkap'] &&
                $user->username === $validatedData['username'] &&
                $user->email === ($validatedData['email'] ?? null) &&
                $user->role === $validatedData['role'] &&
                $user->password === $hashedPassword;

            if ($dataTidakBerubah) {
                return response()->json([
                    'status' => 400,
                    'title' => 'Tidak Ada Perubahan',
                    'message' => 'Data pengguna tidak mengalami perubahan.',
                    'icon' => 'info'
                ], 400);
            }

            // Update manual
            DB::update("
            UPDATE users SET 
                nama_lengkap = ?, 
                username = ?,
                email = ?,
                password = ?, 
                role = ?,
                updated_at = ?
            WHERE user_uuid = ?
        ", [
                $validatedData['nama_lengkap'],
                $validatedData['username'],
                $validatedData['email'] ?? null,
                $hashedPassword,
                $validatedData['role'],
                now(),
                $user->user_uuid
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
                "message" => "Data pengguna berhasil diperbarui.",
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($user_uuid)
    {
        try {
            $user = User::where('user_uuid', $user_uuid)->firstOrFail();

            if ($user) {
                $user->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Berhasil!",
                    "message" => "Data pengguna berhasil dihapus.",
                    "icon" => "success"
                ]);
            }
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
