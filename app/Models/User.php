<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model — Hospital Management System
 *
 * Represents a system user (administrator, doctor, receptionist, or staff).
 * Authentication is unchanged: bcrypt password hashing, sessions, Sanctum tokens.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username', 'name', 'email', 'phone', 'role',
        'is_active', 'avatar', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isDoctor(): bool       { return $this->role === 'doctor'; }
    public function isReceptionist(): bool { return $this->role === 'receptionist'; }
    public function isStaff(): bool        { return $this->role === 'staff'; }

    /** Check if this user has any of the given roles. */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($this->role, $roles);
    }

    // ─── Role Display ─────────────────────────────────────────────────────────

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'         => 'Administrator',
            'doctor'        => 'Doctor',
            'receptionist'  => 'Receptionist',
            'staff'         => 'Staff',
            // Legacy role names kept for backward compatibility
            'coach'         => 'Doctor',
            'treasurer'     => 'Receptionist',
            'member'        => 'Staff',
            default         => ucfirst($this->role),
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            'admin'         => 'gold',
            'doctor',
            'coach'         => 'emerald',
            'receptionist',
            'treasurer'     => 'blue',
            'staff',
            'member'        => 'secondary',
            default         => 'secondary',
        };
    }

    // ─── Avatar ───────────────────────────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
              . '&background=0d6efd&color=fff&size=80';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }
}
