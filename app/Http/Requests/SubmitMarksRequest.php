<?php

namespace App\Http\Requests;

use App\Models\CourseAssessmentSheet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitMarksRequest extends FormRequest
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
        $sheet = $this->resolveSheet();
        $caMax = $sheet ? (float) $sheet->ca_weight : (float) config('academic.assessment_ca_weight', 40.0);
        $examMax = $sheet ? (float) $sheet->exam_weight : (float) config('academic.assessment_exam_weight', 60.0);

        return [
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_mark_id' => ['required', 'integer', 'exists:student_marks,id'],
            'marks.*.ca_score' => ['nullable', 'numeric', 'min:0', "max:{$caMax}"],
            'marks.*.exam_score' => ['nullable', 'numeric', 'min:0', "max:{$examMax}"],
            'marks.*.lecturer_remarks' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'in:save_draft,submit_hod'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $sheet = $this->resolveSheet();
        $caMax = $sheet ? (float) $sheet->ca_weight : 40.0;
        $examMax = $sheet ? (float) $sheet->exam_weight : 60.0;

        return [
            'marks.required' => 'At least one student record is required to submit marks.',
            'marks.*.ca_score.max' => "Continuous assessment (CA) score cannot exceed {$caMax} marks.",
            'marks.*.ca_score.min' => 'Continuous assessment (CA) score cannot be negative.',
            'marks.*.exam_score.max' => "Final examination score cannot exceed {$examMax} marks.",
            'marks.*.exam_score.min' => 'Final examination score cannot be negative.',
        ];
    }

    /**
     * Resolve the CourseAssessmentSheet instance from the route.
     */
    protected function resolveSheet(): ?CourseAssessmentSheet
    {
        $sheet = $this->route('sheet');

        if ($sheet instanceof CourseAssessmentSheet) {
            return $sheet;
        }

        if (is_numeric($sheet)) {
            return CourseAssessmentSheet::find($sheet);
        }

        return null;
    }
}
