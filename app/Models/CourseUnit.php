<?php

namespace App\Models;

use Database\Factories\CourseUnitFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseUnit extends Model
{
    /** @use HasFactory<CourseUnitFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'code',
        'name',
        'credit_units',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_units' => 'decimal:1',
        ];
    }

    /**
     * Interact with the course unit code attribute (ensures uppercase).
     *
     * @return Attribute<string, string>
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper(trim($value)),
        );
    }

    /**
     * Get the department that owns this course unit.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get curriculum course entries for this course unit.
     *
     * @return HasMany<CurriculumCourse, $this>
     */
    public function curriculumCourses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class);
    }

    /**
     * Get curriculums that include this course unit.
     *
     * @return BelongsToMany<Curriculum, $this>
     */
    public function curriculums(): BelongsToMany
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_courses')
            ->withPivot(['id', 'study_year', 'semester', 'course_type'])
            ->withTimestamps();
    }

    /**
     * Get the prerequisite courses required before taking this course unit.
     *
     * @return BelongsToMany<CourseUnit, $this>
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            CourseUnit::class,
            'course_prerequisites',
            'course_unit_id',
            'prerequisite_course_unit_id'
        )->withTimestamps();
    }

    /**
     * Get the courses that require this course unit as a prerequisite.
     *
     * @return BelongsToMany<CourseUnit, $this>
     */
    public function prerequisiteFor(): BelongsToMany
    {
        return $this->belongsToMany(
            CourseUnit::class,
            'course_prerequisites',
            'prerequisite_course_unit_id',
            'course_unit_id'
        )->withTimestamps();
    }

    /**
     * Scope a query to only include active course units.
     *
     * @param  Builder<CourseUnit>  $query
     * @return Builder<CourseUnit>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
