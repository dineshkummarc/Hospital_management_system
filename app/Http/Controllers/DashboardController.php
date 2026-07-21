<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 *
 * Hospital management dashboard — shows key metrics for administrators
 * and a summary view for regular staff.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->staffDashboard($user);
    }

    // ─── Admin Dashboard ──────────────────────────────────────────────────────

    private function adminDashboard()
    {
        // Summary counts
        $totalPatients    = Patient::count();
        $totalDoctors     = Doctor::where('status', 'active')->count();
        $totalDepartments = Department::where('status', 'active')->count();
        $totalUsers       = User::where('is_active', true)->count();

        // Appointment stats
        $todayAppointments    = Appointment::whereDate('appointmentDate', today())->count();
        $pendingAppointments  = Appointment::where('status', 'pending')->count();
        $completedAppointments= Appointment::where('status', 'completed')->count();
        $cancelledAppointments= Appointment::where('status', 'cancelled')->count();

        // Recent patients (last 10)
        $recentPatients = Patient::with('department')
            ->latest()
            ->take(8)
            ->get();

        // Today's appointments with patient + doctor info
        $todayAppointmentList = Appointment::with(['patient', 'doctor.department'])
            ->whereDate('appointmentDate', today())
            ->orderBy('appointmentTime')
            ->take(10)
            ->get();

        // Recent appointments (last 10)
        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->latest()
            ->take(8)
            ->get();

        // Departments with stats
        $departments = Department::withCount(['doctors', 'patients'])
            ->where('status', 'active')
            ->get();

        // Monthly appointments chart (last 6 months) — MySQL / SQLite safe
        $isSQLite = DB::connection()->getDriverName() === 'sqlite';
        $monthFmt  = $isSQLite
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyData = Appointment::selectRaw("{$monthFmt} as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartMonths = collect();
        $chartCounts = collect();
        for ($i = 5; $i >= 0; $i--) {
            $dt  = now()->subMonths($i);
            $key = $dt->format('Y-m');
            $chartMonths->push($dt->format('M Y'));
            $chartCounts->push($monthlyData->get($key)?->total ?? 0);
        }

        // Appointment status distribution for pie chart
        $statusDistribution = [
            'labels' => ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
            'data'   => [
                $pendingAppointments,
                Appointment::where('status', 'confirmed')->count(),
                $completedAppointments,
                $cancelledAppointments,
            ],
        ];

        return view('dashboard.admin', compact(
            'totalPatients', 'totalDoctors', 'totalDepartments', 'totalUsers',
            'todayAppointments', 'pendingAppointments', 'completedAppointments', 'cancelledAppointments',
            'recentPatients', 'todayAppointmentList', 'recentAppointments',
            'departments',
            'chartMonths', 'chartCounts', 'statusDistribution'
        ));
    }

    // ─── Staff Dashboard ──────────────────────────────────────────────────────

    private function staffDashboard(User $user)
    {
        $todayAppointments   = Appointment::whereDate('appointmentDate', today())->count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $totalPatients       = Patient::where('status', 'active')->count();
        $totalDoctors        = Doctor::where('status', 'active')->count();

        $upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->upcoming()
            ->take(8)
            ->get();

        $recentPatients = Patient::with('department')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.staff', compact(
            'user', 'todayAppointments', 'pendingAppointments',
            'totalPatients', 'totalDoctors',
            'upcomingAppointments', 'recentPatients'
        ));
    }
}
