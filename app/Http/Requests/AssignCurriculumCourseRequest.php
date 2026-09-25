<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignCurriculumCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $curriculum = $this->route('curriculum');
        $curriculumId = is_object($curriculum) ? $curriculum->id : $curriculum;

        return [
            'course_unit_id' => [
                'required',
                'exists:course_units,id',
                Rule::unique('curriculum_courses')->where('curriculum_id', $curriculumId),
            ],
            'study_year' => ['required', 'integer', 'min:1', 'max:7'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'course_type' => ['required', 'in:Core,Elective,Audited'],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_unit_id.unique' => 'This course unit is already assigned to this curriculum structure.',
        ];
    }
}
