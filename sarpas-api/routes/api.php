<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TechnicianTaskController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/facility-categories', [FacilityCategoryController::class, 'index']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/reports/quota', [ReportController::class, 'quota']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show'])->whereNumber('report');
    Route::post('/reports/{report}/messages', [ReportController::class, 'addMessage'])->whereNumber('report');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/reports', [AdminReportController::class, 'index']);
        Route::get('/reports/{report}', [AdminReportController::class, 'show']);
        Route::post('/reports/{report}/assign', [AdminReportController::class, 'assign']);
        Route::post('/reports/{report}/status', [AdminReportController::class, 'updateStatus']);
        Route::post('/reports/{report}/messages', [AdminReportController::class, 'addMessage']);
        Route::get('/technicians', [AdminReportController::class, 'technicians']);
        Route::get('/master/{type}', [MasterDataController::class, 'index']);
        Route::post('/master/{type}', [MasterDataController::class, 'store']);
        Route::put('/master/{type}/{id}', [MasterDataController::class, 'update']);
        Route::delete('/master/{type}/{id}', [MasterDataController::class, 'destroy']);
    });

    // Teknisi
    Route::middleware('role:teknisi')->prefix('technician')->group(function () {
        Route::get('/tasks', [TechnicianTaskController::class, 'index']);
        Route::get('/tasks/{report}', [TechnicianTaskController::class, 'show']);
        Route::post('/tasks/{report}/status', [TechnicianTaskController::class, 'updateStatus']);
    });
});
