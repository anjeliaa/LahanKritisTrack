<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PublicController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// ---------- Publik (tanpa login) ----------
Route::get('/', [PublicController::class, 'landing'])->name('home');

// ---------- Autentikasi (sementara, tanpa database) ----------
Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/demo/{role}', [AuthController::class, 'demo'])->name('login.demo');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---------- Admin ----------
Route::middleware(EnsureRole::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/akun', [AdminController::class, 'akun'])->name('akun');
    Route::get('/lahan', [AdminController::class, 'lahan'])->name('lahan');
    Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
    Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
});

// ---------- Petugas ----------
Route::middleware(EnsureRole::class . ':petugas')->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/peta', [PetugasController::class, 'peta'])->name('peta');
    Route::get('/kegiatan', [PetugasController::class, 'kegiatan'])->name('kegiatan');
    Route::get('/monitoring', [PetugasController::class, 'monitoring'])->name('monitoring');
});
