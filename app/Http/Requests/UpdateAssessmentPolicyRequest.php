<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAssessmentPolicyRequest extends FormRequest
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
            'ca_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'exam_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'pass_mark' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ca_weight' => 'Continuous Assessment (CA) weight',
            'exam_weight' => 'Final Examination weight',
            'pass_mark' => 'Minimum Pass Mark',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $ca = (float) $this->input('ca_weight');
            $exam = (float) $this->input('exam_weight');

            if (round($ca + $exam, 2) !== 100.0) {
                $v->errors()->add(
                    'ca_weight',
                    'The sum of Continuous Assessment (CA) weight and Final Examination weight must equal exactly 100.0%.'
                );
            }
        });
    }
}
