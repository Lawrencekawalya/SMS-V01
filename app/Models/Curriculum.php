<?php

namespace App\Models;

use Database\Factories\CurriculumFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    /** @use HasFactory<CurriculumFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'curriculums';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'programme_id',
        'version_name',
        'start_academic_year',
        'end_academic_year',
        'min_graduation_credits',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_academic_year' => 'integer',
            'end_academic_year' => 'integer',
            'min_graduation_credits' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the programme this curriculum belongs to.
     *
     * @return BelongsTo<Programme, $this>
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Get the curriculum courses mapped to this curriculum.
     *
     * @return HasMany<CurriculumCourse, $this>
     */
    public function curriculumCourses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class);
    }

    /**
     * Get the course units associated with this curriculum.
     *
     * @return BelongsToMany<CourseUnit, $this>
     */
    public function courseUnits(): BelongsToMany
    {
        return $this->belongsToMany(CourseUnit::class, 'curriculum_courses')
            ->withPivot(['id', 'study_year', 'semester', 'course_type'])
            ->withTimestamps();
    }

    /**
     * Get the students following this curriculum version.
     *
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope a query to only include active curriculums.
     *
     * @param  Builder<Curriculum>  $query
     * @return Builder<Curriculum>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate total mapped credit units for the entire curriculum.
     */
    public function totalMappedCredits(): float
    {
        return (float) $this->courseUnits()->sum('credit_units');
    }

    /**
     * Calculate total credit load for a specific study year and semester.
     */
    public function totalCreditsForStage(int $studyYear, int $semester): float
    {
        return (float) $this->courseUnits()
            ->wherePivot('study_year', $studyYear)
            ->wherePivot('semester', $semester)
            ->sum('credit_units');
    }
}
