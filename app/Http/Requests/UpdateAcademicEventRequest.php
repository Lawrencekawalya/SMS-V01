<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicEventRequest extends FormRequest
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
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'title' => ['required', 'string', 'max:150'],
            'event_type' => ['required', 'in:academic_deadline,examination,lecture_period,holiday,governance,ceremony'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_all_day' => ['nullable', 'boolean'],
            'target_audience' => ['required', 'in:all,students,lecturers,freshers,staff'],
            'is_holiday' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
            'color' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_all_day' => $this->boolean('is_all_day', true),
            'is_holiday' => $this->boolean('is_holiday', false),
        ]);
    }
}
