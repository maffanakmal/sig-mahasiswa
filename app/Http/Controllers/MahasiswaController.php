<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\MahasiswaExport;
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
            'title' => 'USNIGIS | Halaman Mahasiswa',
            'mahasiswa' => Mahasiswa::where('mahasiswa_uuid', $request->nim)->first(['nim', 'tahun_masuk', 'kode_prodi', 'npsn', 'kode_daerah']),
            'jumlahTidakLengkap' => Mahasiswa::whereNull('kode_daerah')
                ->orWhereNull('npsn')
                ->orWhereNull('kode_prodi')
                ->count(),
        ];

        if ($request->ajax()) {
            $mahasiswas = Mahasiswa::leftJoin('daerah', 'mahasiswa.kode_daerah', '=', 'daerah.kode_daerah')
            ->leftJoin('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
            ->leftJoin('prodi', 'mahasiswa.kode_prodi', '=', 'prodi.kode_prodi')
            ->select(
                'mahasiswa.mahasiswa_uuid',
                'mahasiswa.nim',
                'mahasiswa.tahun_masuk',
                'prodi.nama_prodi as kode_prodi',
                'sekolah.nama_sekolah as npsn',
                'daerah.nama_daerah as kode_daerah',
            )
            ->orderBy('mahasiswa.mahasiswa_uuid', 'DESC')
            ->get();

            return DataTables::of($mahasiswas)
                ->addIndexColumn()
                ->editColumn('kode_prodi', function ($row) {
                    return $row->kode_prodi ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('npsn', function ($row) {
                    return $row->npsn ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('kode_daerah', function ($row) {
                    return $row->kode_daerah ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('action', function ($mahasiswa) {
                    return '<button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-warning btn-sm" onclick="editMahasiswa(this)">
                    <box-icon type="solid" name="pencil" class="icon-crud" color="white"></box-icon>
                </button>
                <button data-id="' . $mahasiswa->mahasiswa_uuid . '" class="btn btn-danger btn-sm" onclick="deleteMahasiswa(this)">
                    <box-icon type="solid" name="trash" class="icon-crud" color="white"></box-icon>
                </button>';
                })
                ->rawColumns(['action', 'kode_prodi', 'npsn', 'kode_daerah']) // penting agar HTML-nya dirender
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
            $prodi = Jurusan::select(['kode_prodi', 'nama_prodi'])->get();
            $sekolah = Sekolah::select(['npsn', 'nama_sekolah'])->get();

            return response()->json([
                'status' => 200,
                'daerah' => $daerah,
                'prodi' => $prodi,
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
                    'nim' => 'required|regex:/^[0-9]+$/|min:9|max:10|unique:mahasiswa,nim',
                    'tahun_masuk' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
                    'kode_prodi' => 'required|regex:/^[0-9]+$/|exists:prodi,kode_prodi',
                    'npsn' => 'required|regex:/^[0-9]+$/|exists:sekolah,npsn',
                    'kode_daerah' => 'required|regex:/^[0-9]+$/|exists:daerah,kode_daerah',
                ],
                [
                    'nim.required' => 'NIM tidak boleh kosong.',
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'nim.regex' => 'NIM hanya boleh berisi angka.',
                    'nim.max' => 'NIM tidak boleh lebih dari 10 karakter.',
                    'nim.min' => 'NIM harus terdiri dari minimal 9 karakter.',

                    'tahun_masuk.required' => 'Tahun masuk tidak boleh kosong.',
                    'tahun_masuk.integer' => 'Tahun masuk harus berupa angka.',
                    'tahun_masuk.min' => 'Tahun masuk tidak boleh kurang dari 1900.',
                    'tahun_masuk.max' => 'Tahun masuk tidak boleh lebih dari tahun saat ini.',
                    'tahun_masuk.digits' => 'Tahun masuk harus terdiri dari 4 digit.',

                    'kode_prodi.required' => 'Program Studi tidak boleh kosong.',
                    'kode_prodi.regex' => 'Kode Program Studi hanya boleh berisi angka.',
                    'kode_prodi.exists' => 'Program Studi tidak valid.',

                    'npsn.exists' => 'Sekolah asal tidak valid.',
                    'npsn.required' => 'NPSN tidak boleh kosong.',
                    'npsn.regex' => 'NPSN hanya boleh berisi angka.',

                    'kode_daerah.exists' => 'Daerah asal tidak valid.',
                    'kode_daerah.required' => 'Kode Daerah tidak boleh kosong.',
                    'kode_daerah.regex' => 'Kode Daerah hanya boleh berisi angka.',
                ]
            );

            $mahasiswa_uuid = Str::uuid();

            DB::insert("
            INSERT INTO mahasiswa (
                mahasiswa_uuid, 
                nim, 
                tahun_masuk, 
                kode_prodi, 
                npsn, 
                kode_daerah
            ) VALUES (?, ?, ?, ?, ?, ?)
        ", [
                $mahasiswa_uuid,
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['kode_prodi'],
                $validatedData['npsn'],
                $validatedData['kode_daerah'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
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
                "title" => "Berhasil!",
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
     * Export data to Excel.
     */
    public function export()
    {
        try {
            if (Mahasiswa::count() === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Data Kosong",
                    "message" => "Tidak ada data mahasiswa yang bisa dibackup.",
                    "icon" => "warning"
                ], 404);
            }
            
            return Excel::download(new MahasiswaExport, 'mahasiswa_' . date('Ymd_His') . '.xlsx');
            
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
    public function show($mahasiswa_uuid)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_uuid)->select(
                'mahasiswa_uuid',
                'nim',
                'tahun_masuk',
                'kode_prodi',
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
    public function update(Request $request, $mahasiswa_uuid)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_uuid)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'nim' => 'required|regex:/^[0-9]+$/|min:9|max:10|unique:mahasiswa,nim,' . $mahasiswa->mahasiswa_uuid . ',mahasiswa_uuid',
                    'tahun_masuk' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
                    'kode_prodi' => 'required|regex:/^[0-9]+$/|exists:prodi,kode_prodi',
                    'npsn' => 'required|regex:/^[0-9]+$/|exists:sekolah,npsn',
                    'kode_daerah' => 'required|regex:/^[0-9]+$/|exists:daerah,kode_daerah',
                ],
                [
                    'nim.required' => 'NIM tidak boleh kosong.',
                    'nim.unique' => 'NIM sudah terdaftar.',
                    'nim.regex' => 'NIM hanya boleh berisi angka.',
                    'nim.max' => 'NIM tidak boleh lebih dari 10 karakter.',
                    'nim.min' => 'NIM harus terdiri dari minimal 9 karakter.',

                    'tahun_masuk.required' => 'Tahun masuk tidak boleh kosong.',
                    'tahun_masuk.integer' => 'Tahun masuk harus berupa angka.',
                    'tahun_masuk.min' => 'Tahun masuk tidak boleh kurang dari 1900.',
                    'tahun_masuk.max' => 'Tahun masuk tidak boleh lebih dari tahun saat ini.',
                    'tahun_masuk.digits' => 'Tahun masuk harus terdiri dari 4 digit.',

                    'kode_prodi.required' => 'Program Studi tidak boleh kosong.',
                    'kode_prodi.regex' => 'Kode Program Studi hanya boleh berisi angka.',
                    'kode_prodi.exists' => 'Program Studi tidak valid.',

                    'npsn.exists' => 'Sekolah asal tidak valid.',
                    'npsn.required' => 'NPSN tidak boleh kosong.',
                    'npsn.regex' => 'NPSN hanya boleh berisi angka.',

                    'kode_daerah.exists' => 'Daerah asal tidak valid.',
                    'kode_daerah.required' => 'Kode Daerah tidak boleh kosong.',
                    'kode_daerah.regex' => 'Kode Daerah hanya boleh berisi angka.',
                ]
            );

            $dataTidakBerubah =
                $mahasiswa->nim == $validatedData['nim'] &&
                $mahasiswa->tahun_masuk == $validatedData['tahun_masuk'] &&
                $mahasiswa->kode_prodi == $validatedData['kode_prodi'] &&
                $mahasiswa->npsn == $validatedData['npsn'] &&
                $mahasiswa->kode_daerah == $validatedData['kode_daerah'];
               

            if ($dataTidakBerubah) {
                return response()->json([
                    'status' => 400,
                    'title' => 'Tidak Ada Perubahan',
                    'message' => 'Data pengguna tidak mengalami perubahan.',
                    'icon' => 'info'
                ], 400);
            }

            DB::update("
            UPDATE mahasiswa SET 
                nim = ?, 
                tahun_masuk = ?, 
                kode_prodi = ?, 
                npsn = ?, 
                kode_daerah = ?
            WHERE mahasiswa_uuid = ?
        ", [
                $validatedData['nim'],
                $validatedData['tahun_masuk'],
                $validatedData['kode_prodi'],
                $validatedData['npsn'],
                $validatedData['kode_daerah'],
                $mahasiswa_uuid
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
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
    public function destroy($mahasiswa_uuid)
    {
        try {
            $mahasiswa = Mahasiswa::where('mahasiswa_uuid', $mahasiswa_uuid)->firstOrFail();

            if ($mahasiswa) {
                $mahasiswa->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Berhasil!",
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
                "title" => "Berhasil!",
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
