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
            'title' => 'USNIGIS | Halaman Daerah',
            'daerah' => Daerah::where('daerah_uuid', $request->kode_daerah)->first([
                'kode_daerah',
                'nama_daerah',
                'latitude_daerah',
                'longitude_daerah',
            ]),
        ];

        if ($request->ajax()) {
            $daerahs = Daerah::select(
                'daerah_uuid',
                'kode_daerah',
                'nama_daerah',
                'latitude_daerah',
                'longitude_daerah',
            )
                ->orderBy('daerah_uuid', 'DESC');

            return DataTables::of($daerahs)
                ->addIndexColumn()
                ->filterColumn('kode_daerah', function ($query, $keyword) {
                    $query->whereRaw("CAST(kode_daerah AS CHAR) LIKE ?", ["%{$keyword}%"]);
                })
                ->addColumn('action', function ($daerah) {
                    return '<button data-id="' . $daerah->daerah_uuid . '" class="btn btn-warning btn-sm" onclick="editDaerah(this)">
                                <box-icon type="solid" name="pencil" class="icon-crud"  color="white"></box-icon>
                            </button>
                            <button data-id="' . $daerah->daerah_uuid . '" class="btn btn-danger btn-sm" onclick="deleteDaerah(this)">
                                <box-icon type="solid" name="trash" class="icon-crud" color="white"></box-icon>
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
                    'kode_daerah' => 'required|regex:/^[0-9]+$/|min:4|max:10|unique:daerah,kode_daerah',
                    'nama_daerah' => 'required|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:100',
                    'latitude_daerah' => 'required|numeric|between:-90,90',
                    'longitude_daerah' => 'required|numeric|between:-180,180',
                ],
                [
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.max' => 'Kode daerah tidak boleh lebih dari 10 karakter.',
                    'kode_daerah.min' => 'Kode daerah harus minimal 4 karakter.',
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',

                    'nama_daerah.required' => 'Nama daerah harus diisi.',
                    'nama_daerah.regex' => 'Nama daerah hanya boleh mengandung huruf, angka, spasi, titik, koma, dan tanda hubung.',
                    'nama_daerah.max' => 'Nama daerah tidak boleh lebih dari 100 karakter.',
                    'nama_daerah.min' => 'Nama daerah harus minimal 5 karakter.',

                    'latitude_daerah.required' => 'Latitude harus diisi.',
                    'latitude_daerah.numeric' => 'Latitude harus berupa angka.',
                    'latitude_daerah.between' => 'Latitude harus antara -90 dan 90.',

                    'longitude_daerah.required' => 'Longitude harus diisi.',
                    'longitude_daerah.numeric' => 'Longitude harus berupa angka.',
                    'longitude_daerah.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            $daerah_uuid = Str::uuid();

            DB::insert("
            INSERT INTO daerah (
                daerah_uuid, 
                kode_daerah,
                nama_daerah,
                latitude_daerah,
                longitude_daerah
            ) VALUES (?, ?, ?, ?, ?)
        ", [
                $daerah_uuid,
                $validatedData['kode_daerah'],
                $validatedData['nama_daerah'],
                $validatedData['latitude_daerah'],
                $validatedData['longitude_daerah'],
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
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
            $request->validate(
                [
                    'import_daerah' => 'required|file|mimes:xlsx,xls',
                ],
                [
                    'import_daerah.required' => 'Form input excel tidak boleh kosong.',
                    'import_daerah.mimes' => 'File harus berupa file Excel (xlsx, xls).',
                ]
            );

            // Import the Excel file
            Excel::import(new DaerahImport, $request->file('import_daerah'));

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
                "message" => "Data daerah berhasil diimport.",
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
    public function show($daerah_uuid)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $daerah_uuid)->select(
                'kode_daerah',
                'nama_daerah',
                'latitude_daerah',
                'longitude_daerah',
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
    public function update(Request $request, $daerah_uuid)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $daerah_uuid)->firstOrFail();

            $validatedData = $request->validate(
                [
                    'kode_daerah'      => 'required|regex:/^[0-9]+$/|min:4|max:10|unique:daerah,kode_daerah,' . $daerah->daerah_uuid . ',daerah_uuid',
                    'nama_daerah' => 'required|regex:/^[a-zA-Z0-9\s.,-]+$/|min:5|max:100',
                    'latitude_daerah' => 'required|numeric|between:-90,90',
                    'longitude_daerah' => 'required|numeric|between:-180,180',
                ],
                [
                    'kode_daerah.required' => 'Kode daerah harus diisi.',
                    'kode_daerah.regex' => 'Kode daerah hanya boleh berupa angka.',
                    'kode_daerah.max' => 'Kode daerah tidak boleh lebih dari 10 karakter.',
                    'kode_daerah.min' => 'Kode daerah harus minimal 4 karakter.',
                    'kode_daerah.unique' => 'Kode daerah sudah terdaftar.',

                    'nama_daerah.required' => 'Nama daerah harus diisi.',
                    'nama_daerah.regex' => 'Nama daerah mengandung karakter tidak valid.',
                    'nama_daerah.max' => 'Nama daerah tidak boleh lebih dari 100 karakter.',
                    'nama_daerah.min' => 'Nama daerah harus minimal 5 karakter.',

                    'latitude_daerah.required' => 'Latitude harus diisi.',
                    'latitude_daerah.numeric' => 'Latitude harus berupa angka.',
                    'latitude_daerah.between' => 'Latitude harus antara -90 dan 90.',

                    'longitude_daerah.required' => 'Longitude harus diisi.',
                    'longitude_daerah.numeric' => 'Longitude harus berupa angka.',
                    'longitude_daerah.between' => 'Longitude harus antara -180 dan 180.',
                ]
            );

            $dataTidakBerubah =
                $daerah->kode_daerah == $validatedData['kode_daerah'] &&
                $daerah->nama_daerah === $validatedData['nama_daerah'] &&
                (float)$daerah->latitude_daerah == (float)$validatedData['latitude_daerah'] &&
                (float)$daerah->longitude_daerah == (float)$validatedData['longitude_daerah'];

            if ($dataTidakBerubah) {
                return response()->json([
                    'status' => 400,
                    'title' => 'Tidak Ada Perubahan',
                    'message' => 'Data daerah tidak mengalami perubahan.',
                    'icon' => 'info'
                ], 400);
            }

            DB::update("
            UPDATE daerah SET 
                kode_daerah = ?, 
                nama_daerah = ?,
                latitude_daerah = ?,
                longitude_daerah = ?
            WHERE daerah_uuid = ?
        ", [
                $validatedData['kode_daerah'],
                $validatedData['nama_daerah'],
                $validatedData['latitude_daerah'],
                $validatedData['longitude_daerah'],
                $daerah_uuid
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
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
    public function destroy($daerah_uuid)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $daerah_uuid)->firstOrFail();

            if ($daerah) {
                $daerah->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Berhasil!",
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
            $count = DB::table('daerah')->count();

            if ($count === 0) {
                return response()->json([
                    "status" => 404,
                    "title" => "Tidak Ada Data",
                    "message" => "Tidak ada data daerah yang dapat dihapus.",
                    "icon" => "warning"
                ]);
            }

            DB::table('daerah')->delete();

            return response()->json([
                "status" => 200,
                "title" => "Berhasil!",
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
