<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgrammeRequest extends FormRequest
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
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:programmes,code'],
            'award_type' => [
                'required',
                'string',
                Rule::in(['Certificate', 'Diploma', 'Bachelors', 'Postgraduate Diploma', 'Masters', 'Doctorate']),
            ],
            'duration_years' => ['required', 'integer', 'min:1', 'max:7'],
            'required_credits_to_graduate' => ['required', 'integer', 'min:10', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
