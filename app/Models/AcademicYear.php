<?php

namespace App\Models;

use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Get the semesters for the academic year.
     *
     * @return HasMany<Semester, $this>
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class)->orderBy('semester_number');
    }

    /**
     * Get the scheduled academic events for this academic year.
     *
     * @return HasMany<AcademicEvent, $this>
     */
    public function academicEvents(): HasMany
    {
        return $this->hasMany(AcademicEvent::class)->orderBy('start_date');
    }

    /**
     * Scope a query to only include the current academic year.
     *
     * @param  Builder<AcademicYear>  $query
     * @return Builder<AcademicYear>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Make this academic year the single current year.
     */
    public function makeCurrent(): void
    {
        DB::transaction(function (): void {
            static::where('id', '!=', $this->id)->update(['is_current' => false]);
            $this->update(['is_current' => true]);
        });
    }
}
