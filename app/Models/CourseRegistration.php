<?php

namespace App\Models;

use Database\Factories\CourseRegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseRegistration extends Model
{
    /** @use HasFactory<CourseRegistrationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'study_year',
        'semester_number',
        'total_credits',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by_user_id',
        'advisor_remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'study_year' => 'integer',
            'semester_number' => 'integer',
            'total_credits' => 'float',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the student who owns this registration slip.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the academic year for this registration.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester for this registration.
     *
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the advisor or registrar user who approved this registration.
     *
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Get the line items associated with this registration slip.
     *
     * @return HasMany<CourseRegistrationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CourseRegistrationItem::class);
    }

    /**
     * Get the course units registered under this slip through pivot items.
     *
     * @return BelongsToMany<CourseUnit, $this>
     */
    public function courseUnits(): BelongsToMany
    {
        return $this->belongsToMany(CourseUnit::class, 'course_registration_items')
            ->withPivot(['id', 'course_type', 'credit_units', 'status', 'dropped_at', 'drop_reason'])
            ->withTimestamps();
    }

    /**
     * Scope query to registrations awaiting academic advisor review.
     *
     * @param  Builder<CourseRegistration>  $query
     * @return Builder<CourseRegistration>
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'add_drop_pending']);
    }

    /**
     * Scope query to fully approved registrations.
     *
     * @param  Builder<CourseRegistration>  $query
     * @return Builder<CourseRegistration>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope query to registrations for a given semester.
     *
     * @param  Builder<CourseRegistration>  $query
     * @return Builder<CourseRegistration>
     */
    public function scopeForSemester(Builder $query, int $semesterId): Builder
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope query to a specific status.
     *
     * @param  Builder<CourseRegistration>  $query
     * @return Builder<CourseRegistration>
     */
    public function scopeInStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Check if this registration can be modified by the student.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected'], true);
    }

    /**
     * Check if the student can perform course Add/Drop on this registration.
     */
    public function canAddDrop(): bool
    {
        if (! in_array($this->status, ['approved', 'submitted', 'add_drop_pending'], true)) {
            return false;
        }

        return $this->semester?->isAddDropOpen() ?? false;
    }

    /**
     * Recalculate and update the total credit units from active registered items.
     */
    public function recalculateTotalCredits(): void
    {
        $total = (float) $this->items()
            ->where('status', '!=', 'dropped')
            ->sum('credit_units');

        $this->update(['total_credits' => $total]);
    }

    /**
     * Get Bootstrap 5 badge class for this registration status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'text-bg-secondary',
            'submitted' => 'text-bg-warning',
            'approved' => 'text-bg-success',
            'rejected' => 'text-bg-danger',
            'add_drop_pending' => 'text-bg-info',
            default => 'text-bg-light border',
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'approved' && ! config('academic.require_registration_approval', false)) {
            return 'Confirmed';
        }

        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'add_drop_pending' => 'Add/Drop Pending',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
