<?php

namespace App\Http\Controllers;

use App\Imports\SekolahImport;
use Exception;
use App\Models\Daerah;
use App\Models\Sekolah;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class SekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'Sekolah Page',
            'sekolah' => Sekolah::where('sekolah_uuid', $request->npsn)->first(['npsn', 'nama_sekolah', 'alamat_sekolah', 'kode_daerah', 'latitude_sekolah', 'longitude_sekolah'])
        ];

        if ($request->ajax()) {
            $sekolahs = Sekolah::leftjoin('daerah', 'sekolah.kode_daerah', '=', 'daerah.kode_daerah')
                ->select(
                    'sekolah.npsn',
                    'sekolah.sekolah_uuid',
                    'sekolah.nama_sekolah',
                    'sekolah.alamat_sekolah',
                    'daerah.nama_daerah as kode_daerah',
                    'sekolah.latitude_sekolah',
                    'sekolah.longitude_sekolah',
                )
                ->orderBy('sekolah.sekolah_uuid', 'DESC')
                ->get();

            return DataTables::of($sekolahs)
                ->addIndexColumn()
                ->editColumn('kode_daerah', function ($row) {
                    return $row->kode_daerah ?? '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('action', function ($sekolah) {
                    return '<button data-id="' . $sekolah->sekolah_uuid . '" class="btn btn-warning btn-sm" onclick="editSekolah(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $sekolah->sekolah_uuid . '" class="btn btn-danger btn-sm" onclick="deleteSekolah(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action', 'kode_daerah'])
                ->make(true);
        }

        return view('admin-dashboard.sekolah', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $daerah = Daerah::select(['kode_daerah', 'nama_daerah'])->get();

            return response()->json([
                'status' => 200,
                'daerah' => $daerah,
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
                    'npsn' => 'required|regex:/^[0-9]+$/|digits_between:1,8|unique:sekolah,npsn',
                    'nama_sekolah' => 'required|string|max:100|regex:/^[\pL\pN\s\.\,\-\'"]+$/u',
                    'alamat_sekolah' => 'required|string|max:255',
                    'kode_daerah' => 'required|numeric|exists:daerah,kode_daerah',
                    'latitude_sekolah' => 'required|numeric|between:-90,90',
                    'longitude_sekolah' => 'required|numeric|between:-180,180',
                ],
                [
                    'npsn.required' => 'NPSN harus diisi.',
                    'npsn.regex' => 'NPSN hanya boleh berisi angka.',
                    'npsn.digits_between' => 'NPSN harus terdiri dari 1 hingga 8 digit.',
                    'npsn.unique' => 'NPSN sudah terdaftar.',

                    'nama_sekolah.required' => 'Nama sekolah harus diisi.',
                    'nama_sekolah.regex' => 'Nama sekolah mengandung karakter tidak valid.',
                    'nama_sekolah.max' => 'Nama sekolah maksimal 100 karakter.',

                    'alamat_sekolah.required' => 'Alamat sekolah harus diisi.',
                    'alamat_sekolah.max' => 'Alamat sekolah maksimal 255 karakter.',
                    'alamat_sekolah.string' => 'Alamat sekolah harus berupa string.',

                    'kode_daerah.required' => 'Daerah sekolah tidak boleh kosong.',
                    'kode_daerah.numeric' => 'Kode daerah harus berupa angka.',
                    'kode_daerah.exists' => 'Kode daerah tidak valid.',

                    'latitude_sekolah.required' => 'Latitude harus diisi.',
                    'latitude_sekolah.numeric' => 'Latitude harus berupa angka.',
                    'latitude_sekolah.between' => 'Latitude harus antara -90 dan 90.',

                    'longitude_sekolah.required' => 'Longitude harus diisi.',
                    'longitude_sekolah.numeric' => 'Longitude harus berupa angka.',
                    'longitude_sekolah.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            $sekolah_uuid = Str::uuid();

            DB::insert("
    INSERT INTO sekolah (
        npsn,
        sekolah_uuid, 
        nama_sekolah,
        alamat_sekolah,
        kode_daerah,
        latitude_sekolah,
        longitude_sekolah
    ) VALUES (?, ?, ?, ?, ?, ?, ?)
", [
                $validatedData['npsn'],
                $sekolah_uuid,
                $validatedData['nama_sekolah'],
                $validatedData['alamat_sekolah'],
                $validatedData['kode_daerah'],
                $validatedData['latitude_sekolah'],
                $validatedData['longitude_sekolah'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data sekolah berhasil ditambahkan.",
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
                    'import_sekolah' => 'required|file|mimes:xlsx,xls',
                ],
                [
                    'import_sekolah.required' => 'Form input excel tidak boleh kosong.',
                    'import_sekolah.mimes' => 'File harus berupa file Excel (xlsx, xls).',
                ]
            );

            // Import the Excel file
            Excel::import(new SekolahImport, $request->file('import_sekolah'));

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data sekolah berhasil diimpor.",
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
    public function show($npsn)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $npsn)->select(
                'npsn',
                'sekolah_uuid',
                'nama_sekolah',
                'alamat_sekolah',
                'kode_daerah',
                'latitude_sekolah',
                'longitude_sekolah',
            )->firstOrFail();

            return response()->json([
                "status" => 200,
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
     * Show the form for editing the specified resource.
     */
    public function edit(Sekolah $sekolah)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $npsn)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $npsn)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'npsn' => 'required|regex:/^[0-9]+$/|digits_between:1,8|unique:sekolah,npsn,' . $sekolah->sekolah_uuid . ',sekolah_uuid',
                    'nama_sekolah' => 'required|string|max:100|regex:/^[\pL\pN\s\.\,\-\'"]+$/u',
                    'alamat_sekolah' => 'required|string|max:255',
                    'kode_daerah' => 'required|numeric|exists:daerah,kode_daerah',
                    'latitude_sekolah' => 'required|numeric|between:-90,90',
                    'longitude_sekolah' => 'required|numeric|between:-180,180',
                ],
                [
                    'npsn.required' => 'NPSN harus diisi.',
                    'npsn.regex' => 'NPSN hanya boleh berisi angka.',
                    'npsn.digits_between' => 'NPSN harus terdiri dari 1 hingga 8 digit.',
                    'npsn.unique' => 'NPSN sudah terdaftar.',

                    'nama_sekolah.required' => 'Nama sekolah harus diisi.',
                    'nama_sekolah.regex' => 'Nama sekolah mengandung karakter tidak valid.',
                    'nama_sekolah.max' => 'Nama sekolah maksimal 100 karakter.',

                    'alamat_sekolah.required' => 'Alamat sekolah harus diisi.',
                    'alamat_sekolah.max' => 'Alamat sekolah maksimal 255 karakter.',
                    'alamat_sekolah.string' => 'Alamat sekolah harus berupa string.',

                    'kode_daerah.required' => 'Daerah sekolah tidak boleh kosong.',
                    'kode_daerah.numeric' => 'Kode daerah harus berupa angka.',
                    'kode_daerah.exists' => 'Kode daerah tidak valid.',

                    'latitude_sekolah.required' => 'Latitude harus diisi.',
                    'latitude_sekolah.numeric' => 'Latitude harus berupa angka.',
                    'latitude_sekolah.between' => 'Latitude harus antara -90 dan 90.',

                    'longitude_sekolah.required' => 'Longitude harus diisi.',
                    'longitude_sekolah.numeric' => 'Longitude harus berupa angka.',
                    'longitude_sekolah.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            DB::update("
            UPDATE sekolah SET 
                npsn = ?, 
                nama_sekolah = ?, 
                alamat_sekolah = ?,
                kode_daerah = ?,
                latitude_sekolah = ?,
                longitude_sekolah = ?
            WHERE sekolah_uuid = ?
        ", [
                $validatedData['npsn'],
                $validatedData['nama_sekolah'],
                $validatedData['alamat_sekolah'],
                $validatedData['kode_daerah'],
                $validatedData['latitude_sekolah'],
                $validatedData['longitude_sekolah'],
                $npsn
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data sekolah berhasil diperbarui.",
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
    public function destroy($npsn)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $npsn)->firstOrFail();

            if ($sekolah) {
                $sekolah->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data sekolah berhasil dihapus.",
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
            $count = DB::table('sekolah')->count();

            if ($count === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Tidak Ada Data",
                    "message" => "Tidak ada data sekolah yang dapat dihapus.",
                    "icon" => "warning"
                ]);
            }

            DB::table('sekolah')->delete();

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Semua data sekolah berhasil dihapus.",
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
