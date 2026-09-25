<?php

namespace App\Models;

use Database\Factories\AcademicEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicEvent extends Model
{
    /** @use HasFactory<AcademicEventFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'academic_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'title',
        'event_type',
        'start_date',
        'end_date',
        'is_all_day',
        'target_audience',
        'is_holiday',
        'description',
        'color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_all_day' => 'boolean',
            'is_holiday' => 'boolean',
        ];
    }

    /**
     * Get the academic year this event belongs to.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester this event is anchored to (if any).
     *
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Scope query to upcoming events starting from today onwards.
     *
     * @param  Builder<AcademicEvent>  $query
     * @return Builder<AcademicEvent>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now()->toDateString())->orderBy('start_date');
    }

    /**
     * Scope query to official holidays.
     *
     * @param  Builder<AcademicEvent>  $query
     * @return Builder<AcademicEvent>
     */
    public function scopeHolidays(Builder $query): Builder
    {
        return $query->where('is_holiday', true);
    }

    /**
     * Get the Bootstrap 5 contextual badge class based on event type.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->event_type) {
            'examination' => 'text-bg-danger',
            'academic_deadline' => 'text-bg-warning',
            'lecture_period' => 'text-bg-primary',
            'holiday' => 'text-bg-success',
            'governance' => 'text-bg-info',
            'ceremony' => 'text-bg-secondary',
            default => 'text-bg-light border',
        };
    }

    /**
     * Get the hex color code for calendar visualization.
     */
    public function getCalendarColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match ($this->event_type) {
            'examination' => '#dc3545',
            'academic_deadline' => '#fd7e14',
            'lecture_period' => '#0d6efd',
            'holiday' => '#198754',
            'governance' => '#0dcaf0',
            'ceremony' => '#6f42c1',
            default => '#6c757d',
        };
    }

    /**
     * Format event object for FullCalendar JavaScript feed.
     *
     * @return array<string, mixed>
     */
    public function toFullCalendarArray(): array
    {
        // FullCalendar end date for multi-day events is exclusive
        $end = $this->end_date ?? $this->start_date;
        $exclusiveEnd = (clone $end)->addDay()->toDateString();

        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'start' => $this->start_date->toDateString(),
            'end' => $exclusiveEnd,
            'allDay' => $this->is_all_day,
            'backgroundColor' => $this->calendar_color,
            'borderColor' => $this->calendar_color,
            'extendedProps' => [
                'eventType' => ucfirst(str_replace('_', ' ', $this->event_type)),
                'badgeClass' => $this->badge_class,
                'audience' => ucfirst($this->target_audience),
                'isHoliday' => $this->is_holiday,
                'description' => $this->description ?? '',
                'academicYear' => $this->academicYear?->name ?? '',
                'semester' => $this->semester?->name ?? 'Full Year',
                'rawStartDate' => $this->start_date->format('M d, Y'),
                'rawEndDate' => $this->end_date ? $this->end_date->format('M d, Y') : null,
            ],
        ];
    }
}
