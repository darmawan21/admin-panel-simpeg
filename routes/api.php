<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SIMPEG Face Recognition Attendance
|--------------------------------------------------------------------------
*/

// ── Public Routes ──────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Protected Routes (JWT Auth) ────────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/employees/me', [AuthController::class, 'me']);

    // Attendance
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/sync', [AttendanceController::class, 'sync']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);

    // Work Sites
    Route::get('/sites', [SiteController::class, 'index']);
});
