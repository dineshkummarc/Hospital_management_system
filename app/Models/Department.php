<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Department Model
 *
 * Represents a hospital department (e.g., Cardiology, Pediatrics).
 *
 * @property int    $departmentID
 * @property string $departmentName
 * @property string $description
 * @property string $status
 */
class Department extends Model
{
    protected $primaryKey = 'departmentID';

    protected $fillable = [
        'departmentName', 'description', 'status',
    ];

    // ─── Computed Attributes ──────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'departmentID', 'departmentID');
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'departmentID', 'departmentID');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
