<?php

namespace App\Models;

use Database\Factories\StudentMarkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentMark extends Model
{
    /** @use HasFactory<StudentMarkFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_assessment_sheet_id',
        'course_registration_item_id',
        'student_id',
        'ca_score',
        'exam_score',
        'final_score',
        'grade_letter',
        'grade_point',
        'is_passed',
        'is_retake',
        'lecturer_remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ca_score' => 'float',
            'exam_score' => 'float',
            'final_score' => 'float',
            'grade_point' => 'float',
            'is_passed' => 'boolean',
            'is_retake' => 'boolean',
        ];
    }

    /**
     * Get the parent course assessment sheet.
     *
     * @return BelongsTo<CourseAssessmentSheet, $this>
     */
    public function courseAssessmentSheet(): BelongsTo
    {
        return $this->belongsTo(CourseAssessmentSheet::class);
    }

    /**
     * Get the course registration item linking this mark to enrollment.
     *
     * @return BelongsTo<CourseRegistrationItem, $this>
     */
    public function registrationItem(): BelongsTo
    {
        return $this->belongsTo(CourseRegistrationItem::class, 'course_registration_item_id');
    }

    /**
     * Get the student associated with this mark.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the audit logs recording changes to this mark.
     *
     * @return HasMany<GradeAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(GradeAuditLog::class);
    }

    /**
     * Get the Bootstrap badge class for the grade letter based on NCHE grading scale.
     */
    public function getGradeBadgeClassAttribute(): string
    {
        if (! $this->grade_letter) {
            return 'text-bg-secondary';
        }

        if (class_exists(GradingScaleTier::class)) {
            $tier = GradingScaleTier::where('grade_letter', $this->grade_letter)->first();
            if ($tier) {
                return $tier->badge_class;
            }
        }

        $scales = config('academic.grading_scale', []);
        foreach ($scales as $scale) {
            if ($scale['grade_letter'] === $this->grade_letter) {
                return $scale['badge_class'];
            }
        }

        return match ($this->grade_letter) {
            'A', 'B+' => 'text-bg-success',
            'B', 'C+' => 'text-bg-primary',
            'C' => 'text-bg-info',
            'D+', 'D' => 'text-bg-warning',
            default => 'text-bg-danger',
        };
    }

    /**
     * Scope query to passed marks.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopePassed(Builder $query): Builder
    {
        return $query->where('is_passed', true);
    }

    /**
     * Scope query to failed marks.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('is_passed', false)->whereNotNull('final_score');
    }

    /**
     * Scope query to retakes.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopeRetakes(Builder $query): Builder
    {
        return $query->where('is_retake', true);
    }
}
