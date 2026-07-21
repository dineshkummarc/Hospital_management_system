<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * DashboardApiController
 *
 * Returns hospital management dashboard statistics as JSON.
 * Used by the mobile API and any AJAX dashboard widgets.
 */
class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin() || in_array($user->role, ['admin', 'doctor', 'coach', 'receptionist', 'treasurer'])) {
            return $this->adminDashboardData();
        }

        return $this->staffDashboardData($user);
    }

    // ─── Admin / Doctor Dashboard Data ───────────────────────────────────────

    private function adminDashboardData()
    {
        $totalPatients         = Patient::count();
        $totalDoctors          = Doctor::where('status', 'active')->count();
        $totalDepartments      = Department::where('status', 'active')->count();
        $todayAppointments     = Appointment::whereDate('appointmentDate', today())->count();
        $pendingAppointments   = Appointment::where('status', 'pending')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();

        $recentPatients = Patient::with('department')
            ->latest()->take(5)->get()
            ->map(fn($p) => [
                'patientID'   => $p->patientID,
                'fullName'    => $p->full_name,
                'gender'      => $p->gender,
                'phone'       => $p->phone,
                'status'      => $p->status,
                'department'  => $p->department?->departmentName,
            ]);

        $upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->upcoming()->take(5)->get()
            ->map(fn($a) => [
                'appointmentID'   => $a->appointmentID,
                'appointmentDate' => $a->appointmentDate?->format('Y-m-d'),
                'appointmentTime' => $a->appointmentTime,
                'patientName'     => $a->patient?->full_name,
                'doctorName'      => $a->doctor?->fullName,
                'status'          => $a->status,
            ]);

        return response()->json([
            'status' => 'success',
            'role'   => auth()->user()->role,
            'data'   => [
                'total_patients'          => $totalPatients,
                'total_doctors'           => $totalDoctors,
                'total_departments'       => $totalDepartments,
                'today_appointments'      => $todayAppointments,
                'pending_appointments'    => $pendingAppointments,
                'completed_appointments'  => $completedAppointments,
                'recent_patients'         => $recentPatients,
                'upcoming_appointments'   => $upcomingAppointments,
            ],
        ]);
    }

    // ─── Staff Dashboard Data ─────────────────────────────────────────────────

    private function staffDashboardData(User $user)
    {
        $todayAppointments   = Appointment::whereDate('appointmentDate', today())->count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();

        $upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->upcoming()->take(5)->get()
            ->map(fn($a) => [
                'appointmentID'   => $a->appointmentID,
                'appointmentDate' => $a->appointmentDate?->format('Y-m-d'),
                'appointmentTime' => $a->appointmentTime,
                'patientName'     => $a->patient?->full_name,
                'doctorName'      => $a->doctor?->fullName,
                'status'          => $a->status,
            ]);

        return response()->json([
            'status' => 'success',
            'role'   => $user->role,
            'data'   => [
                'today_appointments'    => $todayAppointments,
                'pending_appointments'  => $pendingAppointments,
                'upcoming_appointments' => $upcomingAppointments,
            ],
        ]);
    }
}
