<?php

namespace App\Http\Controllers;

use App\Models\Kota;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('admin-dashboard.home', [
            'title' => 'Home Page',
        ]);
    }

    public function dataCount()
    {
        $kelurahanCount = Kelurahan::count();

        return response()->json([
            'status' => 200,
            'kelurahanCount' => $kelurahanCount,
        ], 200);
    }

}
