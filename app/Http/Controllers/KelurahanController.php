<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kelurahan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class KelurahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'title' => 'Kelurahan Page',
            'kelurahan' => Kelurahan::where('kelurahan_uuid', $request->kelurahan_id)->first(['nama_kelurahan', 'warna_kelurahan', 'geojson_kelurahan'])
        ];

        if ($request->ajax()) {
            $kelurahans = Kelurahan::select(
                'kelurahan_uuid',
                'nama_kelurahan',
                'warna_kelurahan',
                'geojson_kelurahan',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(geojson_kelurahan, '$.features[0].geometry.coordinates')) as koordinat_kelurahan")
            )
                ->orderBy('kelurahan_uuid', 'DESC')
                ->get();

            return DataTables::of($kelurahans)
                ->addIndexColumn()
                ->addColumn('action', function ($kelurahan) {
                    return '<button data-id="' . $kelurahan->kelurahan_uuid . '" class="btn btn-warning btn-sm" onclick="editKelurahan(this)">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button data-id="' . $kelurahan->kelurahan_uuid . '" class="btn btn-danger btn-sm" onclick="deleteKelurahan(this)">
                                <i class="bx bx-trash"></i>
                            </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('admin-dashboard.kelurahan', $data);
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
                    'nama_kelurahan' => 'required|string|max:255|unique:kelurahan,nama_kelurahan',
                    'warna_kelurahan' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                    'geojson_kelurahan' => 'required|json', // Validasi JSON
                ],
                [
                    'nama_kelurahan.unique' => 'Nama kelurahan sudah terdaftar.',
                    'warna_kelurahan.regex' => 'Format warna harus dalam bentuk hex (contoh: #ff0000).',
                    'geojson_kelurahan.json' => 'GeoJSON harus berupa format JSON yang valid.'
                ]
            );

            $kelurahan_uuid = Str::uuid();

            DB::insert("
                INSERT INTO kelurahan (kelurahan_uuid, nama_kelurahan, warna_kelurahan, geojson_kelurahan, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ", [$kelurahan_uuid, $validatedData['nama_kelurahan'], $validatedData['warna_kelurahan'], $validatedData['geojson_kelurahan']]);

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
    public function show($kelurahan_id)
    {
        $kelurahan = Kelurahan::where('kelurahan_uuid', $kelurahan_id)->select(
            'kelurahan_uuid',
            'nama_kelurahan',
            'warna_kelurahan',
            'geojson_kelurahan',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(geojson_kelurahan, '$.features[0].geometry.coordinates')) as koordinat_kelurahan")
        )->firstOrFail();

        return response()->json([
            'status' => 200,
            'kelurahan' => $kelurahan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelurahan $kelurahan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $kelurahan_id)
    {
        try {
            $kelurahan = Kelurahan::where('kelurahan_uuid', $kelurahan_id)->firstOrFail(); // Cari user berdasarkan UUID

            $validatedData = $request->validate(
                [
                    'nama_kelurahan' => 'required|string|max:255|unique:kelurahan,nama_kelurahan,' . $kelurahan->kelurahan_uuid . ',kelurahan_uuid',
                    'warna_kelurahan' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                    'geojson_kelurahan' => 'required|json', // Validasi JSON
                ],
                [
                    'nama_kelurahan.unique' => 'Nama kelurahan sudah terdaftar.',
                    'warna_kelurahan.regex' => 'Format warna harus dalam bentuk hex (contoh: #ff0000).',
                    'geojson_kelurahan.json' => 'GeoJSON harus berupa format JSON yang valid.'
                ]
            );

            // Don't change the UUID
            unset($validatedData['kelurahan_uuid']);

            // Ensure geojson_kelurahan is stored as JSON
            $kelurahan->geojson_kelurahan = $validatedData['geojson_kelurahan'];

            // Update the rest of the data in the database
            DB::update("
            UPDATE kelurahan 
            SET nama_kelurahan = ?, warna_kelurahan = ?, geojson_kelurahan = ?, updated_at = NOW()
            WHERE kelurahan_uuid = ?
        ", [
                $validatedData['nama_kelurahan'],
                $validatedData['warna_kelurahan'],
                $validatedData['geojson_kelurahan'],
                $kelurahan_id
            ]);

            return response()->json([
                "status" => 200,
                "title" => "Success",
                "message" => "Data berhasil diubah.",
                "icon" => "success"
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors(), // Kirim semua error validasi ke AJAX
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
    public function destroy($kelurahan_id)
    {
        try {
            $kelurahan = Kelurahan::where('kelurahan_uuid', $kelurahan_id)->firstOrFail();

            if ($kelurahan) {
                $kelurahan->delete();

                return response()->json([
                    "status" => 200,
                    "title" => "Success",
                    "message" => "Data berhasil dihapus.",
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
}
