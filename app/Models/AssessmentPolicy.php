<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentPolicy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'award_level',
        'ca_weight',
        'exam_weight',
        'pass_mark',
        'retake_cgpa_policy',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ca_weight' => 'float',
            'exam_weight' => 'float',
            'pass_mark' => 'float',
        ];
    }

    /**
     * Default institutional assessment policies for each tertiary programme tier.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultPolicies(): array
    {
        return [
            [
                'award_level' => 'degree',
                'ca_weight' => 40.0,
                'exam_weight' => 60.0,
                'pass_mark' => 50.0,
                'retake_cgpa_policy' => 'replace_fail',
            ],
            [
                'award_level' => 'postgraduate',
                'ca_weight' => 40.0,
                'exam_weight' => 60.0,
                'pass_mark' => 60.0,
                'retake_cgpa_policy' => 'replace_fail',
            ],
            [
                'award_level' => 'diploma',
                'ca_weight' => 40.0,
                'exam_weight' => 60.0,
                'pass_mark' => 50.0,
                'retake_cgpa_policy' => 'replace_fail',
            ],
            [
                'award_level' => 'certificate',
                'ca_weight' => 40.0,
                'exam_weight' => 60.0,
                'pass_mark' => 50.0,
                'retake_cgpa_policy' => 'replace_fail',
            ],
        ];
    }

    /**
     * Seed or reset default assessment policies into the database.
     */
    public static function seedDefaults(): void
    {
        foreach (static::defaultPolicies() as $policy) {
            static::updateOrCreate(
                ['award_level' => $policy['award_level']],
                $policy
            );
        }
    }

    /**
     * Retrieve or instantiate the policy for a given tertiary award level.
     */
    public static function forLevel(string $level): self
    {
        $policy = static::where('award_level', $level)->first();

        if ($policy) {
            return $policy;
        }

        $defaults = collect(static::defaultPolicies())->firstWhere('award_level', $level) ?? [
            'award_level' => $level,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => $level === 'postgraduate' ? 60.0 : 50.0,
            'retake_cgpa_policy' => 'replace_fail',
        ];

        return static::create($defaults);
    }
}
