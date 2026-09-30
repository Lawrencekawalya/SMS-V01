<?php

namespace App\Models;

use Database\Factories\StudentSemesterPerformanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSemesterPerformance extends Model
{
    /** @use HasFactory<StudentSemesterPerformanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'semester_id',
        'academic_year_id',
        'credit_units_registered',
        'credit_units_earned',
        'weighted_grade_points',
        'gpa',
        'cumulative_credit_units_registered',
        'cumulative_credit_units_earned',
        'cumulative_weighted_grade_points',
        'cgpa',
        'academic_standing',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_units_registered' => 'float',
            'credit_units_earned' => 'float',
            'weighted_grade_points' => 'float',
            'gpa' => 'float',
            'cumulative_credit_units_registered' => 'float',
            'cumulative_credit_units_earned' => 'float',
            'cumulative_weighted_grade_points' => 'float',
            'cgpa' => 'float',
        ];
    }

    /**
     * Get the student for this semester performance record.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
     * Check if the student is on normal progress.
     */
    public function isNormalProgress(): bool
    {
        return $this->academic_standing === 'Normal Progress';
    }

    /**
     * Check if the student is on academic probation.
     */
    public function isProbation(): bool
    {
        return $this->academic_standing === 'Probation';
    }

    /**
     * Get the Bootstrap badge class for academic standing.
     */
    public function getStandingBadgeClassAttribute(): string
    {
        return match ($this->academic_standing) {
            'Normal Progress' => 'text-bg-success',
            'Probation' => 'text-bg-warning',
            'Discontinued' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }

    /**
     * Scope query to students on probation.
     *
     * @param  Builder<StudentSemesterPerformance>  $query
     * @return Builder<StudentSemesterPerformance>
     */
    public function scopeProbation(Builder $query): Builder
    {
        return $query->where('academic_standing', 'Probation');
    }

    /**
     * Scope query to students on normal progress.
     *
     * @param  Builder<StudentSemesterPerformance>  $query
     * @return Builder<StudentSemesterPerformance>
     */
    public function scopeNormalProgress(Builder $query): Builder
    {
        return $query->where('academic_standing', 'Normal Progress');
    }
}
