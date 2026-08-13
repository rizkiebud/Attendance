<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\LeaveController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Absensi KPPN
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // Refresh harus publik: token lama sudah kedaluwarsa saat dipakai,
    // middleware auth:api menolak sebelum controller refresh sempat jalan.
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Protected routes (require JWT auth)
Route::middleware(['auth:api'])->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Absensi
    Route::prefix('attendance')->group(function () {
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/offices', [AttendanceController::class, 'offices']);
    });

    // Izin / Leave
    Route::prefix('leave')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::get('/{id}', [LeaveController::class, 'show']);
        Route::get('/pending/list', [LeaveController::class, 'pending']);
        Route::post('/{id}/approve', [LeaveController::class, 'approve']);
        Route::post('/{id}/reject', [LeaveController::class, 'reject']);
    });
});
