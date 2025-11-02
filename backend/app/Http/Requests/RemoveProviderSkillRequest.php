<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveProviderSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('service-provider');
    }

    public function rules(): array
    {
        return [
            'skill_id' => 'required|integer|exists:skills,id',
        ];
    }

    public function messages(): array
    {
        return [
            'skill_id.required' => 'Skill ID is required.',
            'skill_id.exists' => 'Selected skill does not exist.',
        ];
    }
}
