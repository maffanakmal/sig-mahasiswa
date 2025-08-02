<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DaerahController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\KelurahanController;
use App\Http\Controllers\MahasiswaController;
use App\Models\Sekolah;

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

Route::group(['middleware' => ['guestOnly']], function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing.index');
    Route::get('/show', [LandingController::class, 'show'])->name('landing.show');

    Route::get('/login', [AuthController::class, 'index'])->name('auth.login');
    Route::post('/login/auth', [AuthController::class, 'login'])->name('login.check');

    Route::get('/login/validasi-email', [AuthController::class, 'validateEmail'])->name('auth.validate.email');
    Route::post('/login/validasi-email/auth', [AuthController::class, 'authEmail'])->name('auth.email');

    Route::get('/login/reset-password/{email}/{reset_token}', [AuthController::class, 'resetPassword'])->name('auth.reset.password');
    Route::post('/login/reset-password/auth', [AuthController::class, 'updatePassword'])->name('auth.update.password');
});

Route::group(['middleware' => ['loginCheck', 'checkSession', 'roleCheck:BAAKPSI,Warek 3,PMB']], function () {
    Route::get('/dashboard/home', [HomeController::class, 'index'])->name('home.index');
    Route::get('/dashboard/count', [HomeController::class, 'dataCount'])->name('home.count');
    Route::get('/dashboard/pengaturan', [HomeController::class, 'pengaturanAkun'])->name('home.pengaturan');
    Route::post('/dashboard/pengaturan/store', [HomeController::class, 'pengaturanAkunStore'])->name('home.pengaturan.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => ['loginCheck', 'checkSession', 'roleCheck:BAAKPSI']], function () {
    Route::get('/dashboard/pengguna', [UsersController::class, 'index'])->name('pengguna.index');
    Route::post('/dashboard/pengguna/store', [UsersController::class, 'store'])->name('pengguna.store');
    Route::get('/dashboard/pengguna/show/{user_uuid}', [UsersController::class, 'show'])->name('pengguna.show');
    Route::put('/dashboard/pengguna/update/{user_uuid}', [UsersController::class, 'update'])->name('pengguna.update');
    Route::delete('/dashboard/pengguna/destroy/{user_uuid}', [UsersController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('/dashboard/daerah', [DaerahController::class, 'index'])->name('daerah.index');
    Route::post('/dashboard/daerah/store', [DaerahController::class, 'store'])->name('daerah.store');
    Route::post('/dashboard/daerah/import', [DaerahController::class, 'import'])->name('daerah.import');
    Route::get('/dashboard/daerah/show/{daerah_uuid}', [DaerahController::class, 'show'])->name('daerah.show');
    Route::put('/dashboard/daerah/update/{daerah_uuid}', [DaerahController::class, 'update'])->name('daerah.update');
    Route::delete('/dashboard/daerah/destroy/{daerah_uuid}', [DaerahController::class, 'destroy'])->name('daerah.destroy');
    Route::post('/dashboard/daerah/delete-selected', [DaerahController::class, 'destroySelected'])->name('daerah.destroySelected');
    // Route::delete('/dashboard/daerah/destroys', [DaerahController::class, 'destroyAll'])->name('daerah.destroyAll');

    Route::get('/dashboard/sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::get('/dashboard/sekolah/create', [SekolahController::class, 'create'])->name('sekolah.create');
    Route::post('/dashboard/sekolah/store', [SekolahController::class, 'store'])->name('sekolah.store');
    Route::post('/dashboard/sekolah/import', [SekolahController::class, 'import'])->name('sekolah.import');
    Route::get('/dashboard/sekolah/show/{sekolah_uuid}', [SekolahController::class, 'show'])->name('sekolah.show');
    Route::put('/dashboard/sekolah/update/{sekolah_uuid}', [SekolahController::class, 'update'])->name('sekolah.update');
    Route::delete('/dashboard/sekolah/destroy/{sekolah_uuid}', [SekolahController::class, 'destroy'])->name('sekolah.destroy');
    Route::post('/dashboard/sekolah/delete-selected', [SekolahController::class, 'destroySelected'])->name('sekolah.destroySelected');
    // Route::delete('/dashboard/sekolah/destroys', [SekolahController::class, 'destroyAll'])->name('sekolah.destroyAll');

    Route::get('/dashboard/prodi', [JurusanController::class, 'index'])->name('prodi.index');
    Route::post('/dashboard/prodi/store', [JurusanController::class, 'store'])->name('prodi.store');
    Route::post('/dashboard/prodi/import', [JurusanController::class, 'import'])->name('prodi.import');
    Route::get('/dashboard/prodi/show/{prodi_uuid}', [JurusanController::class, 'show'])->name('prodi.show');
    Route::put('/dashboard/prodi/update/{prodi_uuid}', [JurusanController::class, 'update'])->name('prodi.update');
    Route::delete('/dashboard/prodi/destroy/{prodi_uuid}', [JurusanController::class, 'destroy'])->name('prodi.destroy');
    Route::post('/dashboard/prodi/delete-selected', [JurusanController::class, 'destroySelected'])->name('prodi.destroySelected');
    // Route::delete('/dashboard/prodi/destroys', [JurusanController::class, 'destroyAll'])->name('prodi.destroyAll');

    Route::get('/dashboard/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/dashboard/mahasiswa/create', [MahasiswaController::class, 'create'])->name('mahasiswa.create');
    Route::post('/dashboard/mahasiswa/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::post('/dashboard/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    Route::get('/dashboard/mahasiswa/show/{mahasiswa_uuid}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::put('/dashboard/mahasiswa/update/{mahasiswa_uuid}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/dashboard/mahasiswa/destroy/{mahasiswa_uuid}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    Route::post('/dashboard/mahasiswa/delete-selected', [MahasiswaController::class, 'destroySelected'])->name('mahasiswa.destroySelected');
    // Route::delete('/dashboard/mahasiswa/destroys', [MahasiswaController::class, 'destroyAll'])->name('mahasiswa.destroyAll');
});

Route::group(['middleware' => ['loginCheck', 'checkSession', 'roleCheck:Warek 3,PMB']], function () {
    Route::get('/dashboard/peta/daerah', [DataController::class, 'mapDaerah'])->name('dashboard.peta.daerah');
    Route::get('/dashboard/peta/sekolah', [DataController::class, 'mapSekolah'])->name('dashboard.peta.sekolah');
    Route::get('/dashboard/peta/show', [DataController::class, 'mapShow'])->name('dashboard.peta.show');
    Route::get('/dashboard/peta/filter', [DataController::class, 'mapFilter'])->name('dashboard.peta.filter');
    Route::post('/dashboard/peta/filter/show/daerah', [DataController::class, 'mapShowFilterDaerah'])->name('dashboard.peta.filter.show.daerah');
    Route::post('/dashboard/peta/filter/show/sekolah', [DataController::class, 'mapShowFilterSekolah'])->name('dashboard.peta.filter.show.sekolah');
});
