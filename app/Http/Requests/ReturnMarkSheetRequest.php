<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReturnMarkSheetRequest extends FormRequest
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
            'remarks' => ['required', 'string', 'min:10', 'max:1000'],
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
            'remarks.required' => 'A detailed moderation revision note is mandatory when returning a mark sheet to the lecturer.',
            'remarks.min' => 'Moderation revision remarks must be at least 10 characters long.',
            'remarks.max' => 'Moderation revision remarks cannot exceed 1000 characters.',
        ];
    }
}
