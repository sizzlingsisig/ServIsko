<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:skills,name',
            'description' => 'nullable|string|max:1000',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Skill name is required.',
            'name.unique' => 'Skill already exists.',
            'name.max' => 'Skill name must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'reason.required' => 'Please provide a reason for requesting this skill.',
            'reason.max' => 'Reason must not exceed 500 characters.',
        ];
    }
}
