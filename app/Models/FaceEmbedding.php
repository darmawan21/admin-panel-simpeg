<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    protected $fillable = [
        'employee_id',
        'embedding',
        'enrollment_status',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'enrolled_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
