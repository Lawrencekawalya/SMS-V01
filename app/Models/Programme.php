<?php

namespace App\Models;

use Database\Factories\ProgrammeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Programme extends Model
{
    /** @use HasFactory<ProgrammeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'award_type',
        'duration_years',
        'required_credits_to_graduate',
        'description',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_years' => 'integer',
            'required_credits_to_graduate' => 'integer',
        ];
    }

    /**
     * Get the department that offers this programme.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the curriculums defined for this programme.
     *
     * @return HasMany<Curriculum, $this>
     */
    public function curriculums(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    /**
     * Get the active curriculum for this programme.
     *
     * @return HasOne<Curriculum, $this>
     */
    public function activeCurriculum(): HasOne
    {
        return $this->hasOne(Curriculum::class)->where('is_active', true)->latestOfMany();
    }

    /**
     * Get the students enrolled in this programme.
     *
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope a query to only include active programmes.
     *
     * @param  Builder<Programme>  $query
     * @return Builder<Programme>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
