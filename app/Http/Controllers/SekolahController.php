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
            'sekolah' => Sekolah::where('sekolah_uuid', $request->sekolah_id)->first(['sekolah_id', 'nama_sekolah', 'daerah_sekolah', 'latitude', 'longitude'])
        ];

        if ($request->ajax()) {
            $sekolahs = Sekolah::join('daerah', 'sekolah.daerah_sekolah', '=', 'daerah.kode_daerah')
                ->select(
                    'sekolah.sekolah_uuid',
                    'sekolah.nama_sekolah',
                    'daerah.nama_daerah as daerah_sekolah',
                    'sekolah.latitude',
                    'sekolah.longitude',
                )
                ->orderBy('sekolah.sekolah_uuid', 'DESC')
                ->get();

            return DataTables::of($sekolahs)
                ->addIndexColumn()
                ->addColumn('action', function ($sekolah) {
                    return '<button data-id="' . $sekolah->sekolah_uuid . '" class="btn btn-warning btn-sm" onclick="editSekolah(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $sekolah->sekolah_uuid . '" class="btn btn-danger btn-sm" onclick="deleteSekolah(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
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
                    'nama_sekolah' => 'required|string|max:255|unique:sekolah,nama_sekolah',
                    'daerah_sekolah' => 'required|string|exists:daerah,kode_daerah',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                ],
                [
                    'nama_sekolah.unique' => 'Nama sekolah sudah terdaftar.',
                    'daerah_sekolah.required' => 'Daerah sekolah tidak boleh kosong.',
                    'latitude.required' => 'Latitude harus diisi.',
                    'latitude.numeric' => 'Latitude harus berupa angka.',
                    'latitude.between' => 'Latitude harus antara -90 dan 90.',
                    'longitude.required' => 'Longitude harus diisi.',
                    'longitude.numeric' => 'Longitude harus berupa angka.',
                    'longitude.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            $sekolah_uuid = Str::uuid();

            DB::insert("
            INSERT INTO sekolah (
                sekolah_uuid, 
                nama_sekolah,
                daerah_sekolah,
                latitude,
                longitude, 
                created_at, 
                updated_at
            ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ", [
                $sekolah_uuid,
                $validatedData['nama_sekolah'],
                $validatedData['daerah_sekolah'],
                $validatedData['latitude'],
                $validatedData['longitude'],
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
            $request->validate([
                'import_sekolah' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'import_sekolah.required' => 'File Excel tidak boleh kosong.',
                'import_sekolah.mimes' => 'File harus berupa file Excel (xlsx, xls).',
            ]);

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
    public function show($sekolah_id)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $sekolah_id)->select(
                'sekolah_uuid',
                'nama_sekolah',
                'daerah_sekolah',
                'latitude',
                'longitude',
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
    public function update(Request $request, $sekolah_id)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $sekolah_id)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'nama_sekolah'      => 'required|unique:sekolah,nama_sekolah,' . $sekolah->sekolah_uuid . ',sekolah_uuid',
                    'daerah_sekolah' => 'required|string|exists:daerah,kode_daerah',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                ],
                [
                    'nama_sekolah.unique' => 'Nama sekolah sudah terdaftar.',
                    'daerah_sekolah.required' => 'Daerah sekolah tidak boleh kosong.',
                    'latitude.required' => 'Latitude harus diisi.',
                    'latitude.numeric' => 'Latitude harus berupa angka.',
                    'latitude.between' => 'Latitude harus antara -90 dan 90.',
                    'longitude.required' => 'Longitude harus diisi.',
                    'longitude.numeric' => 'Longitude harus berupa angka.',
                    'longitude.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            DB::update("
            UPDATE sekolah SET 
                nama_sekolah = ?, 
                daerah_sekolah = ?,
                latitude = ?,
                longitude = ?,
                updated_at = NOW()
            WHERE sekolah_uuid = ?
        ", [
                $validatedData['nama_sekolah'],
                $validatedData['daerah_sekolah'],
                $validatedData['latitude'],
                $validatedData['longitude'],
                $sekolah_id
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
    public function destroy($sekolah_id)
    {
        try {
            $sekolah = Sekolah::where('sekolah_uuid', $sekolah_id)->firstOrFail();

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
