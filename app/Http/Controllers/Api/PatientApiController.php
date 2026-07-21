<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * PatientApiController
 *
 * REST API endpoints for patient management.
 * All responses use the standard JSON format:
 *   { "status": "success"|"error", "message": "...", "data": ... }
 */
class PatientApiController extends Controller
{
    // ─── filterPatients ───────────────────────────────────────────────────────

    /**
     * GET /api/patients
     * Returns a filtered, paginated list of patients.
     */
    public function filterPatients(Request $request): JsonResponse
    {
        $query = Patient::with('department');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('departmentID')) {
            $query->where('departmentID', $request->departmentID);
        }

        $patients = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $patients,
        ]);
    }

    // ─── getPatientDetails ────────────────────────────────────────────────────

    /**
     * GET /api/patients/{id}
     * Returns full details for a single patient using the stored procedure.
     */
    public function getPatientDetails(int $id): JsonResponse
    {
        if ($this->isMysql()) {
            $rows = DB::select('CALL SP_get_patient_details(?)', [$id]);
            if (empty($rows)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Patient not found.',
                ], 404);
            }
            return response()->json(['status' => 'success', 'data' => $rows[0]]);
        }

        // SQLite fallback (testing/dev)
        $patient = Patient::with('department')->find($id);
        if (! $patient) {
            return response()->json(['status' => 'error', 'message' => 'Patient not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $patient]);
    }

    // ─── savePatient ──────────────────────────────────────────────────────────

    /**
     * POST /api/patients
     * Saves (inserts or updates) a patient record.
     * If patientID is present and > 0, performs an UPDATE; otherwise INSERT.
     */
    public function savePatient(Request $request): JsonResponse
    {
        $patientID = (int) $request->input('patientID', 0);

        $rules = [
            'firstName'    => 'required|string|max:80',
            'lastName'     => 'required|string|max:80',
            'gender'       => 'nullable|in:male,female,other',
            'dateOfBirth'  => 'nullable|date|before:today',
            'phone'        => [
                'nullable', 'string', 'max:20',
                $patientID
                    ? Rule::unique('patients', 'phone')->ignore($patientID, 'patientID')
                    : Rule::unique('patients', 'phone'),
            ],
            'email'        => [
                'nullable', 'email', 'max:150',
                $patientID
                    ? Rule::unique('patients', 'email')->ignore($patientID, 'patientID')
                    : Rule::unique('patients', 'email'),
            ],
            'address'      => 'nullable|string|max:500',
            'departmentID' => 'nullable|exists:departments,departmentID',
            'status'       => 'nullable|in:active,discharged,deceased',
        ];

        $data = $request->validate($rules);

        if ($this->isMysql()) {
            // Check for duplicate via stored procedure
            if ($patientID === 0) {
                $check = DB::select('CALL SP_check_patient(?, ?, @exists)', [
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                ]);
                $exists = DB::select('SELECT @exists AS exists')[0]->exists ?? 0;
                if ($exists) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'A patient with this phone or email already exists.',
                    ], 422);
                }
            }

            // Save via stored procedure
            DB::statement('CALL SP_save_patient(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @result_id)', [
                $patientID,
                $data['firstName'],
                $data['lastName'],
                $data['gender']      ?? null,
                $data['dateOfBirth'] ?? null,
                $data['phone']       ?? null,
                $data['email']       ?? null,
                $data['address']     ?? null,
                $data['departmentID'] ?? null,
                $data['status']      ?? 'active',
            ]);
            $resultId = DB::select('SELECT @result_id AS result_id')[0]->result_id ?? null;

            $patient = Patient::with('department')->find($resultId);
        } else {
            // Eloquent fallback
            if ($patientID > 0) {
                $patient = Patient::findOrFail($patientID);
                $patient->update($data);
            } else {
                $patient = Patient::create(array_merge($data, ['status' => $data['status'] ?? 'active']));
            }
            $patient->load('department');
        }

        return response()->json([
            'status'  => 'success',
            'message' => $patientID > 0 ? 'Patient updated successfully.' : 'Patient saved successfully.',
            'data'    => $patient,
        ], $patientID > 0 ? 200 : 201);
    }

    // ─── deletePatient ────────────────────────────────────────────────────────

    /**
     * DELETE /api/patients/{id}
     * Deletes a patient and their appointments.
     */
    public function deletePatient(int $id): JsonResponse
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Patient not found.',
            ], 404);
        }

        if ($this->isMysql()) {
            DB::statement('CALL SP_delete_patient(?, @affected)', [$id]);
        } else {
            $patient->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Patient deleted successfully.',
        ]);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
