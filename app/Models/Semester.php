<?php

namespace App\Models;

use Database\Factories\SemesterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Semester extends Model
{
    /** @use HasFactory<SemesterFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'semester_number',
        'name',
        'start_date',
        'end_date',
        'registration_start_date',
        'registration_end_date',
        'add_drop_deadline',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'semester_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start_date' => 'date',
            'registration_end_date' => 'date',
            'add_drop_deadline' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic year that owns the semester.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the scheduled academic events for this semester.
     *
     * @return HasMany<AcademicEvent, $this>
     */
    public function academicEvents(): HasMany
    {
        return $this->hasMany(AcademicEvent::class)->orderBy('start_date');
    }

    /**
     * Scope a query to only include the active semester.
     *
     * @param  Builder<Semester>  $query
     * @return Builder<Semester>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Activate this semester as the single active semester across the university.
     */
    public function activate(): void
    {
        DB::transaction(function (): void {
            static::where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);

            // Ensure parent academic year is set as current
            $this->academicYear->makeCurrent();
        });
    }

    /**
     * Check if registration is currently open for this semester.
     */
    public function isRegistrationOpen(): bool
    {
        if (! $this->registration_start_date || ! $this->registration_end_date) {
            return false;
        }

        $today = now()->startOfDay();

        return $today->gte($this->registration_start_date->startOfDay())
            && $today->lte($this->registration_end_date->endOfDay());
    }

    /**
     * Check if the add/drop course deadline is still open for this semester.
     */
    public function isAddDropOpen(): bool
    {
        if (! $this->add_drop_deadline) {
            return false;
        }

        return now()->startOfDay()->lte($this->add_drop_deadline->endOfDay());
    }
}
