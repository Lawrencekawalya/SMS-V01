<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurriculumRequest extends FormRequest
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
            'programme_id' => ['required', 'exists:programmes,id'],
            'version_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('curriculums')->where('programme_id', $this->programme_id),
            ],
            'start_academic_year' => ['required', 'integer', 'between:2000,2100'],
            'end_academic_year' => ['nullable', 'integer', 'gte:start_academic_year', 'between:2000,2100'],
            'min_graduation_credits' => ['required', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
