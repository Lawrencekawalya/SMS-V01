<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReopenMarkSheetRequest extends FormRequest
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
            'senate_approval_reference' => ['required', 'string', 'min:3', 'max:100'],
            'reopen_justification' => ['required', 'string', 'min:10', 'max:1000'],
            'confirm_authorization' => ['accepted'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'senate_approval_reference.required' => 'A Senate Approval Reference or Minute Number is mandatory to reopen published results.',
            'senate_approval_reference.min' => 'The Senate approval reference must be at least 3 characters long.',
            'reopen_justification.required' => 'A detailed academic justification and appeal summary is required to unlock published results.',
            'reopen_justification.min' => 'The academic justification must be at least 10 characters long.',
            'confirm_authorization.accepted' => 'You must officially certify that this reopening is approved by Senate or the Academic Registrar.',
        ];
    }
}
