<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\LahanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PerubahanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\PublicController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// ---------- Publik (tanpa login) ----------
Route::get('/', [PublicController::class, 'landing'])->name('home');
Route::get('/data/lahan.geojson', [PublicController::class, 'geojson'])->name('lahan.geojson');

Route::post('/chatbot', [ChatbotController::class, 'ask'])->middleware('throttle:15,1')->name('chatbot.ask');

// ---------- Autentikasi ----------
Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---------- Admin ----------
Route::middleware(EnsureRole::class . ':admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
        Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');

        // ---------- Akun ----------
        Route::get('/akun', [AdminController::class, 'akun'])->name('akun');
        Route::get('/akun/tambah', [AkunController::class, 'create'])->name('akun.create');
        Route::get('/akun/{user}/ubah', [AkunController::class, 'edit'])->name('akun.edit');
        Route::post('/akun', [AkunController::class, 'store'])->name('akun.store');
        Route::put('/akun/{user}', [AkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{user}', [AkunController::class, 'destroy'])->name('akun.destroy');

        // ---------- Data Lahan (rute statis di atas {lahan}) ----------
        Route::get('/lahan', [AdminController::class, 'lahan'])->name('lahan');
        Route::get('/lahan/tambah', [LahanController::class, 'create'])->name('lahan.create');
        Route::get('/lahan/impor', [LahanController::class, 'formImpor'])->name('lahan.impor');
        Route::get('/lahan/{lahan}/ubah', [LahanController::class, 'edit'])->name('lahan.edit');
        Route::get('/lahan/{lahan}', [LahanController::class, 'show'])->name('lahan.show');
        Route::post('/lahan', [LahanController::class, 'store'])->name('lahan.store');
        Route::post('/lahan/import', [LahanController::class, 'import'])->name('lahan.import');
        Route::put('/lahan/{lahan}', [LahanController::class, 'update'])->name('lahan.update');
        Route::delete('/lahan/{lahan}', [LahanController::class, 'destroy'])->name('lahan.destroy');

        // ---------- Laporan Kegiatan dan Monitoring ----------
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/excel', [LaporanController::class, 'excel'])->name('laporan.excel');
        Route::get('/laporan/monitoring/excel', [LaporanController::class, 'excelMonitoring'])
            ->name('laporan.monitoring.excel');
    });

// ---------- Petugas ----------
Route::middleware(EnsureRole::class . ':petugas')
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {

        Route::get('/peta', [PetugasController::class, 'peta'])->name('peta');
        Route::get('/kegiatan', [PetugasController::class, 'kegiatan'])->name('kegiatan');
        Route::get('/monitoring', [PetugasController::class, 'monitoring'])->name('monitoring');

        // Pemberitahuan perubahan dari Admin: tandai sudah dibaca
        Route::post('/perubahan/baca', [PerubahanController::class, 'baca'])->name('perubahan.baca');
    });

// ---------- Pengaturan akun sendiri (Admin & Petugas) ----------
Route::middleware(EnsureRole::class . ':admin,petugas')->group(function () {

    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::put('/profil/password', [ProfilController::class, 'ubahPassword'])
        ->middleware('throttle:6,1')
        ->name('profil.password');
});

// ---------- Kegiatan rehabilitasi (Admin & Petugas; Petugas hanya yang ia kelola) ----------
Route::middleware(EnsureRole::class . ':admin,petugas')->group(function () {

    Route::get('/kegiatan/tambah', [KegiatanController::class, 'create'])->name('kegiatan.create');
    Route::get('/kegiatan/{kegiatan}/ubah', [KegiatanController::class, 'edit'])->name('kegiatan.edit');
    Route::get('/kegiatan/{kegiatan}/status', [KegiatanController::class, 'formStatus'])->name('kegiatan.status.edit');
    Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::post('/kegiatan/status', [KegiatanController::class, 'status'])->name('kegiatan.status');
    Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');

    // Riwayat monitoring per kegiatan
    Route::get('/monitoring/riwayat/{kegiatan}', [MonitoringController::class, 'riwayat'])->name('monitoring.riwayat');

    // Ubah monitoring: Admin semua, Petugas hanya miliknya (dicek di controller)
    Route::get('/monitoring/{monitoring}/ubah', [MonitoringController::class, 'edit'])->name('monitoring.edit');
    Route::put('/monitoring/{monitoring}', [MonitoringController::class, 'update'])->name('monitoring.update');
});

// ---------- Catat monitoring: khusus Petugas (Admin hanya meninjau, mengubah, dan menghapus) ----------
Route::middleware(EnsureRole::class . ':petugas')->group(function () {

    Route::get('/monitoring/catat', [MonitoringController::class, 'create'])->name('monitoring.create');
    Route::post('/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
});

// ---------- Hapus data: khusus Admin ----------
Route::middleware(EnsureRole::class . ':admin')->group(function () {

    Route::get('/kegiatan/{kegiatan}/hapus', [KegiatanController::class, 'konfirmasiHapus'])->name('kegiatan.hapus');
    Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
    Route::delete('/monitoring/{monitoring}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');
});