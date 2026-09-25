<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseUnitRequest extends FormRequest
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
            'department_id' => ['required', 'exists:departments,id'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('course_units', 'code')->ignore($this->route('course_unit')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'credit_units' => ['required', 'numeric', 'between:1.0,15.0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,archived'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }
    }
}
