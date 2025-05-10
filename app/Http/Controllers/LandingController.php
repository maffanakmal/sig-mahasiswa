<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Daerah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing-page.layout.main', [
            'title' => 'gisapp',
        ]);
    }

    public function show()
    {
        try {
            $mahasiswa = Mahasiswa::with('daerah')->get();

            return response()->json([
                'status' => 200,
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

}
