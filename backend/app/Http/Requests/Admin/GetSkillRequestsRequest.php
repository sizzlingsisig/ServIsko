<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetSkillRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:created_at,skill_name,user_name',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Per page must be an integer.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
            'status.in' => 'Status must be one of: pending, approved, rejected.',
            'sort_by.in' => 'Sort by must be one of: created_at, skill_name, user_name.',
        ];
    }
}
