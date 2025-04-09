<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
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
            'daerah' => Daerah::where('daerah_uuid', $request->daerah_id)->first([
                'daerah_uuid',
                'nama_geojson_daerah',
                'file_geojson_daerah',
            ]),
        ];

        if ($request->ajax()) {
            $daerahs = Daerah::select(
                'daerah_uuid',
                'nama_geojson_daerah',
                'file_geojson_daerah',
            )
                ->orderBy('daerah_uuid', 'DESC')
                ->get();

            return DataTables::of($daerahs)
                ->addIndexColumn()
                ->editColumn('file_geojson_daerah', function ($daerah) {
                    if ($daerah->file_geojson_daerah) {
                        $url = asset('storage/' . $daerah->file_geojson_daerah);
                        return '<a href="' . $url . '" target="_blank">Lihat GeoJSON</a>';
                    }
                    return 'Tidak ada file';
                })
                ->addColumn('action', function ($daerah) {
                    return '<button data-id="' . $daerah->daerah_uuid . '" class="btn btn-warning btn-sm" onclick="editDaerah(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $daerah->daerah_uuid . '" class="btn btn-danger btn-sm" onclick="deleteDaerah(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['file_geojson_daerah', 'action'])
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
                    'nama_geojson_daerah' => 'required|string|max:255|unique:daerah,nama_geojson_daerah',
                    'file_geojson_daerah' => 'required|file|mimes:json,geojson|max:2048', // Validasi file
                ],
                [
                    'nama_geojson_daerah.unique' => 'Nama daerah sudah terdaftar.',
                    'file_geojson_daerah.mimes' => 'GeoJSON harus berupa format JSON yang valid.'
                ]
            );

            $daerah_uuid = Str::uuid();

            // Simpan file ke storage
            $file = $request->file('file_geojson_daerah');
            $fileName = time() . '_' . $file->getClientOriginalName(); // Nama unik
            $filePath = $file->storeAs('geojson', $fileName, 'public'); // Simpan di storage/public/geojson/

            DB::insert("
            INSERT INTO daerah (daerah_uuid, nama_geojson_daerah, file_geojson_daerah, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
            ", [$daerah_uuid, $validatedData['nama_geojson_daerah'], $filePath]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data berhasil dibuat.",
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
     * Display the specified resource.
     */
    public function show($daerah_id)
    {
        $daerah = Daerah::where('daerah_uuid', $daerah_id)->select(
            'daerah_uuid',
            'nama_geojson_daerah',
            'file_geojson_daerah',
        )->firstOrFail();

        return response()->json([
            'status' => 200,
            'daerah' => $daerah,
        ]);
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
    public function update(Request $request, Daerah $Daerah)
    {
        try {
            $validatedData = $request->validate(
                [
                    'nama_geojson_daerah' => 'required|string|max:255|unique:daerah,nama_geojson_daerah,' . $Daerah->daerah_uuid . ',daerah_uuid',
                    'file_geojson_daerah' => 'nullable|file|mimes:json,geojson|max:2048', // Validasi file
                ],
                [
                    'nama_geojson_daerah.unique' => 'Nama daerah sudah terdaftar.',
                    'file_geojson_daerah.mimes' => 'GeoJSON harus berupa format JSON yang valid.'
                ]
            );

            if ($request->hasFile('file_geojson_daerah')) {
                // Hapus file lama jika ada
                if ($Daerah->file_geojson_daerah) {
                    Storage::disk('public')->delete($Daerah->file_geojson_daerah);
                }

                // Simpan file baru
                $file = $request->file('file_geojson_daerah');
                $fileName = time() . '_' . $file->getClientOriginalName(); // Nama unik
                $filePath = $file->storeAs('geojson', $fileName, 'public'); // Simpan di storage/public/geojson/
            } else {
                $filePath = $Daerah->file_geojson_daerah; // Gunakan file lama jika tidak ada file baru
            }

            DB::update("
            UPDATE daerah SET nama_geojson_daerah = ?, file_geojson_daerah = ?, updated_at = NOW()
            WHERE daerah_uuid = ?
            ", [$validatedData['nama_geojson_daerah'], $filePath, $Daerah->daerah_uuid]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data berhasil diperbarui.",
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
    public function destroy($daerah_id)
    {
        try {
            $daerah = Daerah::where('daerah_uuid', $daerah_id)->firstOrFail();

            // Hapus file dari storage
            if ($daerah->file_geojson_daerah) {
                Storage::disk('public')->delete($daerah->file_geojson_daerah);
            }

            DB::delete("
            DELETE FROM daerah WHERE daerah_uuid = ?
            ", [$daerah->daerah_uuid]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data berhasil dihapus.",
                "icon" => "success"
            ], 200);
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
