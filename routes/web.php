<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// ─── Public Landing Redirect ──────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (all roles)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Patients ─────────────────────────────────────────────────────────────
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');

    // ─── Doctors ──────────────────────────────────────────────────────────────
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');

    // ─── Departments ──────────────────────────────────────────────────────────
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');

    // ─── Appointments ─────────────────────────────────────────────────────────
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');

    // ─── Admin-only ───────────────────────────────────────────────────────────
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::post('users/{user}/role',   [UserController::class, 'assignRole'])->name('users.role');

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');

        // Settings
        Route::get('settings',  [SettingController::class, 'index'])->name('settings');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__ . '/auth.php';
