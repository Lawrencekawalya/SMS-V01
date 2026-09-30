<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScaleTier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'grade_letter',
        'min_score',
        'max_score',
        'grade_point',
        'classification',
        'badge_class',
        'is_pass',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_score' => 'float',
            'max_score' => 'float',
            'grade_point' => 'float',
            'is_pass' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Default Uganda NCHE 5.0 Standard grading scale definition.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultTiers(): array
    {
        return [
            [
                'grade_letter' => 'A',
                'min_score' => 80.0,
                'max_score' => 100.0,
                'grade_point' => 5.0,
                'classification' => 'Exceptional / Distinction',
                'badge_class' => 'text-bg-success',
                'is_pass' => true,
                'sort_order' => 1,
            ],
            [
                'grade_letter' => 'B+',
                'min_score' => 75.0,
                'max_score' => 79.99,
                'grade_point' => 4.5,
                'classification' => 'Very Good',
                'badge_class' => 'text-bg-success',
                'is_pass' => true,
                'sort_order' => 2,
            ],
            [
                'grade_letter' => 'B',
                'min_score' => 70.0,
                'max_score' => 74.99,
                'grade_point' => 4.0,
                'classification' => 'Good',
                'badge_class' => 'text-bg-primary',
                'is_pass' => true,
                'sort_order' => 3,
            ],
            [
                'grade_letter' => 'C+',
                'min_score' => 65.0,
                'max_score' => 69.99,
                'grade_point' => 3.5,
                'classification' => 'Fairly Good',
                'badge_class' => 'text-bg-info',
                'is_pass' => true,
                'sort_order' => 4,
            ],
            [
                'grade_letter' => 'C',
                'min_score' => 60.0,
                'max_score' => 64.99,
                'grade_point' => 3.0,
                'classification' => 'Clear Pass',
                'badge_class' => 'text-bg-info',
                'is_pass' => true,
                'sort_order' => 5,
            ],
            [
                'grade_letter' => 'D+',
                'min_score' => 55.0,
                'max_score' => 59.99,
                'grade_point' => 2.5,
                'classification' => 'Marginal Pass',
                'badge_class' => 'text-bg-warning',
                'is_pass' => true,
                'sort_order' => 6,
            ],
            [
                'grade_letter' => 'D',
                'min_score' => 50.0,
                'max_score' => 54.99,
                'grade_point' => 2.0,
                'classification' => 'Pass (Minimum Passing Grade)',
                'badge_class' => 'text-bg-warning',
                'is_pass' => true,
                'sort_order' => 7,
            ],
            [
                'grade_letter' => 'F',
                'min_score' => 0.0,
                'max_score' => 49.99,
                'grade_point' => 0.0,
                'classification' => 'Fail (Requires Retake)',
                'badge_class' => 'text-bg-danger',
                'is_pass' => false,
                'sort_order' => 8,
            ],
        ];
    }

    /**
     * Seed or reset default NCHE 5.0 tiers into database.
     */
    public static function seedDefaults(): void
    {
        static::truncate();

        foreach (static::defaultTiers() as $tier) {
            static::create($tier);
        }
    }

    /**
     * Scope query ordered by highest score descending.
     *
     * @param  Builder<GradingScaleTier>  $query
     * @return Builder<GradingScaleTier>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('min_score');
    }
}
