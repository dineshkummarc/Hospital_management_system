<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * SchedulingService
 *
 * Handles intelligent appointment scheduling and doctor availability
 * management for the Hospital Management System.
 */
class SchedulingService
{
    /**
     * Get available doctors for a given department and date.
     *
     * @param int    $departmentId
     * @param string $date         Date in Y-m-d format
     * @return Collection
     */
    public function getAvailableDoctors(int $departmentId, string $date): Collection
    {
        return User::where('role', 'doctor')
            ->where('is_active', true)
            ->whereDoesntHave('appointments', function ($q) use ($date) {
                $q->where('appointment_date', $date)
                  ->where('status', 'scheduled');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Calculate doctor workload for a given date range.
     *
     * @param int    $doctorId
     * @param string $from   Start date in Y-m-d format
     * @param string $to     End date in Y-m-d format
     * @return array         Workload summary
     */
    public function getDoctorWorkload(int $doctorId, string $from, string $to): array
    {
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$from, $to])
            ->get();

        $byStatus = $appointments->groupBy('status');

        return [
            'doctor_id'  => $doctorId,
            'period'     => "$from to $to",
            'total'      => $appointments->count(),
            'scheduled'  => $byStatus->get('scheduled', collect())->count(),
            'completed'  => $byStatus->get('completed', collect())->count(),
            'cancelled'  => $byStatus->get('cancelled', collect())->count(),
            'no_show'    => $byStatus->get('no_show', collect())->count(),
        ];
    }

    /**
     * Get upcoming appointments within the next N days, sorted by datetime.
     *
     * @param int $days  Number of days to look ahead (default 7)
     * @param int $limit Maximum results to return
     * @return Collection
     */
    public function getUpcomingAppointments(int $days = 7, int $limit = 20): Collection
    {
        return Appointment::with(['patient', 'doctor', 'department'])
            ->where('appointment_date', '>=', now()->format('Y-m-d'))
            ->where('appointment_date', '<=', now()->addDays($days)->format('Y-m-d'))
            ->where('status', 'scheduled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Summarise overall appointment statistics for dashboard.
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'today_total'     => Appointment::where('appointment_date', $today)->count(),
            'today_scheduled' => Appointment::where('appointment_date', $today)->where('status', 'scheduled')->count(),
            'today_completed' => Appointment::where('appointment_date', $today)->where('status', 'completed')->count(),
            'today_cancelled' => Appointment::where('appointment_date', $today)->where('status', 'cancelled')->count(),
            'total_doctors'   => User::where('role', 'doctor')->where('is_active', true)->count(),
        ];
    }
}
