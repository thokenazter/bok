<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk halaman pending approval
Route::get('/approval/pending', fn() => view('auth.approval-pending'))->name('approval.pending');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'approved',
])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Master Data Routes
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
    Route::resource('villages', App\Http\Controllers\VillageController::class);
    Route::resource('activities', App\Http\Controllers\ActivityController::class);
    Route::resource('rate-settings', App\Http\Controllers\RateSettingController::class);
    // Per-diem-rates tidak diperlukan karena uang harian fixed Rp 150.000 per desa
    // Route::resource('per-diem-rates', App\Http\Controllers\PerDiemRateController::class);

    // LPJ Routes
    Route::resource('lpjs', App\Http\Controllers\LpjController::class);
    Route::post('lpjs/bulk-delete', [App\Http\Controllers\LpjController::class, 'bulkDelete'])->name('lpjs.bulk-delete');
    Route::get('lpjs/search/employees', [App\Http\Controllers\LpjController::class, 'searchEmployees'])->name('lpjs.search.employees');
    Route::get('lpjs/search/activities', [App\Http\Controllers\LpjController::class, 'searchActivities'])->name('lpjs.search.activities');

    // Employee Saldo Routes
    Route::get('/employee-saldo', [App\Http\Controllers\EmployeeSaldoController::class, 'index'])->name('employee-saldo.index');
    Route::get('/employee-saldo/{employee}', [App\Http\Controllers\EmployeeSaldoController::class, 'show'])->name('employee-saldo.show');

    // LPJ Document Routes
    Route::get('/lpj/{lpj}/download', [App\Http\Controllers\LpjDocumentController::class, 'download'])->name('lpj.download');
    Route::get('/lpj/{lpj}/preview', [App\Http\Controllers\LpjDocumentController::class, 'preview'])->name('lpj.preview');
    Route::post('/lpj/{lpj}/regenerate', [App\Http\Controllers\LpjDocumentController::class, 'regenerate'])->name('lpj.regenerate');

    // Pejabat TTD Routes
    Route::resource('pejabat-ttd', App\Http\Controllers\PejabatTtdController::class);

    // Tiba Berangkat Routes
    Route::resource('tiba-berangkats', App\Http\Controllers\TibaBerangkatController::class);
    Route::get('/tiba-berangkats/{tibaBerangkat}/download', [App\Http\Controllers\TibaBerangkatController::class, 'download'])->name('tiba-berangkats.download');
    Route::get('/api/pejabat-by-desa', [App\Http\Controllers\TibaBerangkatController::class, 'getPejabatByDesa'])->name('api.pejabat-by-desa');
});

// Admin Routes - Super Admin Only
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'approved', 'super_admin'])->group(function () {
    Route::resource('/users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
});
