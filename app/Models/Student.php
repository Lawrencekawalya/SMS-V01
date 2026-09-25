<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'registration_number',
        'student_number',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'national_id_nin',
        'campus_id',
        'programme_id',
        'curriculum_id',
        'admission_academic_year_id',
        'study_mode',
        'intake',
        'current_study_year',
        'current_semester',
        'status',
        'cumulative_gpa',
        'user_id',
        'academic_advisor_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'current_study_year' => 'integer',
            'current_semester' => 'integer',
            'cumulative_gpa' => 'float',
        ];
    }

    /**
     * Get the campus where the student is enrolled.
     *
     * @return BelongsTo<Campus, $this>
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the academic programme the student is enrolled in.
     *
     * @return BelongsTo<Programme, $this>
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Get the curriculum version this student is following.
     *
     * @return BelongsTo<Curriculum, $this>
     */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    /**
     * Get the academic year of admission.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function admissionAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'admission_academic_year_id');
    }

    /**
     * Get the student's assigned academic advisor.
     *
     * @return BelongsTo<User, $this>
     */
    public function academicAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }

    /**
     * Get the user account for portal login.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to only active students.
     *
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to students in a specific programme.
     *
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    public function scopeInProgramme(Builder $query, int $programmeId): Builder
    {
        return $query->where('programme_id', $programmeId);
    }

    /**
     * Scope query to students in a specific study year and semester.
     *
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    public function scopeInStage(Builder $query, int $studyYear, int $semester): Builder
    {
        return $query->where('current_study_year', $studyYear)
            ->where('current_semester', $semester);
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name} {$this->other_names}");
    }

    /**
     * Get the human-readable academic stage string (e.g. Year 1, Sem 1).
     */
    public function getAcademicStageAttribute(): string
    {
        return "Year {$this->current_study_year}, Sem {$this->current_semester}";
    }

    /**
     * Get Bootstrap status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'text-bg-success',
            'probation' => 'text-bg-warning',
            'suspended' => 'text-bg-danger',
            'graduated' => 'text-bg-primary',
            'withdrawn' => 'text-bg-secondary',
            default => 'text-bg-light border',
        };
    }

    /**
     * Check if student is in good standing and eligible to register for courses.
     */
    public function isEligibleForRegistration(): bool
    {
        return $this->status === 'active';
    }
}
