<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DaerahController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\KelurahanController;
use App\Http\Controllers\MahasiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/show', [LandingController::class, 'show'])->name('landing.show');

Route::get('/login', function () {
    return view('auth-page.login', [
        'title' => 'Login Page',
    ]);
})->name('login');


Route::get('/dashboard/home', [HomeController::class, 'index'])->name('home.index');
Route::get('/dashboard/count', [HomeController::class, 'dataCount'])->name('home.count');

Route::get('/dashboard/daerah', [DaerahController::class, 'index'])->name('daerah.index');
Route::post('/dashboard/daerah/store', [DaerahController::class, 'store'])->name('daerah.store');
Route::post('/dashboard/daerah/import', [DaerahController::class, 'import'])->name('daerah.import');
Route::get('/dashboard/daerah/show/{kode_daerah}', [DaerahController::class, 'show'])->name('daerah.show');
Route::put('/dashboard/daerah/update/{kode_daerah}', [DaerahController::class, 'update'])->name('daerah.update');
Route::delete('/dashboard/daerah/destroy/{kode_daerah}', [DaerahController::class, 'destroy'])->name('daerah.destroy');
Route::delete('/dashboard/daerah/destroys', [DaerahController::class, 'destroyAll'])->name('daerah.destroyAll');
Route::get('/dashboard/daerah/check', [DaerahController::class, 'check'])->name('daerah.check');

Route::get('/dashboard/sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
Route::get('/dashboard/sekolah/create', [SekolahController::class, 'create'])->name('sekolah.create');
Route::post('/dashboard/sekolah/store', [SekolahController::class, 'store'])->name('sekolah.store');
Route::post('/dashboard/sekolah/import', [SekolahController::class, 'import'])->name('sekolah.import');
Route::get('/dashboard/sekolah/show/{kode_sekolah}', [SekolahController::class, 'show'])->name('sekolah.show');
Route::put('/dashboard/sekolah/update/{kode_sekolah}', [SekolahController::class, 'update'])->name('sekolah.update');
Route::delete('/dashboard/sekolah/destroy/{kode_sekolah}', [SekolahController::class, 'destroy'])->name('sekolah.destroy');
Route::delete('/dashboard/sekolah/destroys', [SekolahController::class, 'destroyAll'])->name('sekolah.destroyAll');

Route::get('/dashboard/jurusan', [JurusanController::class, 'index'])->name('jurusan.index');
Route::post('/dashboard/jurusan/store', [JurusanController::class, 'store'])->name('jurusan.store');
Route::post('/dashboard/jurusan/import', [JurusanController::class, 'import'])->name('jurusan.import');
Route::get('/dashboard/jurusan/show/{kode_jurusan}', [JurusanController::class, 'show'])->name('jurusan.show');
Route::put('/dashboard/jurusan/update/{kode_jurusan}', [JurusanController::class, 'update'])->name('jurusan.update');
Route::delete('/dashboard/jurusan/destroy/{kode_jurusan}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');
Route::delete('/dashboard/jurusan/destroys', [JurusanController::class, 'destroyAll'])->name('jurusan.destroyAll');

Route::get('/dashboard/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
Route::get('/dashboard/mahasiswa/create', [MahasiswaController::class, 'create'])->name('mahasiswa.create');
Route::post('/dashboard/mahasiswa/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
Route::post('/dashboard/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
Route::get('/dashboard/mahasiswa/show/{nim}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');
Route::put('/dashboard/mahasiswa/update/{nim}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
Route::delete('/dashboard/mahasiswa/destroy/{nim}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
Route::delete('/dashboard/mahasiswa/destroys', [MahasiswaController::class, 'destroyAll'])->name('mahasiswa.destroyAll');

Route::get('/dashboard/grafik/peta', [DataController::class, 'mapIndex'])->name('grafik.peta');
Route::get('/dashboard/grafik/peta/show', [DataController::class, 'mapShow'])->name('grafik.peta.show');
Route::get('/dashboard/grafik/peta/filter', [DataController::class, 'mapFilter'])->name('grafik.peta.filter');
Route::post('/dashboard/grafik/peta/filter/show', [DataController::class, 'mapFilterShow'])->name('grafik.peta.filter.show');