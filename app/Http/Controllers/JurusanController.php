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
            'title' => 'Program Studi Page',
            'jurusan' => Jurusan::where('jurusan_uuid', $request->kode_jurusan)->first([
                'jurusan_uuid',
                'kode_jurusan',
                'nama_jurusan',
            ]),
        ];

        if ($request->ajax()) {
            $jurusans = Jurusan::select(
                'jurusan_uuid',
                'kode_jurusan',
                'nama_jurusan',
            )
                ->orderBy('jurusan_uuid', 'DESC')
                ->get();

            return DataTables::of($jurusans)
                ->addIndexColumn()
                ->addColumn('action', function ($jurusan) {
                    return '<button data-id="' . $jurusan->jurusan_uuid . '" class="btn btn-warning btn-sm" onclick="editJurusan(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $jurusan->jurusan_uuid . '" class="btn btn-danger btn-sm" onclick="deleteJurusan(this)">
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
                    'kode_jurusan' => 'required|numeric|regex:/^[a-zA-Z0-9\s.,-]+$/|unique:jurusan,kode_jurusan',
                    'nama_jurusan' => 'required|string|max:100',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'nama_jurusan.required' => 'Nama daerah harus diisi.',
                    'nama_jurusan.string' => 'Nama daerah harus berupa string.',
                    'nama_jurusan.max' => 'Nama daerah tidak boleh lebih dari 100 karakter.',
                ]
            );

            $jurusan_uuid = Str::uuid();

            DB::insert("
            INSERT INTO jurusan (
                jurusan_uuid, 
                kode_jurusan,
                nama_jurusan
            ) VALUES (?, ?, ?)
        ", [
                $jurusan_uuid,
                $validatedData['kode_jurusan'],
                $validatedData['nama_jurusan'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data jurusan berhasil ditambahkan.",
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
                'import_jurusan' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'import_jurusan.mimes' => 'File harus berupa file Excel (xlsx, xls).',
                'import_jurusan.required' => 'Form input excel tidak boleh kosong.'
            ]);

            // Import the Excel file
            Excel::import(new JurusanImport, $request->file('import_jurusan'));

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data jurusan berhasil diimpor.",
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
    public function show($kode_jurusan)
    {
        try {
            $jurusan = Jurusan::where('jurusan_uuid', $kode_jurusan)->select(
                'jurusan_uuid',
                'kode_jurusan',
                'nama_jurusan',
            )->firstOrFail();

            return response()->json([
                "status" => 200,
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

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, $kode_jurusan)
    {
        try {
            $jurusan = Jurusan::where('jurusan_uuid', $kode_jurusan)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'kode_jurusan'      => 'required|numeric|regex:/^[a-zA-Z0-9\s.,-]+$/|unique:jurusan,kode_jurusan,' . $jurusan->jurusan_uuid . ',jurusan_uuid',
                    'nama_jurusan'      => 'required|string|max:100',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'nama_jurusan.required' => 'Nama daerah harus diisi.',
                    'nama_jurusan.string' => 'Nama daerah harus berupa string.',
                    'nama_jurusan.max' => 'Nama daerah tidak boleh lebih dari 100 karakter.',
                ]
            );

            DB::update("
            UPDATE jurusan SET 
                kode_jurusan = ?, 
                nama_jurusan = ?
            WHERE jurusan_uuid = ?
        ", [
                $validatedData['kode_jurusan'],
                $validatedData['nama_jurusan'],
                $kode_jurusan
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data jurusan berhasil diperbarui.",
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
    public function destroy($kode_jurusan)
    {
        try {
            $jurusan = Jurusan::where('jurusan_uuid', $kode_jurusan)->firstOrFail();

            if ($jurusan) {
                $jurusan->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data jurusan berhasil dihapus.",
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
            $count = DB::table('jurusan')->count();

            if ($count === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Tidak Ada Data",
                    "message" => "Tidak ada data jurusan yang dapat dihapus.",
                    "icon" => "warning"
                ]);
            }

            DB::table('jurusan')->delete();

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Semua data jurusan berhasil dihapus.",
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
