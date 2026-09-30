<?php

namespace App\Models;

use Database\Factories\CourseAssessmentSheetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseAssessmentSheet extends Model
{
    /** @use HasFactory<CourseAssessmentSheetFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_unit_id',
        'semester_id',
        'academic_year_id',
        'instructor_id',
        'ca_weight',
        'exam_weight',
        'pass_mark',
        'status',
        'submitted_at',
        'moderated_at',
        'moderated_by_id',
        'published_at',
        'published_by_id',
        'moderation_remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ca_weight' => 'float',
            'exam_weight' => 'float',
            'pass_mark' => 'float',
            'submitted_at' => 'datetime',
            'moderated_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the course unit for this mark sheet.
     *
     * @return BelongsTo<CourseUnit, $this>
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class);
    }

    /**
     * Get the semester session.
     *
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the academic year.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the lecturer/instructor responsible for entering marks.
     *
     * @return BelongsTo<User, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the HoD or examination officer who moderated the mark sheet.
     *
     * @return BelongsTo<User, $this>
     */
    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_id');
    }

    /**
     * Get the Registrar or Senate official who published the mark sheet.
     *
     * @return BelongsTo<User, $this>
     */
    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_id');
    }

    /**
     * Get individual student mark entries on this assessment sheet.
     *
     * @return HasMany<StudentMark, $this>
     */
    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }

    /**
     * Check if mark sheet is editable by the lecturer.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'returned_for_revision'], true);
    }

    /**
     * Check if mark sheet is ready for HoD moderation.
     */
    public function isSubmittedToHod(): bool
    {
        return $this->status === 'submitted_to_hod';
    }

    /**
     * Check if mark sheet has been officially published by Senate.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get Bootstrap 5 badge class for this assessment sheet status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'text-bg-secondary',
            'submitted_to_hod' => 'text-bg-warning',
            'department_moderated' => 'text-bg-info',
            'published' => 'text-bg-success',
            'returned_for_revision' => 'text-bg-danger',
            default => 'text-bg-light border',
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft (Lecturer)',
            'submitted_to_hod' => 'Pending HoD Moderation',
            'department_moderated' => 'Department Moderated',
            'published' => 'Senate Published',
            'returned_for_revision' => 'Returned for Revision',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Calculate class pass rate percentage.
     */
    public function getPassRateAttribute(): float
    {
        $total = $this->studentMarks()->whereNotNull('final_score')->count();
        if ($total === 0) {
            return 0.0;
        }

        $passed = $this->studentMarks()->where('is_passed', true)->count();

        return round(($passed / $total) * 100, 1);
    }

    /**
     * Calculate class average score.
     */
    public function getAverageScoreAttribute(): float
    {
        $avg = $this->studentMarks()->whereNotNull('final_score')->avg('final_score');

        return $avg ? round((float) $avg, 1) : 0.0;
    }

    /**
     * Scope query to sheets pending HoD moderation.
     *
     * @param  Builder<CourseAssessmentSheet>  $query
     * @return Builder<CourseAssessmentSheet>
     */
    public function scopePendingModeration(Builder $query): Builder
    {
        return $query->where('status', 'submitted_to_hod');
    }

    /**
     * Scope query to officially published sheets.
     *
     * @param  Builder<CourseAssessmentSheet>  $query
     * @return Builder<CourseAssessmentSheet>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
