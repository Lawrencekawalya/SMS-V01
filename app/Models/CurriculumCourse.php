<?php

namespace App\Models;

use Database\Factories\CurriculumCourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumCourse extends Model
{
    /** @use HasFactory<CurriculumCourseFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'curriculum_courses';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'curriculum_id',
        'course_unit_id',
        'study_year',
        'semester',
        'course_type',
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
            'semester' => 'integer',
        ];
    }

    /**
     * Get the curriculum this record belongs to.
     *
     * @return BelongsTo<Curriculum, $this>
     */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    /**
     * Get the course unit associated with this curriculum mapping.
     *
     * @return BelongsTo<CourseUnit, $this>
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class);
    }

    /**
     * Scope query to courses for a specific study year and semester.
     *
     * @param  Builder<CurriculumCourse>  $query
     * @return Builder<CurriculumCourse>
     */
    public function scopeForStage(Builder $query, int $studyYear, int $semester): Builder
    {
        return $query->where('study_year', $studyYear)->where('semester', $semester);
    }

    /**
     * Scope query to core courses only.
     *
     * @param  Builder<CurriculumCourse>  $query
     * @return Builder<CurriculumCourse>
     */
    public function scopeCore(Builder $query): Builder
    {
        return $query->where('course_type', 'Core');
    }

    /**
     * Scope query to elective courses only.
     *
     * @param  Builder<CurriculumCourse>  $query
     * @return Builder<CurriculumCourse>
     */
    public function scopeElective(Builder $query): Builder
    {
        return $query->where('course_type', 'Elective');
    }
}
