<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncError extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_log_id',
        'error_type',
        'error_message',
        'payload',
        'resolved',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'resolved' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceLog()
    {
        return $this->belongsTo(AttendanceLog::class);
    }
}
