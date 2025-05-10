<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\MahasiswaImport;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
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
            'mahasiswa' => Mahasiswa::where('mahasiswa_uuid', $request->nim)->first(['nim', 'tahun_masuk', 'jurusan', 'sekolah_asal', 'daerah_asal'])
        ];

        if ($request->ajax()) {
            $mahasiswas = Mahasiswa::join('daerah', 'mahasiswa.daerah_asal', '=', 'daerah.kode_daerah')
                ->join('sekolah', 'mahasiswa.sekolah_asal', '=', 'sekolah.sekolah_id')
                ->join('jurusan', 'mahasiswa.jurusan', '=', 'jurusan.kode_jurusan')
                ->select(
                    'mahasiswa.mahasiswa_uuid',
                    'mahasiswa.nim',
                    'mahasiswa.tahun_masuk',
                    'jurusan.nama_jurusan as jurusan',
                    'sekolah.nama_sekolah as sekolah_asal',
                    'daerah.nama_daerah as daerah_asal',
                )
                ->orderBy('mahasiswa.mahasiswa_uuid', 'DESC')
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
        try {
            $daerah = Daerah::select(['kode_daerah', 'nama_daerah'])->get();
            $jurusan = Jurusan::select(['kode_jurusan', 'nama_jurusan'])->get();
            $sekolah = Sekolah::select(['sekolah_id', 'nama_sekolah'])->get();

            return response()->json([
                'status' => 200,
                'daerah' => $daerah,
                'jurusan' => $jurusan,
                'sekolah' => $sekolah,
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
            $validatedData = $request->validate(
                [
                    'nim'              => 'required|string|max:100|unique:mahasiswa,nim',
                    'tahun_masuk'      => 'required|string|max:4',
                    'jurusan'          => 'required|string|exists:jurusan,kode_jurusan',
                    'sekolah_asal'     => 'required|string|exists:sekolah,sekolah_id',
                    'daerah_asal'      => 'required|string|exists:daerah,kode_daerah',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'jurusan.exists' => 'Jurusan tidak valid.',
                    'sekolah_asal.exists' => 'Sekolah asal tidak valid.',
                    'daerah_asal.exists' => 'Daerah asal tidak valid.',
                ]
            );

            $mahasiswa_uuid = Str::uuid();

            DB::insert("
            INSERT INTO mahasiswa (
                mahasiswa_uuid, 
                nim, 
                tahun_masuk, 
                jurusan, 
                sekolah_asal, 
                daerah_asal, 
                created_at, 
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
                $mahasiswa_uuid,
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['jurusan'],
                $validatedData['sekolah_asal'],
                $validatedData['daerah_asal'],
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
            $request->validate(
                [
                    'import_mahasiswa' => 'required|file|mimes:xlsx,xls',
                ],
                [
                    'import_mahasiswa.mimes' => 'File harus berupa file Excel (xlsx, xls).',
                    'import_mahasiswa.required' => 'File Excel tidak boleh kosong.',
                ]
            );

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
    public function show($nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $nim)->select(
                'mahasiswa_uuid',
                'nim',
                'tahun_masuk',
                'jurusan',
                'sekolah_asal',
                'daerah_asal',
            )->firstOrFail();

            return response()->json([
                "status" => 200,
                'mahasiswa' => $mahasiswa,
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
    public function edit(Mahasiswa $mahasiswa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $nim)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'nim'              => 'required|string|max:100|unique:mahasiswa,nim,' . $mahasiswa->mahasiswa_uuid . ',mahasiswa_uuid',
                    'tahun_masuk'      => 'required|string|max:4',
                    'jurusan'          => 'required|string|exists:jurusan,kode_jurusan',
                    'sekolah_asal'     => 'required|string|exists:sekolah,sekolah_id',
                    'daerah_asal'      => 'required|string|exists:daerah,kode_daerah',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'jurusan.exists' => 'Jurusan tidak valid.',
                    'sekolah_asal.exists' => 'Sekolah asal tidak valid.',
                    'daerah_asal.exists' => 'Daerah asal tidak valid.',
                ]
            );

            DB::update("
            UPDATE mahasiswa SET 
                nim = ?, 
                tahun_masuk = ?, 
                jurusan = ?, 
                sekolah_asal = ?, 
                daerah_asal = ?, 
                updated_at = NOW()
            WHERE mahasiswa_uuid = ?
        ", [
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['jurusan'],
                $validatedData['sekolah_asal'],
                $validatedData['daerah_asal'],
                $nim
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
    public function destroy($nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $nim)->firstOrFail();

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
            DB::table('mahasiswa')->delete();

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
