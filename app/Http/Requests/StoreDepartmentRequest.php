<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
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
            'faculty_id' => ['required', 'exists:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')->where('faculty_id', $this->faculty_id),
            ],
            'hod_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
