<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\MahasiswaImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'Mahasiswa Page',
            'mahasiswa' => Mahasiswa::where('mahasiswa_uuid', $request->mahasiswa_id)->first(['nama_mahasiswa', 'nim', 'tahun_masuk', 'jurusan', 'sekolah_asal', 'daerah_asal', 'status_mahasiswa'])
        ];

        if ($request->ajax()) {
            $mahasiswas = Mahasiswa::select(
                'mahasiswa_uuid',
                'nama_mahasiswa',
                'nim',
                'tahun_masuk',
                'jurusan',
                'sekolah_asal',
                'daerah_asal',
                'status_mahasiswa'
            )
                ->orderBy('mahasiswa_uuid', 'DESC')
                ->get();

            return DataTables::of($mahasiswas)
                ->addIndexColumn()
                ->addColumn('action', function ($mahasiswa) {
                    return '<button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-warning btn-sm" onclick="editMahasiswa(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-danger btn-sm" onclick="deleteMahasiswa(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('admin-dashboard.mahasiswa', $data);
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
                    'nama_mahasiswa'   => 'required|string|max:255',
                    'nim'              => 'required|string|max:100|unique:mahasiswa,nim',
                    'tahun_masuk'      => 'required|number|max:4',
                    'jurusan'          => 'required|string|max:255',
                    'sekolah_asal'     => 'required|string|max:255',
                    'daerah_asal'      => 'required|string|max:255',
                    'status_mahasiswa' => 'required|string|max:100',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                ]
            );

            $mahasiswa_uuid = Str::uuid();

            DB::insert("
            INSERT INTO mahasiswa (
                mahasiswa_uuid, 
                nama_mahasiswa, 
                nim, 
                tahun_masuk, 
                jurusan, 
                sekolah_asal, 
                daerah_asal, 
                status_mahasiswa, 
                created_at, 
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
                $mahasiswa_uuid,
                $validatedData['nama_mahasiswa'],
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['jurusan'],
                $validatedData['sekolah_asal'],
                $validatedData['daerah_asal'],
                $validatedData['status_mahasiswa']
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data mahasiswa berhasil ditambahkan.",
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
                'import_mahasiswa' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'import_mahasiswa.mimes' => 'File harus berupa file Excel (xlsx, xls).',
            ]);

            // Import the Excel file
            Excel::import(new MahasiswaImport, $request->file('import_mahasiswa'));

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data mahasiswa berhasil diimpor.",
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
    public function show($mahasiswa_id)
    {
        $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_id)->select(
            'mahasiswa_uuid',
            'nama_mahasiswa',
            'nim',
            'tahun_masuk',
            'jurusan',
            'sekolah_asal',
            'daerah_asal',
            'status_mahasiswa',
        )->firstOrFail();

        return response()->json([
            'status' => 200,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $mahasiswa_id)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_id)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'nama_mahasiswa'   => 'required|string|max:255',
                    'nim'              => 'required|string|max:100|unique:mahasiswa,nim,' . $mahasiswa->mahasiswa_uuid . ',mahasiswa_uuid',
                    'tahun_masuk'      => 'required|string|max:4',
                    'jurusan'          => 'required|string|max:255',
                    'sekolah_asal'     => 'required|string|max:255',
                    'daerah_asal'      => 'required|string|max:255',
                    'status_mahasiswa' => 'required|string|max:100',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                ]
            );

            DB::update("
            UPDATE mahasiswa SET 
                nama_mahasiswa = ?, 
                nim = ?, 
                tahun_masuk = ?, 
                jurusan = ?, 
                sekolah_asal = ?, 
                daerah_asal = ?, 
                status_mahasiswa = ?,
                updated_at = NOW()
            WHERE mahasiswa_uuid = ?
        ", [
                $validatedData['nama_mahasiswa'],
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['jurusan'],
                $validatedData['sekolah_asal'],
                $validatedData['daerah_asal'],
                $validatedData['status_mahasiswa'],
                $mahasiswa_id
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data mahasiswa berhasil diperbarui.",
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
    public function destroy($mahasiswa_id)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_id)->firstOrFail();

            if ($mahasiswa) {
                $mahasiswa->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data mahasiswa berhasil dihapus.",
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
            DB::table('mahasiswa')->truncate();

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Semua data mahasiswa berhasil dihapus.",
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
