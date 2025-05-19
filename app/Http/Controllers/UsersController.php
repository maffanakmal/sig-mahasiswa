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
            'title' => 'Pengguna Page',
            'pengguna' => User::where('user_uuid', $request->user_id)->first([
                'user_uuid',
                'nama_user',
                'username',
                'role',
            ]),
        ];

        if ($request->ajax()) {
            $users = User::select(
                'user_uuid',
                'nama_user',
                'username',
                'role',
            )
                ->orderBy('user_uuid', 'DESC')
                ->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function ($user) {
                    return '<button data-id="' . $user->user_uuid . '" class="btn btn-warning btn-sm" onclick="editUser(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $user->user_uuid . '" class="btn btn-danger btn-sm" onclick="deleteUser(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('admin-dashboard.pengguna', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $role = ['BAAKPSI', 'Warek 3', 'PMB'];

            return response()->json([
                'status' => 200,
                'role' => $role,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_user' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z0-9\s.,-]+$/'
                ],
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:5|max:60|confirmed',
                'role' => 'required|in:BAAKPSI,Warek 3,PMB',
            ], [
                'nama_user.required' => 'Nama user tidak boleh kosong.',
                'nama_user.regex' => 'Nama user tidak boleh mengandung karakter khusus.',
                'nama_user.max' => 'Nama user tidak boleh lebih dari 100 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.unique' => 'Username sudah digunakan.',
                'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 5 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',
                'role.required' => 'Role harus dipilih.',
                'role.in' => 'Role yang dipilih tidak valid.',
            ]);

            $user_uuid = Str::uuid();

            DB::insert("
            INSERT INTO users (
                user_uuid, 
                nama_user,
                username,
                password,
                role
            ) VALUES (?, ?, ?, ?, ?)
        ", [
                $user_uuid,
                $validatedData['nama_user'],
                $validatedData['username'],
                Hash::make($validatedData['password']),
                $validatedData['role'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data pengguna berhasil ditambahkan.",
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
    public function show($user_id)
    {
        try {
            $user = User::where('user_uuid', $user_id)->select(
                'user_uuid',
                'nama_user',
                'username',
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
    public function update(Request $request, $user_id)
    {
        try {
            $user = User::where('user_uuid', $user_id)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate([
                'nama_user' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z0-9\s.,-]+$/'
                ],
                'username' => 'required|string|max:50|unique:users,username,' . $user->user_uuid . ',user_uuid',
                'password' => 'nullable|string|min:5|max:60|confirmed', // jadikan nullable
                'role' => 'required|in:BAAKPSI,Warek 3,PMB',
            ], [
                'nama_user.required' => 'Nama user tidak boleh kosong.',
                'nama_user.regex' => 'Nama user tidak boleh mengandung karakter khusus.',
                'nama_user.max' => 'Nama user tidak boleh lebih dari 100 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.unique' => 'Username sudah digunakan.',
                'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.max' => 'Password tidak boleh lebih dari 60 karakter.',
                'role.required' => 'Role harus dipilih.',
                'role.in' => 'Role yang dipilih tidak valid.',
            ]);


            $password = $validatedData['password'] ?? null;

            if ($password) {
                // Jika password baru diisi, gunakan yang baru dan hash
                $hashedPassword = Hash::make($password);
            } else {
                // Jika password tidak diisi, ambil password lama dari database
                $hashedPassword = $user->password;
            }

            DB::update("
                UPDATE users SET 
                    nama_user = ?, 
                    username = ?,
                    password = ?, 
                    role = ?,
                WHERE user_uuid = ?
            ", [
                $validatedData['nama_user'],
                $validatedData['username'],
                $hashedPassword,
                $validatedData['role'],
                $user_id
            ]);


            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data user berhasil diperbarui.",
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
     * Remove the specified resource from storage.
     */
    public function destroy($user_id)
    {
        try {
            $user = User::where('user_uuid', $user_id)->firstOrFail();

            if ($user) {
                $user->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
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
