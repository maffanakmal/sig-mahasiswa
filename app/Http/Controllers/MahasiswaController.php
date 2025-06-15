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
            'mahasiswa' => Mahasiswa::where('mahasiswa_uuid', $request->nim)->first(['nim', 'tahun_masuk', 'kode_jurusan', 'npsn', 'kode_daerah'])
        ];

        if ($request->ajax()) {
            $mahasiswas = Mahasiswa::leftJoin('daerah', 'mahasiswa.kode_daerah', '=', 'daerah.kode_daerah')
                ->leftJoin('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
                ->leftJoin('jurusan', 'mahasiswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
                ->select(
                    'mahasiswa.mahasiswa_uuid',
                    'mahasiswa.nim',
                    'mahasiswa.tahun_masuk',
                    'jurusan.nama_jurusan as kode_jurusan',
                    'sekolah.nama_sekolah as npsn',
                    'daerah.nama_daerah as kode_daerah',
                )
                ->orderBy('mahasiswa.mahasiswa_uuid', 'DESC')
                ->get();


            return DataTables::of($mahasiswas)
                ->addIndexColumn()
                ->editColumn('kode_jurusan', function ($row) {
                    return $row->kode_jurusan ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('npsn', function ($row) {
                    return $row->npsn ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('kode_daerah', function ($row) {
                    return $row->kode_daerah ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('action', function ($mahasiswa) {
                    return '<button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-warning btn-sm" onclick="editMahasiswa(this)">
                    <i class="bx bx-pencil"></i>
                </button>
                <button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-danger btn-sm" onclick="deleteMahasiswa(this)">
                    <i class="bx bx-trash"></i>
                </button>';
                })
                ->rawColumns(['action', 'kode_jurusan', 'npsn', 'kode_daerah']) // penting agar HTML-nya dirender
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
            $sekolah = Sekolah::select(['npsn', 'nama_sekolah'])->get();

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
                    'tahun_masuk'      => 'required|digits:4',
                    'kode_jurusan'          => 'required|string|exists:jurusan,kode_jurusan',
                    'npsn'     => 'required|string|exists:sekolah,nspn',
                    'kode_daerah'      => 'required|string|exists:daerah,kode_daerah',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'kode_jurusan.exists' => 'Jurusan tidak valid.',
                    'npsn.exists' => 'Sekolah asal tidak valid.',
                    'kode_daerah.exists' => 'Daerah asal tidak valid.',
                    'tahun_masuk.digits' => 'Tahun masuk harus terdiri dari 4 digit.',
                ]
            );

            $mahasiswa_uuid = Str::uuid();

            DB::insert("
            INSERT INTO mahasiswa (
                mahasiswa_uuid, 
                nim, 
                tahun_masuk, 
                kode_jurusan, 
                npsn, 
                kode_daerah
            ) VALUES (?, ?, ?, ?, ?, ?)
        ", [
                $mahasiswa_uuid,
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['kode_jurusan'],
                $validatedData['npsn'],
                $validatedData['kode_daerah'],
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
                'kode_jurusan',
                'npsn',
                'kode_daerah',
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
                    'tahun_masuk'      => 'required|digits:4',
                    'kode_jurusan'          => 'required|string|exists:jurusan,kode_jurusan',
                    'npsn'     => 'required|string|exists:sekolah,sekolah_id',
                    'kode_daerah'      => 'required|string|exists:daerah,kode_daerah',
                ],
                [
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'kode_jurusan.exists' => 'Jurusan tidak valid.',
                    'npsn.exists' => 'Sekolah asal tidak valid.',
                    'kode_jurusan.exists' => 'Daerah asal tidak valid.',
                    'tahun_masuk.digits' => 'Tahun masuk harus terdiri dari 4 digit.',
                ]
            );

            DB::update("
            UPDATE mahasiswa SET 
                nim = ?, 
                tahun_masuk = ?, 
                kode_jurusan = ?, 
                npsn = ?, 
                kode_daerah = ?
            WHERE mahasiswa_uuid = ?
        ", [
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['kode_jurusan'],
                $validatedData['npsn'],
                $validatedData['kode_daerah'],
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
            $count = DB::table('mahasiswa')->count();

            if ($count === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Tidak Ada Data",
                    "message" => "Tidak ada data mahasiswa yang dapat dihapus.",
                    "icon" => "warning"
                ]);
            }

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
