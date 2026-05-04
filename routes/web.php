<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:web')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Employee CRUD
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);

        // Face Enrollment
        Route::get('employees/{employee}/enroll', [\App\Http\Controllers\Admin\EnrollmentController::class, 'create'])->name('employees.enroll.create');
        Route::post('employees/{employee}/enroll', [\App\Http\Controllers\Admin\EnrollmentController::class, 'store'])->name('employees.enroll.store');

        // Work Sites CRUD
        Route::resource('sites', \App\Http\Controllers\Admin\SiteController::class);

        // Attendance Reports
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    });
});