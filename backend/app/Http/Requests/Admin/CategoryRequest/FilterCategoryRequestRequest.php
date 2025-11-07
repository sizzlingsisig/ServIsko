<?php

namespace App\Http\Requests\Admin\CategoryRequest;

use Illuminate\Foundation\Http\FormRequest;

class FilterCategoryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:pending,approved,rejected',
            'user_id' => 'sometimes|exists:users,id',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be pending, approved, or rejected.',
            'user_id.exists' => 'Selected user does not exist.',
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}
