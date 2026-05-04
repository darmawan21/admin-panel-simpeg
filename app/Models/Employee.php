<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'role',
        'enrollment_status',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── JWT Interface ──────────────────────────────────────────────────

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'employee_id' => $this->employee_id,
            'role' => $this->role,
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function faceEmbedding()
    {
        return $this->hasOne(FaceEmbedding::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function syncErrors()
    {
        return $this->hasMany(SyncError::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function isEnrolled(): bool
    {
        return $this->enrollment_status === 'enrolled';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }
}
