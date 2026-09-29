<?php

namespace App\Models;

use Database\Factories\CourseRegistrationItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRegistrationItem extends Model
{
    /** @use HasFactory<CourseRegistrationItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_registration_id',
        'course_unit_id',
        'course_type',
        'credit_units',
        'status',
        'dropped_at',
        'drop_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_units' => 'float',
            'dropped_at' => 'datetime',
        ];
    }

    /**
     * Get the parent course registration slip.
     *
     * @return BelongsTo<CourseRegistration, $this>
     */
    public function courseRegistration(): BelongsTo
    {
        return $this->belongsTo(CourseRegistration::class);
    }

    /**
     * Get the course unit for this registration item.
     *
     * @return BelongsTo<CourseUnit, $this>
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class);
    }

    /**
     * Scope query to only active (non-dropped) registered courses.
     *
     * @param  Builder<CourseRegistrationItem>  $query
     * @return Builder<CourseRegistrationItem>
     */
    public function scopeActiveOnly(Builder $query): Builder
    {
        return $query->where('status', '!=', 'dropped');
    }

    /**
     * Scope query to only dropped courses.
     *
     * @param  Builder<CourseRegistrationItem>  $query
     * @return Builder<CourseRegistrationItem>
     */
    public function scopeDropped(Builder $query): Builder
    {
        return $query->where('status', 'dropped');
    }

    /**
     * Scope query to Core courses.
     *
     * @param  Builder<CourseRegistrationItem>  $query
     * @return Builder<CourseRegistrationItem>
     */
    public function scopeCore(Builder $query): Builder
    {
        return $query->where('course_type', 'Core');
    }

    /**
     * Scope query to Elective courses.
     *
     * @param  Builder<CourseRegistrationItem>  $query
     * @return Builder<CourseRegistrationItem>
     */
    public function scopeElective(Builder $query): Builder
    {
        return $query->where('course_type', 'Elective');
    }

    /**
     * Check if this course item was dropped.
     */
    public function isDropped(): bool
    {
        return $this->status === 'dropped';
    }

    /**
     * Check if this is a mandatory core course.
     */
    public function isCore(): bool
    {
        return $this->course_type === 'Core';
    }

    /**
     * Check if this is an elective course.
     */
    public function isElective(): bool
    {
        return $this->course_type === 'Elective';
    }

    /**
     * Get Bootstrap badge class for course type.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->course_type) {
            'Core' => 'text-bg-primary',
            'Elective' => 'text-bg-info',
            'Audited' => 'text-bg-secondary',
            'Retake' => 'text-bg-warning',
            default => 'text-bg-light border',
        };
    }

    /**
     * Get Bootstrap badge class for registration item status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'text-bg-success',
            'dropped' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}
