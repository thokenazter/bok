<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk halaman pending approval
Route::get('/approval/pending', fn() => view('auth.approval-pending'))->name('approval.pending');

// API Route untuk pengumuman aktif (public)
Route::get('/api/pengumuman/active', [App\Http\Controllers\PengumumanController::class, 'getActive'])->name('api.pengumuman.active');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'approved',
])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Master Data Routes
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
    Route::resource('activities', App\Http\Controllers\ActivityController::class);
    // Per-diem-rates tidak diperlukan karena uang harian fixed Rp 150.000 per desa
    // Route::resource('per-diem-rates', App\Http\Controllers\PerDiemRateController::class);

    // Master RAB (admin-only moved below)

    // LPJ Routes
    Route::resource('lpjs', App\Http\Controllers\LpjController::class);
    Route::post('lpjs/bulk-delete', [App\Http\Controllers\LpjController::class, 'bulkDelete'])->name('lpjs.bulk-delete');
    Route::get('lpjs/search/employees', [App\Http\Controllers\LpjController::class, 'searchEmployees'])->name('lpjs.search.employees');
    Route::get('lpjs/search/activities', [App\Http\Controllers\LpjController::class, 'searchActivities'])->name('lpjs.search.activities');
    Route::post('lpjs/create-activity', [App\Http\Controllers\LpjController::class, 'createActivity'])->name('lpjs.create.activity');
    // Create LPJ from existing (e.g., continue SPPT -> SPPD)
    Route::get('lpjs/{lpj}/create-from', [App\Http\Controllers\LpjController::class, 'createFrom'])->name('lpjs.create_from');

    // Employee Saldo Routes
    Route::get('/employee-saldo', [App\Http\Controllers\EmployeeSaldoController::class, 'index'])->name('employee-saldo.index');
    Route::get('/employee-saldo/{employee}', [App\Http\Controllers\EmployeeSaldoController::class, 'show'])->name('employee-saldo.show');

    // LPJ Document Routes
    Route::get('/lpj/{lpj}/download', [App\Http\Controllers\LpjDocumentController::class, 'download'])->name('lpj.download');
    Route::get('/lpj/{lpj}/preview', [App\Http\Controllers\LpjDocumentController::class, 'preview'])->name('lpj.preview');
    Route::post('/lpj/{lpj}/regenerate', [App\Http\Controllers\LpjDocumentController::class, 'regenerate'])->name('lpj.regenerate');
    Route::get('/lpj/download-multiple', [App\Http\Controllers\LpjDocumentController::class, 'downloadMultiple'])->name('lpj.download_multiple');

    // Pejabat TTD Routes
    Route::resource('pejabat-ttd', App\Http\Controllers\PejabatTtdController::class);

    // Tiba Berangkat Routes
    Route::resource('tiba-berangkats', App\Http\Controllers\TibaBerangkatController::class);
    Route::post('tiba-berangkats/bulk-delete', [App\Http\Controllers\TibaBerangkatController::class, 'bulkDelete'])->name('tiba-berangkats.bulk-delete');
    Route::get('/tiba-berangkats/{tibaBerangkat}/download', [App\Http\Controllers\TibaBerangkatController::class, 'download'])->name('tiba-berangkats.download');
    Route::post('/tiba-berangkats/{tibaBerangkat}/quick-update', [App\Http\Controllers\TibaBerangkatController::class, 'quickUpdate'])->name('tiba-berangkats.quick_update');
    Route::get('/api/pejabat-by-desa', [App\Http\Controllers\TibaBerangkatController::class, 'getPejabatByDesa'])->name('api.pejabat-by-desa');
    // Auto-create TB from a single LPJ (SPPT or SPPD)
    Route::get('/tiba-berangkats/auto-from-lpj/{lpj}', [App\Http\Controllers\TibaBerangkatController::class, 'autoFromLpj'])->name('tiba-berangkats.auto_from_lpj');

    // RAB Routes (read-only for regular users)
    Route::resource('rabs', App\Http\Controllers\RabController::class)->only(['index']);
    Route::get('/api/rabs/info-by-kegiatan', [App\Http\Controllers\RabController::class, 'infoByKegiatan'])->name('rabs.info_by_kegiatan');
    Route::get('/api/rabs/{rab}/basic', [App\Http\Controllers\RabController::class, 'basic'])->name('rabs.basic');

    // Budgets & Allocations
    // Allocations: read-only for regular users
    Route::resource('allocations', App\Http\Controllers\BudgetAllocationController::class)->only(['index']);
    Route::get('/api/allocations/summary-by-kegiatan', [App\Http\Controllers\BudgetAllocationController::class, 'summaryByKegiatan'])->name('allocations.summary_by_kegiatan');

    // POA Routes
    Route::resource('poa', App\Http\Controllers\PoaController::class);
    Route::get('/api/poa/available-rabs', [App\Http\Controllers\PoaController::class, 'availableRabs'])->name('poa.available_rabs');
    Route::post('/poa/{poa}/schedule/carryover', [App\Http\Controllers\PoaController::class, 'carryOver'])->name('poa.schedule.carryover');
    Route::post('/poa/{poa}/schedule/toggle-mark', [App\Http\Controllers\PoaController::class, 'toggleMark'])->name('poa.schedule.toggle_mark');
    Route::post('/poa/{poa}/schedule/upsert-month', [App\Http\Controllers\PoaController::class, 'upsertMonthMeta'])->name('poa.schedule.upsert_month');
    Route::post('/poa/{poa}/schedule/toggle-claim-label', [App\Http\Controllers\PoaController::class, 'toggleClaimLabel'])->name('poa.schedule.toggle_claim_label');
    Route::post('/poa/{poa}/schedule/toggle-claim-lock', [App\Http\Controllers\PoaController::class, 'toggleClaimLock'])->name('poa.schedule.toggle_claim_lock');
    Route::post('/poa/{poa}/item-progress', [App\Http\Controllers\PoaController::class, 'updateItemProgress'])->name('poa.item_progress.update');
    Route::post('/poa/{poa}/claim', [App\Http\Controllers\PoaController::class, 'claim'])->name('poa.claim');

    // Pengumuman Routes moved to super admin group below
});

