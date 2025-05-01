<?php

namespace App\Http\Controllers;

use App\Models\Daerah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // Fetching data from the database
        $mahasiswa = Mahasiswa::select(['jurusan', 'daerah_asal'])->get();
        $daerah = Daerah::select(['nama_daerah', 'latitude', 'longitude'])->get();

        return view('landing-page.layout.main', [
            'mahasiswa' => $mahasiswa,
            'daerah' => $daerah,
            'title' => 'Welcome to My Website',
        ]);
    }
}
