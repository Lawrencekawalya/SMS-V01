<?php

namespace App\Rules;

use App\Services\CourseEligibilityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidSemesterCreditLoad implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(public ?float $maxCredits = null, public ?float $minCredits = null) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $credits = (float) $value;
        $min = $this->minCredits ?? CourseEligibilityService::MIN_SEMESTER_CREDITS;
        $max = $this->maxCredits ?? CourseEligibilityService::MAX_SEMESTER_CREDITS;

        if ($credits < $min) {
            $fail("The :attribute ({$credits} CU) must be at least {$min} CU.");
        }

        if ($credits > $max) {
            $fail("The :attribute ({$credits} CU) may not exceed {$max} CU.");
        }
    }
}
