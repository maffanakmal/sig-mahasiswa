<?php

namespace App\Http\Controllers;

use App\Imports\JurusanImport;
use Exception;
use App\Models\Jurusan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class JurusanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'USNIGIS | Halaman Program Studi',
            'prodi' => Jurusan::where('prodi_uuid', $request->kode_prodi)->first([
                'kode_prodi',
                'nama_prodi',
            ]),
        ];

        if ($request->ajax()) {
            $prodis = Jurusan::select(
                'prodi_uuid',
                'kode_prodi',
                'nama_prodi',
            )
                ->orderBy('prodi_uuid', 'DESC')
                ->get();

            return DataTables::of($prodis)
                ->addIndexColumn()
                ->addColumn('action', function ($prodi) {
                    return '<button data-id="' . $prodi->prodi_uuid . '" class="btn btn-warning btn-sm" onclick="editProdi(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $prodi->prodi_uuid . '" class="btn btn-danger btn-sm" onclick="deleteProdi(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('admin-dashboard.jurusan', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                [
                    'kode_prodi' => 'required|numeric|regex:/^[a-zA-Z0-9\s.,-]+$/|unique:prodi,kode_prodi',
                    'nama_prodi' => 'required|string|max:50',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'nama_prodi.required' => 'Nama daerah harus diisi.',
                    'nama_prodi.string' => 'Nama daerah harus berupa string.',
                    'nama_prodi.max' => 'Nama daerah tidak boleh lebih dari 50 karakter.',
                ]
            );

            $prodi_uuid = Str::uuid();

            DB::insert("
            INSERT INTO prodi (
                prodi_uuid, 
                kode_prodi,
                nama_prodi
            ) VALUES (?, ?, ?)
        ", [
                $prodi_uuid,
                $validatedData['kode_prodi'],
                $validatedData['nama_prodi'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data program studi berhasil ditambahkan.",
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

    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_prodi' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'import_prodi.mimes' => 'File harus berupa file Excel (xlsx, xls).',
                'import_prodi.required' => 'Form input excel tidak boleh kosong.'
            ]);

            // Import the Excel file
            Excel::import(new JurusanImport, $request->file('import_prodi'));

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data program studi berhasil diimpor.",
                "icon" => "success"
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle general exceptions
            return response()->json([
                "status" => 500,
                "title" => "Internal Server Error",
                "message" => $e->getMessage(),
                "icon" => "error"
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($prodi_uuid)
    {
        try {
            $prodi = Jurusan::where('prodi_uuid', $prodi_uuid)->select(
                'kode_prodi',
                'nama_prodi',
            )->firstOrFail();

            return response()->json([
                "status" => 200,
                'prodi' => $prodi,
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
    public function update(Request $request, $prodi_uuid)
    {
        try {
            $prodi = Jurusan::where('prodi_uuid', $prodi_uuid)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'kode_prodi'      => 'required|numeric|regex:/^[a-zA-Z0-9\s.,-]+$/|unique:prodi,kode_prodi,' . $prodi->prodi_uuid . ',prodi_uuid',
                    'nama_prodi'      => 'required|string|max:50',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'nama_prodi.required' => 'Nama daerah harus diisi.',
                    'nama_prodi.string' => 'Nama daerah harus berupa string.',
                    'nama_prodi.max' => 'Nama daerah tidak boleh lebih dari 50 karakter.',
                ]
            );

            $dataTidakBerubah =
                $prodi->kode_prodi == $validatedData['kode_prodi'] &&
                $prodi->nama_prodi === $validatedData['nama_prodi'];

            if ($dataTidakBerubah) {
                return response()->json([
                    'status' => 400,
                    'title' => 'Tidak Ada Perubahan',
                    'message' => 'Data pengguna tidak mengalami perubahan.',
                    'icon' => 'info'
                ], 400);
            }

            DB::update("
            UPDATE prodi SET 
                kode_prodi = ?, 
                nama_prodi = ?
            WHERE prodi_uuid = ?
        ", [
                $validatedData['kode_prodi'],
                $validatedData['nama_prodi'],
                $prodi_uuid
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data program studi berhasil diperbarui.",
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
    public function destroy($prodi_uuid)
    {
        try {
            $prodi = Jurusan::where('prodi_uuid', $prodi_uuid)->firstOrFail();

            if ($prodi) {
                $prodi->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data program studi berhasil dihapus.",
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

    public function destroyAll()
    {
        try {
            $count = DB::table('prodi')->count();

            if ($count === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Tidak Ada Data",
                    "message" => "Tidak ada data program studi yang dapat dihapus.",
                    "icon" => "warning"
                ]);
            }

            DB::table('prodi')->delete();

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Semua data program studi berhasil dihapus.",
                "icon" => "success"
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
