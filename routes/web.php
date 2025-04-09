<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DaerahController;
use App\Http\Controllers\DataController;
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

Route::get('/', function () {
    return view('landing-page.jumbotron', [
        'title' => 'Welcome to My Website',
    ]);
});

Route::get('/login', function () {
    return view('auth-page.login', [
        'title' => 'Login Page',
    ]);
})->name('login');

Route::get('/dashboard/home', [HomeController::class, 'index'])->name('home.index');
Route::get('/dashboard/count', [HomeController::class, 'dataCount'])->name('home.count');

Route::get('/dashboard/daerah', [DaerahController::class, 'index'])->name('daerah.index');
Route::post('/dashboard/daerah/store', [DaerahController::class, 'store'])->name('daerah.store');
Route::get('/dashboard/daerah/show/{daerah_id}', [DaerahController::class, 'show'])->name('daerah.show');
Route::put('/dashboard/daerah/update/{daerah_id}', [DaerahController::class, 'update'])->name('daerah.update');
Route::delete('/dashboard/daerah/destroy/{daerah_id}', [DaerahController::class, 'destroy'])->name('daerah.destroy');

Route::get('/dashboard/kelurahan', [KelurahanController::class, 'index'])->name('kelurahan.index');
Route::post('/dashboard/kelurahan/store', [KelurahanController::class, 'store'])->name('kelurahan.store');
Route::get('/dashboard/kelurahan/show/{kelurahan_id}', [KelurahanController::class, 'show'])->name('kelurahan.show');
Route::put('/dashboard/kelurahan/update/{kelurahan_id}', [KelurahanController::class, 'update'])->name('kelurahan.update');
Route::delete('/dashboard/kelurahan/destroy/{kelurahan_id}', [KelurahanController::class, 'destroy'])->name('kelurahan.destroy');

Route::get('/dashboard/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
Route::post('/dashboard/mahasiswa/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
Route::post('/dashboard/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
Route::get('/dashboard/mahasiswa/show/{mahasiswa_id}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');
Route::put('/dashboard/mahasiswa/update/{mahasiswa_id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
Route::delete('/dashboard/mahasiswa/destroy/{mahasiswa_id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
Route::delete('/dashboard/mahasiswa/destroys', [MahasiswaController::class, 'destroyAll'])->name('mahasiswa.destroyAll');

Route::get('/dashboard/grafik/peta', [DataController::class, 'mapIndex'])->name('grafik.peta');
Route::get('/dashboard/grafik/peta/filter', [DataController::class, 'mapFilter'])->name('grafik.peta.filter');
Route::post('/dashboard/grafik/peta/filter/show', [DataController::class, 'mapFilterShow'])->name('grafik.peta.filter.show');