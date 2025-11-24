<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenUsulanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubmenuController;
use App\Http\Controllers\TindakLanjutUsulanController;
use App\Http\Controllers\UserController;
use App\Models\Location;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsulanController;

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    Route::resource('/menu', MenuController::class)->except(['show', 'create']);
    Route::resource('/submenu', SubmenuController::class)->except(['show', 'create']);
    Route::resource('/user', UserController::class)->except(['show', 'create']);
    Route::resource('/role', RoleController::class)->except(['show', 'create']);
    Route::resource('/permission', PermissionController::class)->except(['show', 'create']);

    Route::get('usulan/data', [UsulanController::class, 'getData'])->name('usulan.data');
    Route::get('usulan/export-excel', [UsulanController::class, 'exportExcel'])->name('usulan.export.excel');
    Route::resource('usulan', UsulanController::class);
    Route::put('usulan/{id}/status', [UsulanController::class, 'updateStatus']);

    Route::get('usulan/{id}/history', [UsulanController::class, 'getHistory'])->name('usulan.history');
    Route::get('usulan/{id}/tindak-lanjut', [UsulanController::class, 'getTindakLanjut']);

    Route::post('tindak-lanjut', [TindakLanjutUsulanController::class, 'store'])->name('tindak-lanjut.store');

    Route::get('/dokumen-usulan/{id}', [DokumenUsulanController::class, 'download'])
        ->name('dokumen-usulan.download');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/ajax/desa-by-kecamatan/{id}', function ($id) {
        return Location::where('parent_id', $id)
            ->where('type', 'desa')
            ->get();
    });
});