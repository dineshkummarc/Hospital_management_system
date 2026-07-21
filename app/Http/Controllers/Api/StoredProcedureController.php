<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * StoredProcedureController
 *
 * Exposes dedicated REST endpoints that invoke the hospital MySQL stored procedures.
 * All routes require a valid Sanctum API token (auth:sanctum middleware).
 *
 * Routes (registered in routes/api.php):
 *   GET  /api/sp/patient/{id}/details          → SP_get_patient_details
 *   GET  /api/sp/doctor/{id}/details           → SP_get_doctor_details
 *   GET  /api/sp/department/{id}/details       → SP_get_department_details
 *   GET  /api/sp/appointments/filter           → SP_filter_appointments
 *   POST /api/sp/patient/save                  → SP_save_patient
 *   GET  /api/sp/users/activity-report         → sp_get_user_activity_report
 */
class StoredProcedureController extends Controller
{
    // ─── Patient Procedures ───────────────────────────────────────────────────

    /**
     * GET /api/sp/patient/{id}/details
     * Returns full details for a patient using SP_get_patient_details.
     */
    public function patientDetails(int $id): JsonResponse
    {
        $rows = DB::select('CALL SP_get_patient_details(?)', [$id]);

        if (empty($rows)) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $rows[0],
        ]);
    }

    /**
     * POST /api/sp/patient/save
     * Body: { patientID, firstName, lastName, gender, dateOfBirth, phone, email, address, departmentID, status }
     */
    public function savePatient(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patientID'    => 'nullable|integer',
            'firstName'    => 'required|string|max:80',
            'lastName'     => 'required|string|max:80',
            'gender'       => 'nullable|in:male,female,other',
            'dateOfBirth'  => 'nullable|date',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string',
            'departmentID' => 'nullable|integer',
            'status'       => 'nullable|string',
        ]);

        DB::statement('CALL SP_save_patient(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @result_id)', [
            (int) ($data['patientID'] ?? 0),
            $data['firstName'],
            $data['lastName'],
            $data['gender']       ?? null,
            $data['dateOfBirth']  ?? null,
            $data['phone']        ?? null,
            $data['email']        ?? null,
            $data['address']      ?? null,
            $data['departmentID'] ?? null,
            $data['status']       ?? 'active',
        ]);

        $resultId = DB::select('SELECT @result_id AS result_id')[0]->result_id ?? null;

        return response()->json([
            'success'    => true,
            'patient_id' => $resultId,
            'message'    => 'Patient saved successfully.',
        ]);
    }

    /**
     * GET /api/sp/appointments/filter
     * Query params: search, status, doctorID, departmentID, date_from, date_to, limit, offset
     */
    public function filterAppointments(Request $request): JsonResponse
    {
        $limit  = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);

        $rows = DB::select('CALL SP_filter_appointments(?, ?, ?, ?, ?, ?, ?, ?)', [
            $request->query('search'),
            $request->query('status'),
            $request->query('doctorID'),
            $request->query('departmentID'),
            $request->query('date_from'),
            $request->query('date_to'),
            $limit,
            $offset,
        ]);

        return response()->json([
            'success' => true,
            'count'   => count($rows),
            'data'    => $rows,
        ]);
    }

    // ─── Doctor Procedures ────────────────────────────────────────────────────

    /**
     * GET /api/sp/doctor/{id}/details
     */
    public function doctorDetails(int $id): JsonResponse
    {
        $rows = DB::select('CALL SP_get_doctor_details(?)', [$id]);

        if (empty($rows)) {
            return response()->json(['message' => 'Doctor not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $rows[0]]);
    }

    // ─── Department Procedures ────────────────────────────────────────────────

    /**
     * GET /api/sp/department/{id}/details
     */
    public function departmentDetails(int $id): JsonResponse
    {
        $rows = DB::select('CALL SP_get_department_details(?)', [$id]);

        if (empty($rows)) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $rows[0]]);
    }

    // ─── Reporting ────────────────────────────────────────────────────────────

    /**
     * GET /api/sp/users/activity-report
     * Admin only — returns all users with role and account status.
     */
    public function userActivityReport(): JsonResponse
    {
        if (! auth()->user()?->hasRole(['admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $rows = DB::select('CALL sp_get_user_activity_report()');

        return response()->json([
            'success' => true,
            'count'   => count($rows),
            'data'    => $rows,
        ]);
    }
}
