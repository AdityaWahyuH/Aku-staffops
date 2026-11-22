<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/', fn () => auth()->check() && auth()->user()->role === 'staff'
    ? redirect()->route('staff-portal.index') : redirect()->route('dashboard'));

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('staff', StaffController::class)->except(['show', 'destroy']);
    Route::patch('/staff/{staff}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
    Route::resource('locations', LocationController::class)->except(['show', 'destroy']);
    Route::resource('shifts', ShiftController::class)->except(['show', 'destroy']);
    Route::get('/requirements', [RequirementController::class, 'index'])->name('requirements.index');
    Route::put('/requirements', [RequirementController::class, 'update'])->name('requirements.update');
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::put('/availability', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');
    Route::patch('/schedules/{assignment}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/schedules/publish-selected', [ScheduleController::class, 'publishSelected'])->name('schedules.publish');
    Route::post('/schedules/publish-day', [ScheduleController::class, 'publishDay'])->name('schedules.publish-day');
    Route::patch('/schedules/{assignment}/cancel', [ScheduleController::class, 'cancel'])->name('schedules.cancel');
    Route::get('/schedules/export/print', [ScheduleController::class, 'print'])->name('schedules.print');
    Route::get('/schedules/export/csv', [ScheduleController::class, 'csv'])->name('schedules.csv');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'csv'])->name('reports.csv');
    Route::get('/reports/export/print', [ReportController::class, 'print'])->name('reports.print');
});
Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/my-schedule', [StaffPortalController::class, 'index'])->name('staff-portal.index');
});
