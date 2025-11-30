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
            'tag_id' => 'sometimes|exists:tags,id',
            'has_hired_user' => 'sometimes|boolean',
            'include_expired' => 'sometimes|boolean',
            'created_from' => 'sometimes|date',
            'created_to' => 'sometimes|date|after_or_equal:created_from',
            'sort_by' => 'sometimes|in:created_at,updated_at,title,budget',
            'sort_order' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be active, closed, or expired.',
            'category_id.exists' => 'Selected category does not exist.',
            'seeker_id.exists' => 'Selected user does not exist.',
            'tag_id.exists' => 'Selected tag does not exist.',
            'has_hired_user.boolean' => 'Has hired user must be true or false.',
            'include_expired.boolean' => 'Include expired must be true or false.',
            'created_from.date' => 'Created from must be a valid date.',
            'created_to.date' => 'Created to must be a valid date.',
            'created_to.after_or_equal' => 'Created to must be after or equal to created from.',
            'sort_by.in' => 'Sort by must be created_at, updated_at, title, or budget.',
            'sort_order.in' => 'Sort order must be asc or desc.',
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}
