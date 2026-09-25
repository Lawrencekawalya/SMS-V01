<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
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
            'semester_number' => [
                'required',
                'integer',
                'between:1,4',
                Rule::unique('semesters')->where(fn ($query) => $query->where('academic_year_id', $this->academic_year_id)),
            ],
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'registration_start_date' => ['nullable', 'date'],
            'registration_end_date' => ['nullable', 'date', 'after_or_equal:registration_start_date', 'before_or_equal:end_date'],
            'add_drop_deadline' => ['nullable', 'date', 'after_or_equal:registration_start_date', 'before_or_equal:end_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
