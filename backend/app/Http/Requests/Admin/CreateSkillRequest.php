<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skills', 'name'),
            ],
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Skill name is required.',
            'name.unique' => 'This skill name already exists.',
            'name.max' => 'Skill name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'category.max' => 'Category cannot exceed 255 characters.',
            'icon.max' => 'Icon cannot exceed 255 characters.',
        ];
    }
}
