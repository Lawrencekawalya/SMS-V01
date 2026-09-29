<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectRegistrationRequest extends FormRequest
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
            'advisor_remarks' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'advisor_remarks.required' => 'Feedback remarks explaining why this registration slip is being rejected are mandatory.',
            'advisor_remarks.min' => 'Advisor remarks must be at least 10 characters in length to provide actionable feedback.',
            'advisor_remarks.max' => 'Advisor remarks cannot exceed 1000 characters.',
        ];
    }
}
