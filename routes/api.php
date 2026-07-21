<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PatientApiController;
use App\Http\Controllers\Api\DoctorApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\StoredProcedureController;

/*
|--------------------------------------------------------------------------
| Hospital Management System — REST API Routes (Laravel Sanctum Protected)
|--------------------------------------------------------------------------
|
| All mutation routes are guarded by api.role middleware.
| Public:  POST /api/login
| Private: All other routes require auth:sanctum token.
|
*/

// ─── Public — No Auth Required ────────────────────────────────────────────────
Route::post('/login', [AuthApiController::class, 'login']);

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // ─── Patients ─────────────────────────────────────────────────────────────
    Route::get('/patients',    [PatientApiController::class, 'filterPatients']);
    Route::get('/patients/{id}', [PatientApiController::class, 'getPatientDetails']);

    Route::middleware('api.role:admin,receptionist,treasurer')->group(function () {
        Route::post('/patients',       [PatientApiController::class, 'savePatient']);
        Route::put('/patients/{id}',   [PatientApiController::class, 'savePatient']);
        Route::delete('/patients/{id}',[PatientApiController::class, 'deletePatient']);
    });

    // ─── Doctors ──────────────────────────────────────────────────────────────
    Route::get('/doctors',     [DoctorApiController::class, 'filterDoctors']);
    Route::get('/doctors/{id}',[DoctorApiController::class, 'getDoctorDetails']);

    Route::middleware('api.role:admin')->group(function () {
        Route::post('/doctors',        [DoctorApiController::class, 'saveDoctor']);
        Route::put('/doctors/{id}',    [DoctorApiController::class, 'saveDoctor']);
        Route::delete('/doctors/{id}', [DoctorApiController::class, 'deleteDoctor']);
    });

    // ─── Departments ──────────────────────────────────────────────────────────
    Route::get('/departments',     [DepartmentApiController::class, 'filterDepartments']);
    Route::get('/departments/{id}',[DepartmentApiController::class, 'getDepartmentDetails']);

    Route::middleware('api.role:admin')->group(function () {
        Route::post('/departments',        [DepartmentApiController::class, 'saveDepartment']);
        Route::put('/departments/{id}',    [DepartmentApiController::class, 'saveDepartment']);
        Route::delete('/departments/{id}', [DepartmentApiController::class, 'deleteDepartment']);
    });

    // ─── Appointments ─────────────────────────────────────────────────────────
    Route::get('/appointments',     [AppointmentApiController::class, 'filterAppointments']);
    Route::get('/appointments/{id}',[AppointmentApiController::class, 'getAppointmentDetails']);

    Route::middleware('api.role:admin,receptionist,doctor,treasurer,coach')->group(function () {
        Route::post('/appointments',        [AppointmentApiController::class, 'saveAppointment']);
        Route::put('/appointments/{id}',    [AppointmentApiController::class, 'saveAppointment']);
        Route::delete('/appointments/{id}', [AppointmentApiController::class, 'deleteAppointment']);
    });

    // ─── Users ────────────────────────────────────────────────────────────────
    Route::get('/users',        [UserApiController::class, 'index']);
    Route::get('/users/{user}', [UserApiController::class, 'show']);

    Route::middleware('api.role:admin')->group(function () {
        Route::post('/users',          [UserApiController::class, 'store']);
        Route::put('/users/{user}',    [UserApiController::class, 'update']);
        Route::delete('/users/{user}', [UserApiController::class, 'destroy']);
    });

    // ─── Profile ──────────────────────────────────────────────────────────────
    Route::get('/profile',       [ProfileApiController::class, 'show']);
    Route::put('/profile',       [ProfileApiController::class, 'update']);
    Route::post('/profile/avatar',[ProfileApiController::class, 'updateAvatar']);

    // ─── Stored Procedure Endpoints ───────────────────────────────────────────
    Route::prefix('sp')->group(function () {
        // Patients
        Route::get('/patient/{id}/details',     [StoredProcedureController::class, 'patientDetails']);
        Route::post('/patient/save',            [StoredProcedureController::class, 'savePatient']);

        // Doctors
        Route::get('/doctor/{id}/details',      [StoredProcedureController::class, 'doctorDetails']);

        // Departments
        Route::get('/department/{id}/details',  [StoredProcedureController::class, 'departmentDetails']);

        // Appointments
        Route::get('/appointments/filter',      [StoredProcedureController::class, 'filterAppointments']);

        // Reporting
        Route::get('/users/activity-report',    [StoredProcedureController::class, 'userActivityReport']);
    });
});
