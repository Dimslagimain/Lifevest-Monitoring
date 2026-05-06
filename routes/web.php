<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AircraftController;
use Illuminate\Support\Facades\Route;

// ============================================
// AUTH ROUTES (Guest only)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});



// ============================================
// AUTHENTICATED ROUTES (All roles)
// ============================================
Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');


    // Dashboard (homepage)
    Route::get('/', DashboardController::class)->name('dashboard');

    // Profile Settings
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.settings');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Export — semua role bisa download
    Route::get('/export/replacement-plan', [\App\Http\Controllers\ExcelReportController::class, 'exportReplacementPlan'])->name('reports.excel');
    Route::get('/export/summary', [\App\Http\Controllers\ExcelReportController::class, 'exportSummaryDashboard'])->name('reports.summary');
    Route::get('/export/activity-log', [\App\Http\Controllers\ExcelReportController::class, 'exportActivityLog'])->name('reports.activityLog');
    Route::get('/export/activity-log/{id}', [\App\Http\Controllers\ExcelReportController::class, 'exportSingleActivity'])->name('reports.activityLog.single');

    // Aircraft view — semua role bisa lihat
    Route::get('/aircraft/{registration}', [AircraftController::class, 'show'])->name('aircraft.show');
    Route::get('/aircraft/{registration}/seat-status/{status}', [AircraftController::class, 'seatStatus'])->name('aircraft.seatStatus');

    // PDF Report & Blank Form — semua role bisa download
    Route::get('/aircraft/{registration}/report', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/aircraft/{registration}/blank-form', [\App\Http\Controllers\ReportController::class, 'exportBlankForm'])->name('reports.blank');

    // Fleet view — semua role bisa lihat
    Route::get('/fleet', [\App\Http\Controllers\FleetController::class, 'index'])->name('fleet.index');
    Route::get('/fleet/{fleet}', [\App\Http\Controllers\FleetController::class, 'show'])->name('fleet.show');

    // ============================================
    // ADMIN-ONLY ROUTES
    // ============================================
    Route::middleware('admin')->group(function () {

        // Aircraft edit operations
        Route::post('/aircraft/{registration}/update-seats', [AircraftController::class, 'updateSeats'])->name('aircraft.updateSeats');
        Route::delete('/aircraft/{registration}/delete-seat', [AircraftController::class, 'deleteSeat'])->name('aircraft.deleteSeat');

        // Batch Input
        Route::get('/aircraft/{registration}/batch-input', [AircraftController::class, 'batchInput'])->name('aircraft.batchInput');
        Route::post('/aircraft/{registration}/batch-input', [AircraftController::class, 'storeBatchInput'])->name('aircraft.storeBatchInput');

        // Fleet Management (CRUD - except index & show)
        Route::get('/fleet/create', [\App\Http\Controllers\FleetController::class, 'create'])->name('fleet.create');
        Route::post('/fleet', [\App\Http\Controllers\FleetController::class, 'store'])->name('fleet.store');
        Route::get('/fleet/{fleet}/edit', [\App\Http\Controllers\FleetController::class, 'edit'])->name('fleet.edit');
        Route::put('/fleet/{fleet}', [\App\Http\Controllers\FleetController::class, 'update'])->name('fleet.update');
        Route::patch('/fleet/{fleet}', [\App\Http\Controllers\FleetController::class, 'update']);
        Route::delete('/fleet/{fleet}', [\App\Http\Controllers\FleetController::class, 'destroy'])->name('fleet.destroy');

        // Airlines Management
        Route::get('/fleet/airlines/create', [\App\Http\Controllers\FleetController::class, 'createAirline'])->name('airlines.create');
        Route::post('/fleet/airlines', [\App\Http\Controllers\FleetController::class, 'storeAirline'])->name('airlines.store');
        Route::get('/fleet/airlines/{id}/edit', [\App\Http\Controllers\FleetController::class, 'editAirline'])->name('airlines.edit');
        Route::put('/fleet/airlines/{id}', [\App\Http\Controllers\FleetController::class, 'updateAirline'])->name('airlines.update');
        Route::delete('/fleet/airlines/{id}', [\App\Http\Controllers\FleetController::class, 'destroyAirline'])->name('airlines.destroy');
    });

    // ============================================
    // SUPERADMIN-ONLY ROUTES
    // ============================================
    Route::middleware('superadmin')->group(function () {
        Route::get('/superadmin/users', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('superadmin.users');
        Route::post('/superadmin/users', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('superadmin.users.store');
        Route::put('/superadmin/users/{user}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('superadmin.users.update');
        Route::delete('/superadmin/users/{user}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('superadmin.users.destroy');
        Route::post('/superadmin/users/{user}/suspend', [\App\Http\Controllers\UserManagementController::class, 'suspend'])->name('superadmin.users.suspend');
        Route::post('/superadmin/users/{user}/unsuspend', [\App\Http\Controllers\UserManagementController::class, 'unsuspend'])->name('superadmin.users.unsuspend');
        
        // Bulk Import
        Route::get('/superadmin/bulk-import', [\App\Http\Controllers\BulkImportController::class, 'index'])->name('superadmin.bulk-import');
        Route::post('/superadmin/bulk-import', [\App\Http\Controllers\BulkImportController::class, 'import'])->name('superadmin.bulk-import.process');
        Route::get('/superadmin/bulk-import/template/{type}', [\App\Http\Controllers\BulkImportController::class, 'downloadTemplate'])->name('superadmin.bulk-import.template');
    });
});
