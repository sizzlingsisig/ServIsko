<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'sometimes|in:active,closed,expired',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|min:2|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Title cannot exceed 255 characters.',
            'budget.numeric' => 'Budget must be a number.',
            'category_id.exists' => 'Selected category does not exist.',
            'status.in' => 'Invalid status.',
            'tags.max' => 'You can add a maximum of 10 tags.',
            'tags.*.min' => 'Each tag must be at least 2 characters.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ];
    }
}
