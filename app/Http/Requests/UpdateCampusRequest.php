<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampusRequest extends FormRequest
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
        $campus = $this->route('campus');
        $universityId = $campus?->university_id ?? $this->input('university_id');

        return [
            'university_id' => ['sometimes', 'required', 'exists:universities,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('campuses', 'code')
                    ->where('university_id', $universityId)
                    ->ignore($campus?->id),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'is_main_campus' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
