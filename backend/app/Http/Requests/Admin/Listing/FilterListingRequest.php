<?php

namespace App\Http\Requests\Admin\Listing;

use Illuminate\Foundation\Http\FormRequest;

class FilterListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:active,closed,expired',
            'category_id' => 'sometimes|exists:categories,id',
            'seeker_id' => 'sometimes|exists:users,id',
            'has_hired_user' => 'sometimes|boolean',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be active, closed, or expired.',
            'category_id.exists' => 'Selected category does not exist.',
            'seeker_id.exists' => 'Selected user does not exist.',
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}
