<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkCreateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skills' => 'required|array|min:1',
            'skills.*.name' => 'required|string|max:255|distinct',
            'skills.*.description' => 'nullable|string|max:1000',
            'skills.*.category' => 'nullable|string|max:255',
            'skills.*.icon' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'skills.required' => 'Skills array is required.',
            'skills.min' => 'At least one skill is required.',
            'skills.*.name.required' => 'Each skill must have a name.',
            'skills.*.name.distinct' => 'Duplicate skill names are not allowed.',
            'skills.*.name.max' => 'Skill name cannot exceed 255 characters.',
            'skills.*.description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }
}
