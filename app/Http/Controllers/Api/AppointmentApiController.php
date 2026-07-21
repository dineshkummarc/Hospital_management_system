<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AppointmentApiController
 *
 * REST API endpoints for appointment management.
 */
class AppointmentApiController extends Controller
{
    /**
     * GET /api/appointments
     * Returns a filtered list of appointments.
     */
    public function filterAppointments(Request $request): JsonResponse
    {
        if ($this->isMysql()) {
            $rows = DB::select('CALL SP_filter_appointments(?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->input('search'),
                $request->input('status'),
                $request->input('doctorID'),
                $request->input('departmentID'),
                $request->input('date_from'),
                $request->input('date_to'),
                $request->input('limit', 20),
                $request->input('offset', 0),
            ]);
            return response()->json(['status' => 'success', 'data' => $rows]);
        }

        // Eloquent fallback
        $query = Appointment::with(['patient', 'doctor.department']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', fn($q) => $q
                ->where('firstName', 'like', "%{$term}%")
                ->orWhere('lastName', 'like', "%{$term}%")
            )->orWhereHas('doctor', fn($q) => $q->where('fullName', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('doctorID')) {
            $query->where('doctorID', $request->doctorID);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('appointmentDate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointmentDate', '<=', $request->date_to);
        }

        $appointments = $query->orderByDesc('appointmentDate')
                              ->orderByDesc('appointmentTime')
                              ->paginate(20);

        return response()->json(['status' => 'success', 'data' => $appointments]);
    }

    /**
     * GET /api/appointments/{id}
     * Full details for a single appointment.
     */
    public function getAppointmentDetails(int $id): JsonResponse
    {
        if ($this->isMysql()) {
            $rows = DB::select('CALL SP_get_appointment_details(?)', [$id]);
            if (empty($rows)) {
                return response()->json(['status' => 'error', 'message' => 'Appointment not found.'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $rows[0]]);
        }

        $appointment = Appointment::with(['patient', 'doctor.department'])->find($id);
        if (! $appointment) {
            return response()->json(['status' => 'error', 'message' => 'Appointment not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $appointment]);
    }

    /**
     * POST /api/appointments
     * Saves (inserts or updates) an appointment.
     */
    public function saveAppointment(Request $request): JsonResponse
    {
        $appointmentID = (int) $request->input('appointmentID', 0);

        $data = $request->validate([
            'patientID'       => 'required|exists:patients,patientID',
            'doctorID'        => 'required|exists:doctors,doctorID',
            'appointmentDate' => 'required|date',
            'appointmentTime' => 'nullable|date_format:H:i',
            'reason'          => 'nullable|string|max:500',
            'status'          => 'nullable|in:pending,confirmed,completed,cancelled',
        ]);

        if ($this->isMysql()) {
            // Check for duplicate
            if ($appointmentID === 0) {
                DB::select('CALL SP_check_appointment(?, ?, ?, ?, ?, @exists)', [
                    $data['patientID'],
                    $data['doctorID'],
                    $data['appointmentDate'],
                    $data['appointmentTime'] ?? null,
                    null,
                ]);
                $exists = DB::select('SELECT @exists AS exists')[0]->exists ?? 0;
                if ($exists) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'An appointment for this patient with this doctor at the same time already exists.',
                    ], 422);
                }
            }

            DB::statement('CALL SP_save_appointment(?, ?, ?, ?, ?, ?, ?, @result_id)', [
                $appointmentID,
                $data['patientID'],
                $data['doctorID'],
                $data['appointmentDate'],
                $data['appointmentTime'] ?? null,
                $data['reason']          ?? null,
                $data['status']          ?? 'pending',
            ]);
            $resultId    = DB::select('SELECT @result_id AS result_id')[0]->result_id ?? null;
            $appointment = Appointment::with(['patient', 'doctor'])->find($resultId);
        } else {
            if ($appointmentID > 0) {
                $appointment = Appointment::findOrFail($appointmentID);
                $appointment->update($data);
            } else {
                $appointment = Appointment::create(array_merge($data, ['status' => $data['status'] ?? 'pending']));
            }
            $appointment->load(['patient', 'doctor']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $appointmentID > 0 ? 'Appointment updated successfully.' : 'Appointment saved successfully.',
            'data'    => $appointment,
        ], $appointmentID > 0 ? 200 : 201);
    }

    /**
     * DELETE /api/appointments/{id}
     */
    public function deleteAppointment(int $id): JsonResponse
    {
        $appointment = Appointment::find($id);
        if (! $appointment) {
            return response()->json(['status' => 'error', 'message' => 'Appointment not found.'], 404);
        }

        if ($this->isMysql()) {
            DB::statement('CALL SP_delete_appointment(?, @affected)', [$id]);
        } else {
            $appointment->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Appointment deleted successfully.',
        ]);
    }

    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
