<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'embedding',
        'latitude',
        'longitude',
        'accuracy',
        'device_timestamp',
        'verified',
        'verification_score',
        'synced_from_offline',
        'site_id',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy' => 'decimal:2',
            'device_timestamp' => 'datetime',
            'verified' => 'boolean',
            'verification_score' => 'decimal:4',
            'synced_from_offline' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function workSite()
    {
        return $this->belongsTo(WorkSite::class, 'site_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('device_timestamp', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('device_timestamp', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('device_timestamp', now()->month)
                     ->whereYear('device_timestamp', now()->year);
    }

    public function scopeByEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeClockIn(Builder $query): Builder
    {
        return $query->where('type', 'clock_in');
    }

    public function scopeClockOut(Builder $query): Builder
    {
        return $query->where('type', 'clock_out');
    }
}
