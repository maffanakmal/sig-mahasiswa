<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kota;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class KotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'Kota Page',
            'kota' => Kota::where('kota_uuid', $request->kota_id)->first(['nama_kota', 'warna_kota', 'geojson_kota'])
        ];

        if ($request->ajax()) {
            $kotas = Kota::select(
                'kota_uuid',
                'nama_kota',
                'warna_kota',
                'geojson_kota',
            )
                ->orderBy('kota_uuid', 'DESC')
                ->get();

            return DataTables::of($kotas)
                ->addIndexColumn()
                ->editColumn('geojson_kota', function ($kota) {
                    if ($kota->geojson_kota) {
                        $url = asset('storage/' . $kota->geojson_kota);
                        return '<a href="' . $url . '" target="_blank">Lihat GeoJSON</a>';
                    }
                    return 'Tidak ada file';
                })
                ->addColumn('action', function ($kota) {
                    return '<button data-id="' . $kota->kota_uuid . '" class="btn btn-warning btn-sm" onclick="editKota(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $kota->kota_uuid . '" class="btn btn-danger btn-sm" onclick="deleteKota(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['geojson_kota', 'action'])
                ->make(true);
        }


        return view('admin-dashboard.kota', $data);
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
                    'nama_kota' => 'required|string|max:255|unique:kota,nama_kota',
                    'warna_kota' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                    'geojson_kota' => 'required|file|mimes:json,geojson|max:2048', // Validasi file
                ],
                [
                    'nama_kota.unique' => 'Nama kota sudah terdaftar.',
                    'warna_kota.regex' => 'Format warna harus dalam bentuk hex (contoh: #ff0000).',
                    'geojson_kota.mimes' => 'GeoJSON harus berupa format JSON yang valid.'
                ]
            );

            $kota_uuid = Str::uuid();

            // Simpan file ke storage
            $file = $request->file('geojson_kota');
            $fileName = time() . '_' . $file->getClientOriginalName(); // Nama unik
            $filePath = $file->storeAs('geojson', $fileName, 'public'); // Simpan di storage/public/geojson/

            DB::insert("
            INSERT INTO kota (kota_uuid, nama_kota, warna_kota, geojson_kota, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ", [$kota_uuid, $validatedData['nama_kota'], $validatedData['warna_kota'], $filePath]);

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
    public function show($kota_id)
    {
        $kota = Kota::where('kota_uuid', $kota_id)->select(
            'kota_uuid',
            'nama_kota',
            'warna_kota',
            'geojson_kota',
        )->firstOrFail();

        return response()->json([
            'status' => 200,
            'kota' => $kota,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kota $kota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kota $kota)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kota $kota)
    {
        //
    }
}
