<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * DoctorApiController
 *
 * REST API endpoints for doctor management.
 */
class DoctorApiController extends Controller
{
    /**
     * GET /api/doctors
     * Returns a filtered list of doctors.
     */
    public function filterDoctors(Request $request): JsonResponse
    {
        $query = Doctor::with('department');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('departmentID')) {
            $query->where('departmentID', $request->departmentID);
        }

        $doctors = $query->orderBy('fullName')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $doctors,
        ]);
    }

    /**
     * GET /api/doctors/{id}
     * Returns full details for a doctor using the stored procedure.
     */
    public function getDoctorDetails(int $id): JsonResponse
    {
        if ($this->isMysql()) {
            $rows = DB::select('CALL SP_get_doctor_details(?)', [$id]);
            if (empty($rows)) {
                return response()->json(['status' => 'error', 'message' => 'Doctor not found.'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $rows[0]]);
        }

        $doctor = Doctor::with('department')->find($id);
        if (! $doctor) {
            return response()->json(['status' => 'error', 'message' => 'Doctor not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $doctor]);
    }

    /**
     * POST /api/doctors
     * Saves (inserts or updates) a doctor.
     */
    public function saveDoctor(Request $request): JsonResponse
    {
        $doctorID = (int) $request->input('doctorID', 0);

        $data = $request->validate([
            'fullName'       => 'required|string|max:100',
            'email'          => [
                'nullable', 'email', 'max:150',
                $doctorID
                    ? Rule::unique('doctors', 'email')->ignore($doctorID, 'doctorID')
                    : Rule::unique('doctors', 'email'),
            ],
            'phone'          => [
                'nullable', 'string', 'max:20',
                $doctorID
                    ? Rule::unique('doctors', 'phone')->ignore($doctorID, 'doctorID')
                    : Rule::unique('doctors', 'phone'),
            ],
            'specialization' => 'nullable|string|max:100',
            'departmentID'   => 'nullable|exists:departments,departmentID',
            'status'         => 'nullable|in:active,inactive,on_leave',
        ]);

        if ($this->isMysql()) {
            if ($doctorID === 0) {
                $check = DB::select('CALL SP_check_doctor(?, ?, @exists)', [
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                ]);
                $exists = DB::select('SELECT @exists AS exists')[0]->exists ?? 0;
                if ($exists) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'A doctor with this email or phone already exists.',
                    ], 422);
                }
            }

            DB::statement('CALL SP_save_doctor(?, ?, ?, ?, ?, ?, ?, @result_id)', [
                $doctorID,
                $data['fullName'],
                $data['email']          ?? null,
                $data['phone']          ?? null,
                $data['specialization'] ?? null,
                $data['departmentID']   ?? null,
                $data['status']         ?? 'active',
            ]);
            $resultId = DB::select('SELECT @result_id AS result_id')[0]->result_id ?? null;
            $doctor   = Doctor::with('department')->find($resultId);
        } else {
            if ($doctorID > 0) {
                $doctor = Doctor::findOrFail($doctorID);
                $doctor->update($data);
            } else {
                $doctor = Doctor::create(array_merge($data, ['status' => $data['status'] ?? 'active']));
            }
            $doctor->load('department');
        }

        return response()->json([
            'status'  => 'success',
            'message' => $doctorID > 0 ? 'Doctor updated successfully.' : 'Doctor saved successfully.',
            'data'    => $doctor,
        ], $doctorID > 0 ? 200 : 201);
    }

    /**
     * DELETE /api/doctors/{id}
     * Deletes a doctor record.
     */
    public function deleteDoctor(int $id): JsonResponse
    {
        $doctor = Doctor::find($id);
        if (! $doctor) {
            return response()->json(['status' => 'error', 'message' => 'Doctor not found.'], 404);
        }

        if ($this->isMysql()) {
            DB::statement('CALL SP_delete_doctor(?, @affected)', [$id]);
        } else {
            $doctor->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Doctor deleted successfully.',
        ]);
    }

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
