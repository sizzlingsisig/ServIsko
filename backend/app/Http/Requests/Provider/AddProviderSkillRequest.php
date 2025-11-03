<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddProviderSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skill_id' => [
                'required',
                'integer',
                Rule::exists('skills', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'skill_id.required' => 'Skill ID is required.',
            'skill_id.integer' => 'Skill ID must be an integer.',
            'skill_id.exists' => 'The selected skill does not exist.',
        ];
    }
}
