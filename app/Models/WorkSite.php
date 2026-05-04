<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSite extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'geofence_radius',
        'geofence_policy',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'site_id');
    }
}
