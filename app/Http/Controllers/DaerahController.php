<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\DaerahImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DaerahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'Daerah Page',
            'daerah' => Daerah::where('daerah_uuid', $request->kode_daerah)->first([
                'daerah_uuid',
                'kode_daerah',
                'nama_daerah',
                'latitude',
                'longitude',
            ]),
        ];

        if ($request->ajax()) {
            $daerahs = Daerah::select(
                'daerah_uuid',
                'kode_daerah',
                'nama_daerah',
                'latitude',
                'longitude',
            )
                ->orderBy('daerah_uuid', 'DESC')
                ->get();

            return DataTables::of($daerahs)
                ->addIndexColumn()
                ->addColumn('action', function ($daerah) {
                    return '<button data-id="' . $daerah->daerah_uuid . '" class="btn btn-warning btn-sm" onclick="editDaerah(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $daerah->daerah_uuid . '" class="btn btn-danger btn-sm" onclick="deleteDaerah(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('admin-dashboard.daerah', $data);
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
                    'kode_daerah' => 'required|regex:/^\d{1,4}$/|unique:daerah,kode_daerah',
                    'nama_daerah' => 'required|string|max:255',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah tidak boleh lebih dari 4 karakter.',
                    'nama_daerah.required' => 'Nama daerah harus diisi.',
                    'latitude.required' => 'Latitude harus diisi.',
                    'latitude.numeric' => 'Latitude harus berupa angka.',
                    'latitude.between' => 'Latitude harus antara -90 dan 90.',
                    'longitude.required' => 'Longitude harus diisi.',
                    'longitude.numeric' => 'Longitude harus berupa angka.',
                    'longitude.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            $daerah_uuid = Str::uuid();

            DB::insert("
            INSERT INTO daerah (
                daerah_uuid, 
                kode_daerah,
                nama_daerah,
                latitude,
                longitude, 
                created_at, 
                updated_at
            ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ", [
                $daerah_uuid,
                $validatedData['kode_daerah'],
                $validatedData['nama_daerah'],
                $validatedData['latitude'],
                $validatedData['longitude'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data daerah berhasil ditambahkan.",
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
                'import_daerah' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'import_daerah.mimes' => 'File harus berupa file Excel (xlsx, xls).',
            ]);

            // Import the Excel file
            Excel::import(new DaerahImport, $request->file('import_daerah'));

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data daerah berhasil diimpor.",
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
    public function show($kode_daerah)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $kode_daerah)->select(
                'daerah_uuid',
                'kode_daerah',
                'nama_daerah',
                'latitude',
                'longitude',
            )->firstOrFail();

            return response()->json([
                "status" => 200,
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
     * Show the form for editing the specified resource.
     */
    public function edit(Daerah $Daerah)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $kode_daerah)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $kode_daerah)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'kode_daerah'      => 'required|regex:/^\d{1,4}$/|unique:daerah,kode_daerah,' . $daerah->daerah_uuid . ',daerah_uuid',
                    'nama_daerah'      => 'required|string|max:255',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                ],
                [
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',
                    'kode_daerah.regex' => 'Kode daerah tidak boleh lebih dari 4 karakter.',
                    'nama_daerah.required' => 'Nama daerah harus diisi.',
                    'latitude.required' => 'Latitude harus diisi.',
                    'latitude.numeric' => 'Latitude harus berupa angka.',
                    'latitude.between' => 'Latitude harus antara -90 dan 90.',
                    'longitude.required' => 'Longitude harus diisi.',
                    'longitude.numeric' => 'Longitude harus berupa angka.',
                    'longitude.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            DB::update("
            UPDATE daerah SET 
                kode_daerah = ?, 
                nama_daerah = ?,
                latitude = ?,
                longitude = ?,
                updated_at = NOW()
            WHERE daerah_uuid = ?
        ", [
                $validatedData['kode_daerah'],
                $validatedData['nama_daerah'],
                $validatedData['latitude'],
                $validatedData['longitude'],
                $kode_daerah
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data daerah berhasil diperbarui.",
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
    public function destroy($kode_daerah)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $kode_daerah)->firstOrFail();

            if ($daerah) {
                $daerah->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data daerah berhasil dihapus.",
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
            DB::table('daerah')->delete();

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Semua data daerah berhasil dihapus.",
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
