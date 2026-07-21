<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Patient Model
 *
 * Represents a registered patient in the hospital system.
 *
 * @property int    $patientID
 * @property string $firstName
 * @property string $lastName
 * @property string $gender
 * @property string $dateOfBirth
 * @property string $phone
 * @property string $email
 * @property string $address
 * @property int    $departmentID
 * @property string $createdDate
 * @property string $status
 */
class Patient extends Model
{
    protected $primaryKey = 'patientID';

    protected $fillable = [
        'firstName', 'lastName', 'gender', 'dateOfBirth',
        'phone', 'email', 'address', 'departmentID',
        'createdDate', 'status',
    ];

    protected function casts(): array
    {
        return [
            'dateOfBirth' => 'date',
            'createdDate' => 'date',
        ];
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->dateOfBirth
            ? $this->dateOfBirth->age
            : null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'     => 'success',
            'discharged' => 'secondary',
            'deceased'   => 'dark',
            default      => 'secondary',
        };
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male'   => 'Male',
            'female' => 'Female',
            'other'  => 'Other',
            default  => '—',
        };
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'departmentID', 'departmentID');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patientID', 'patientID');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('firstName', 'like', "%{$term}%")
              ->orWhere('lastName',  'like', "%{$term}%")
              ->orWhere('phone',     'like', "%{$term}%")
              ->orWhere('email',     'like', "%{$term}%");
        });
    }
}
