<?php

namespace App\Models;

use Database\Factories\GradeAuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeAuditLog extends Model
{
    /** @use HasFactory<GradeAuditLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_mark_id',
        'changed_by_id',
        'score_type',
        'old_score',
        'new_score',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_score' => 'float',
            'new_score' => 'float',
        ];
    }

    /**
     * Get the student mark that was modified.
     *
     * @return BelongsTo<StudentMark, $this>
     */
    public function studentMark(): BelongsTo
    {
        return $this->belongsTo(StudentMark::class);
    }

    /**
     * Get the user (lecturer, HoD, Registrar) who performed the modification.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

    /**
     * Get a human-readable label for the score type.
     */
    public function getScoreTypeLabelAttribute(): string
    {
        return match ($this->score_type) {
            'ca' => 'Continuous Assessment (CA)',
            'exam' => 'Final Examination',
            'final' => 'Overall Final Score',
            default => strtoupper($this->score_type),
        };
    }
}
