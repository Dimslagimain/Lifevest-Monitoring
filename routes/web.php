<?php

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelReportController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\PdfScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
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
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.settings');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Export — semua role bisa download
    Route::get('/export/replacement-plan', [ExcelReportController::class, 'exportReplacementPlan'])->name('reports.excel');
    Route::get('/export/summary', [ExcelReportController::class, 'exportSummaryDashboard'])->name('reports.summary');
    Route::get('/export/activity-log', [ExcelReportController::class, 'exportActivityLog'])->name('reports.activityLog');
    Route::get('/export/activity-log/{id}', [ExcelReportController::class, 'exportSingleActivity'])->name('reports.activityLog.single');

    // Aircraft view — semua role bisa lihat
    Route::get('/aircraft/{registration}', [AircraftController::class, 'show'])->name('aircraft.show');
    Route::get('/aircraft/{registration}/seat-status/{status}', [AircraftController::class, 'seatStatus'])->name('aircraft.seatStatus');

    // PDF Report & Blank Form — semua role bisa download
    Route::get('/aircraft/{registration}/report', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/aircraft/{registration}/blank-form', [ReportController::class, 'exportBlankForm'])->name('reports.blank');

    // Fleet view — semua role bisa lihat
    Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');

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
        Route::get('/fleet/create', [FleetController::class, 'create'])->name('fleet.create');
        Route::post('/fleet', [FleetController::class, 'store'])->name('fleet.store');
        Route::get('/fleet/{fleet}/edit', [FleetController::class, 'edit'])->name('fleet.edit');
        Route::put('/fleet/{fleet}', [FleetController::class, 'update'])->name('fleet.update');
        Route::patch('/fleet/{fleet}', [FleetController::class, 'update']);
        Route::delete('/fleet/{fleet}', [FleetController::class, 'destroy'])->name('fleet.destroy');

        // Airlines Management
        Route::get('/fleet/airlines/create', [FleetController::class, 'createAirline'])->name('airlines.create');
        Route::post('/fleet/airlines', [FleetController::class, 'storeAirline'])->name('airlines.store');
        Route::get('/fleet/airlines/{id}/edit', [FleetController::class, 'editAirline'])->name('airlines.edit');
        Route::put('/fleet/airlines/{id}', [FleetController::class, 'updateAirline'])->name('airlines.update');
        Route::delete('/fleet/airlines/{id}', [FleetController::class, 'destroyAirline'])->name('airlines.destroy');
    });

    // ============================================
    // SUPERADMIN-ONLY ROUTES
    // ============================================
    Route::middleware('superadmin')->group(function () {
        Route::get('/superadmin/users', [UserManagementController::class, 'index'])->name('superadmin.users');
        Route::post('/superadmin/users', [UserManagementController::class, 'store'])->name('superadmin.users.store');
        Route::put('/superadmin/users/{user}', [UserManagementController::class, 'update'])->name('superadmin.users.update');
        Route::delete('/superadmin/users/{user}', [UserManagementController::class, 'destroy'])->name('superadmin.users.destroy');
        Route::post('/superadmin/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('superadmin.users.suspend');
        Route::post('/superadmin/users/{user}/unsuspend', [UserManagementController::class, 'unsuspend'])->name('superadmin.users.unsuspend');

        // Bulk Import
        Route::get('/superadmin/bulk-import', [BulkImportController::class, 'index'])->name('superadmin.bulk-import');
        Route::post('/superadmin/bulk-import', [BulkImportController::class, 'import'])->name('superadmin.bulk-import.process');
        Route::get('/superadmin/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('superadmin.bulk-import.template');

        // PDF Scan
        Route::get('/superadmin/pdf-scan', [PdfScanController::class, 'index'])->name('superadmin.pdf-scan');
        Route::post('/superadmin/pdf-scan', [PdfScanController::class, 'scan'])->name('superadmin.pdf-scan.process');
        Route::get('/superadmin/pdf-scan/clear', [PdfScanController::class, 'clearScan'])->name('superadmin.pdf-scan.clear');
        Route::post('/superadmin/pdf-scan/export', [PdfScanController::class, 'exportExcel'])->name('superadmin.pdf-scan.export');
    });
});
