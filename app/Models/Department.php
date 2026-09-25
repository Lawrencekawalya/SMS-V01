<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'faculty_id',
        'name',
        'code',
        'hod_user_id',
        'description',
        'status',
    ];

    /**
     * Get the faculty that owns the department.
     *
     * @return BelongsTo<Faculty, $this>
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the Head of Department (HOD) user.
     *
     * @return BelongsTo<User, $this>
     */
    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    /**
     * Get the programmes offered by the department.
     *
     * @return HasMany<Programme, $this>
     */
    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    /**
     * Get the course units offered by the department.
     *
     * @return HasMany<CourseUnit, $this>
     */
    public function courseUnits(): HasMany
    {
        return $this->hasMany(CourseUnit::class);
    }

    /**
     * Scope a query to only include active departments.
     *
     * @param  Builder<Department>  $query
     * @return Builder<Department>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
