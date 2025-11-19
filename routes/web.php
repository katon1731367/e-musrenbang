<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenUsulanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubmenuController;
use App\Http\Controllers\TindakLanjutUsulanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsulanController;

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);

    Route::resource('/menu', MenuController::class)->except(['show', 'create']);
    Route::resource('/submenu', SubmenuController::class)->except(['show', 'create']);
    Route::resource('/user', UserController::class)->except(['show', 'create']);
    Route::resource('/role', RoleController::class)->except(['show', 'create']);
    Route::resource('/permission', PermissionController::class)->except(['show', 'create']);

    Route::get('usulan/data', [UsulanController::class, 'getData'])->name('usulan.data');
    Route::resource('usulan', UsulanController::class);
    Route::put('usulan/{id}/status', [UsulanController::class, 'updateStatus']);

    Route::get('usulan/{id}/history', [UsulanController::class, 'getHistory'])->name('usulan.history');
    Route::get('usulan/{id}/tindak-lanjut', [UsulanController::class, 'getTindakLanjut']);

    Route::post('tindak-lanjut', [TindakLanjutUsulanController::class, 'store'])->name('tindak-lanjut.store');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dokumen-usulan/{id}', [DokumenUsulanController::class, 'download'])
            ->name('dokumen-usulan.download');
    });
});