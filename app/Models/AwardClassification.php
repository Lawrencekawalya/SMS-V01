<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardClassification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'award_level',
        'name',
        'min_cgpa',
        'max_cgpa',
        'badge_class',
        'academic_standing',
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
            'min_cgpa' => 'float',
            'max_cgpa' => 'float',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Default award classifications for all tertiary programme levels.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultClassifications(): array
    {
        return [
            // 1. Bachelor's & Postgraduate Degrees (Standard Honours Scale)
            [
                'award_level' => 'degree',
                'name' => 'First Class Honours',
                'min_cgpa' => 4.40,
                'max_cgpa' => 5.00,
                'badge_class' => 'text-bg-success',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 1,
            ],
            [
                'award_level' => 'degree',
                'name' => 'Second Class Honours (Upper Division)',
                'min_cgpa' => 3.60,
                'max_cgpa' => 4.39,
                'badge_class' => 'text-bg-primary',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 2,
            ],
            [
                'award_level' => 'degree',
                'name' => 'Second Class Honours (Lower Division)',
                'min_cgpa' => 2.80,
                'max_cgpa' => 3.59,
                'badge_class' => 'text-bg-info',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 3,
            ],
            [
                'award_level' => 'degree',
                'name' => 'Pass Degree',
                'min_cgpa' => 2.00,
                'max_cgpa' => 2.79,
                'badge_class' => 'text-bg-warning',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 4,
            ],
            [
                'award_level' => 'degree',
                'name' => 'Fail / Academic Probation',
                'min_cgpa' => 0.00,
                'max_cgpa' => 1.99,
                'badge_class' => 'text-bg-danger',
                'academic_standing' => 'Probation',
                'sort_order' => 5,
            ],

            // 2. Undergraduate Diplomas (Class I, II, III Scale)
            [
                'award_level' => 'diploma',
                'name' => 'Class I (Distinction)',
                'min_cgpa' => 4.40,
                'max_cgpa' => 5.00,
                'badge_class' => 'text-bg-success',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 1,
            ],
            [
                'award_level' => 'diploma',
                'name' => 'Class II (Credit)',
                'min_cgpa' => 3.60,
                'max_cgpa' => 4.39,
                'badge_class' => 'text-bg-primary',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 2,
            ],
            [
                'award_level' => 'diploma',
                'name' => 'Class III (Pass)',
                'min_cgpa' => 2.00,
                'max_cgpa' => 3.59,
                'badge_class' => 'text-bg-info',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 3,
            ],
            [
                'award_level' => 'diploma',
                'name' => 'Fail / Academic Probation',
                'min_cgpa' => 0.00,
                'max_cgpa' => 1.99,
                'badge_class' => 'text-bg-danger',
                'academic_standing' => 'Probation',
                'sort_order' => 4,
            ],

            // 3. Certificates (Distinction, Credit, Pass Scale)
            [
                'award_level' => 'certificate',
                'name' => 'Distinction',
                'min_cgpa' => 4.40,
                'max_cgpa' => 5.00,
                'badge_class' => 'text-bg-success',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 1,
            ],
            [
                'award_level' => 'certificate',
                'name' => 'Credit',
                'min_cgpa' => 3.60,
                'max_cgpa' => 4.39,
                'badge_class' => 'text-bg-primary',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 2,
            ],
            [
                'award_level' => 'certificate',
                'name' => 'Pass',
                'min_cgpa' => 2.00,
                'max_cgpa' => 3.59,
                'badge_class' => 'text-bg-info',
                'academic_standing' => 'Normal Progress',
                'sort_order' => 3,
            ],
            [
                'award_level' => 'certificate',
                'name' => 'Fail / Academic Probation',
                'min_cgpa' => 0.00,
                'max_cgpa' => 1.99,
                'badge_class' => 'text-bg-danger',
                'academic_standing' => 'Probation',
                'sort_order' => 4,
            ],
        ];
    }

    /**
     * Seed or reset default award classifications into database.
     */
    public static function seedDefaults(): void
    {
        static::truncate();

        foreach (static::defaultClassifications() as $class) {
            static::create($class);
        }
    }

    /**
     * Scope query by award level.
     *
     * @param  Builder<AwardClassification>  $query
     * @return Builder<AwardClassification>
     */
    public function scopeForLevel(Builder $query, string $level): Builder
    {
        return $query->where('award_level', $level)->orderBy('sort_order');
    }
}
