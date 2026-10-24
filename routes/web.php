<?php

use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->session()->has('firebase.uid')) {
        return redirect()->route('dashboard');
    }

    return view('landing');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.firebase')->name('logout');

Route::middleware('auth.firebase')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/points', [DashboardController::class, 'points'])->name('dashboard.points');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/baru', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{id}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::post('/laporan/{id}/komentar', [LaporanController::class, 'comment'])->name('laporan.comment');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('role:admin,rt')->group(function () {
            Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
            Route::get('/laporan/{id}', [AdminLaporanController::class, 'show'])->name('laporan.show');
            Route::post('/laporan/{id}/status', [AdminLaporanController::class, 'updateStatus'])->name('laporan.status');
            Route::post('/laporan/{id}/komentar', [AdminLaporanController::class, 'comment'])->name('laporan.comment');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/pengguna/{uid}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/pengguna/{uid}', [AdminUserController::class, 'update'])->name('users.update');

            Route::get('/kategori', [AdminKategoriController::class, 'index'])->name('kategori.index');
            Route::post('/kategori', [AdminKategoriController::class, 'store'])->name('kategori.store');
            Route::delete('/kategori/{id}', [AdminKategoriController::class, 'destroy'])->name('kategori.destroy');
        });
    });
});
