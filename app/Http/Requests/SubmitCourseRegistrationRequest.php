<?php

namespace App\Http\Requests;

use App\Models\CourseUnit;
use App\Models\Semester;
use App\Models\Student;
use App\Rules\ValidSemesterCreditLoad;
use App\Services\CourseEligibilityService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitCourseRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $targetStatus = $this->input('action_status', $this->input('status', 'submitted'));

        $totalCredits = $this->input('total_credits');
        if (($totalCredits === null || $totalCredits === '') && $this->has('course_unit_ids') && is_array($this->input('course_unit_ids'))) {
            $totalCredits = (float) CourseUnit::whereIn('id', $this->input('course_unit_ids'))->sum('credit_units');
        }

        $this->merge([
            'status' => $targetStatus,
            'total_credits' => $totalCredits !== null && $totalCredits !== '' ? (float) $totalCredits : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'course_unit_ids' => ['required', 'array', 'min:1'],
            'course_unit_ids.*' => ['required', 'integer', 'exists:course_units,id'],
            'status' => ['nullable', 'string', 'in:draft,submitted'],
            'action_status' => ['nullable', 'string', 'in:draft,submitted'],
        ];

        if ($this->input('status') === 'submitted') {
            $student = Student::find($this->input('student_id'));
            $semester = Semester::find($this->input('semester_id'));
            $minCredits = CourseEligibilityService::MIN_SEMESTER_CREDITS;
            $maxCredits = CourseEligibilityService::MAX_SEMESTER_CREDITS;
            if ($student && $semester) {
                $eligibility = app(CourseEligibilityService::class)->getEligibleCoursesForStudent($student, $semester);
                $minCredits = (float) ($eligibility['summary']['min_credits'] ?? $minCredits);
                $maxCredits = (float) ($eligibility['summary']['max_credits'] ?? $maxCredits);
            }

            $rules['total_credits'] = ['required', 'numeric', new ValidSemesterCreditLoad($maxCredits, $minCredits)];
        } else {
            $rules['total_credits'] = ['nullable', 'numeric'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance with institutional eligibility rules.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $student = Student::find($this->input('student_id'));
            $semester = Semester::find($this->input('semester_id'));

            if (! $student || ! $semester) {
                return;
            }

            // In submitted mode, enforce full eligibility and core completeness
            if ($this->input('status') === 'submitted') {
                $service = app(CourseEligibilityService::class);
                $validation = $service->validateCourseSelection(
                    $student,
                    $semester,
                    (array) $this->input('course_unit_ids', [])
                );

                if (! $validation['is_valid']) {
                    foreach ($validation['errors'] as $error) {
                        $v->errors()->add('course_unit_ids', $error);
                    }
                }
            }
        });
    }
}
