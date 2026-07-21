<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Appointment Model
 *
 * Represents a scheduled appointment between a patient and a doctor.
 *
 * @property int    $appointmentID
 * @property int    $patientID
 * @property int    $doctorID
 * @property string $appointmentDate
 * @property string $appointmentTime
 * @property string $reason
 * @property string $status
 * @property string $createdDate
 */
class Appointment extends Model
{
    protected $primaryKey = 'appointmentID';

    protected $fillable = [
        'patientID', 'doctorID', 'appointmentDate',
        'appointmentTime', 'reason', 'status', 'createdDate',
    ];

    protected function casts(): array
    {
        return [
            'appointmentDate' => 'date',
            'createdDate'     => 'date',
        ];
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status ?? ''),
        };
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->appointmentTime
            ? date('h:i A', strtotime($this->appointmentTime))
            : '—';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patientID', 'patientID');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctorID', 'doctorID');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('appointmentDate', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('appointmentDate', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed'])
                     ->orderBy('appointmentDate')
                     ->orderBy('appointmentTime');
    }
}