// Admin Routes - Super Admin Only
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'approved', 'super_admin'])->group(function () {
    Route::resource('/users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');

    // RAB management (create/update/delete/export) - Super Admin only
    Route::resource('rabs', App\Http\Controllers\RabController::class)->except(['index','show']);
    Route::get('/rabs/{rab}/export', [App\Http\Controllers\RabController::class, 'export'])->name('rabs.export');

    // Allocations management (create/update/delete) - Super Admin only
    Route::resource('allocations', App\Http\Controllers\BudgetAllocationController::class)->except(['index','show']);
    Route::get('/api/allocations/available-rabs', [App\Http\Controllers\BudgetAllocationController::class, 'availableRabs'])->name('allocations.available_rabs');

    // POA bulk actions
    Route::post('/poa/bulk-toggle-claim-lock', [App\Http\Controllers\PoaController::class, 'bulkToggleClaimLock'])->name('poa.bulk_toggle_claim_lock');
});

// Admin Routes - Admin or Super Admin
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'approved', 'admin'])->group(function () {
    // Restrict these master data to admin/super admin
    Route::resource('villages', App\Http\Controllers\VillageController::class);
    Route::resource('rate-settings', App\Http\Controllers\RateSettingController::class);

    // Master RAB (admin/super admin)
    Route::get('rab-menus/by-component', [App\Http\Controllers\RabMenuController::class, 'byComponent'])->name('rab-menus.by-component');
    Route::resource('rab-menus', App\Http\Controllers\RabMenuController::class);
    Route::get('rab-kegiatans/by-menu', [App\Http\Controllers\RabKegiatanController::class, 'byMenu'])->name('rab-kegiatans.by-menu');
    Route::resource('rab-kegiatans', App\Http\Controllers\RabKegiatanController::class);

    // Budgets (Pagu Tahunan)
    Route::resource('budgets', App\Http\Controllers\AnnualBudgetController::class);

    // Pengumuman (Announcements)
    Route::resource('pengumuman', App\Http\Controllers\PengumumanController::class)->except(['show']);
    Route::patch('pengumuman/{pengumuman}/toggle', [App\Http\Controllers\PengumumanController::class, 'toggle'])->name('pengumuman.toggle');
});

// Place dynamic {id} show routes after admin CRUD to avoid conflicts with 
// '/create' being captured by '/{id}'
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'approved'])->group(function () {
    Route::get('rabs/{rab}', [App\Http\Controllers\RabController::class, 'show'])->name('rabs.show');
    Route::get('allocations/{allocation}', [App\Http\Controllers\BudgetAllocationController::class, 'show'])->name('allocations.show');
});
