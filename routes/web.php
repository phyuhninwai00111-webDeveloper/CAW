<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimesheetController;
use App\Models\Department;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', [
            'departments' => Department::orderBy('department_name')->get(),
            'isHr' => (int) auth()->user()->role_id === 1,
        ]);
    })->name('dashboard');

    Route::get('/attendance', function () {
        return view('attendance');
    })->name('attendance');

    Route::get('/attendance/data', [AttendanceController::class, 'index'])->name('attendance.data');
    Route::get('/dashboard/summary', [AttendanceController::class, 'summary'])->name('dashboard.summary');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
    Route::get('/timesheets/{report_code}', [TimesheetController::class, 'show'])->name('timesheets.show');
    Route::put('/timesheets/{report_code}', [TimesheetController::class, 'update'])->name('timesheets.update');

    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
