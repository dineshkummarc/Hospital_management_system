<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Doctor Model
 *
 * Represents a doctor/medical staff member in the hospital.
 *
 * @property int    $doctorID
 * @property string $fullName
 * @property string $email
 * @property string $phone
 * @property string $specialization
 * @property int    $departmentID
 * @property string $status
 */
class Doctor extends Model
{
    protected $primaryKey = 'doctorID';

    protected $fillable = [
        'fullName', 'email', 'phone', 'specialization',
        'departmentID', 'status',
    ];

    // ─── Computed Attributes ──────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'success',
            'on_leave' => 'warning',
            'inactive' => 'secondary',
            default    => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Active',
            'on_leave' => 'On Leave',
            'inactive' => 'Inactive',
            default    => ucfirst($this->status ?? ''),
        };
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'departmentID', 'departmentID');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctorID', 'doctorID');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('fullName',        'like', "%{$term}%")
              ->orWhere('email',         'like', "%{$term}%")
              ->orWhere('specialization','like', "%{$term}%");
        });
    }
}
