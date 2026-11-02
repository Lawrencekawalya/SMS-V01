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
        'exam_status',
        'sitting_type',
        'special_exam_score',
        'special_exam_date',
        'special_exam_remarks',
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
            'special_exam_score' => 'float',
            'special_exam_date' => 'date',
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
            'INC' => 'text-bg-warning text-dark',
            'ABS' => 'text-bg-danger',
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

    /**
     * Check if this paper was missed on approved medical or official permission.
     */
    public function isMissedWithPermission(): bool
    {
        return $this->exam_status === 'missed_permission';
    }

    /**
     * Check if the student was absent without permission (unexcused).
     */
    public function isAbsentWithoutPermission(): bool
    {
        return $this->exam_status === 'absent';
    }

    /**
     * Check if this mark record was sat as a Special Examination.
     */
    public function isSpecialExam(): bool
    {
        return $this->sitting_type === 'special';
    }

    /**
     * Check if this mark record was sat as a Supplementary Examination.
     */
    public function isSupplementaryExam(): bool
    {
        return $this->sitting_type === 'supplementary';
    }

    /**
     * Get a human-readable badge for the examination attendance/sitting status.
     */
    public function getExamStatusBadgeAttribute(): string
    {
        return match ($this->exam_status) {
            'missed_permission' => '<span class="badge text-bg-warning text-dark"><i class="bi bi-hospital me-1"></i>INC (Medical/Permitted)</span>',
            'absent' => '<span class="badge text-bg-danger"><i class="bi bi-x-circle me-1"></i>ABS (Unexcused)</span>',
            default => '<span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>',
        };
    }

    /**
     * Get a human-readable badge for the sitting type.
     */
    public function getSittingTypeBadgeAttribute(): string
    {
        return match ($this->sitting_type) {
            'special' => '<span class="badge text-bg-info text-dark"><i class="bi bi-star-fill me-1"></i>Special Exam</span>',
            'supplementary' => '<span class="badge text-bg-primary"><i class="bi bi-arrow-repeat me-1"></i>Supplementary</span>',
            default => '<span class="badge text-bg-light border text-muted">Main Sitting</span>',
        };
    }

    /**
     * Scope query to marks missed with permission awaiting Special Examination.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopeMissedWithPermission(Builder $query): Builder
    {
        return $query->where('exam_status', 'missed_permission');
    }

    /**
     * Scope query to unexcused absences.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopeAbsentWithoutPermission(Builder $query): Builder
    {
        return $query->where('exam_status', 'absent');
    }

    /**
     * Scope query to special or supplementary examination records.
     *
     * @param  Builder<StudentMark>  $query
     * @return Builder<StudentMark>
     */
    public function scopeSpecialExams(Builder $query): Builder
    {
        return $query->whereIn('sitting_type', ['special', 'supplementary']);
    }
}
