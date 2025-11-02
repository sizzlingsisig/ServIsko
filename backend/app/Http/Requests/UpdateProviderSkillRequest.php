<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('service-provider');
    }

    public function rules(): array
    {
        return [
            'proficiency_level' => 'required|string|in:beginner,intermediate,advanced,expert',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'proficiency_level.required' => 'Proficiency level is required.',
            'proficiency_level.in' => 'Proficiency level must be beginner, intermediate, advanced, or expert.',
            'years_of_experience.min' => 'Years of experience cannot be negative.',
            'years_of_experience.max' => 'Years of experience cannot exceed 50.',
            'years_of_experience.integer' => 'Years of experience must be a whole number.',
        ];
    }
}
