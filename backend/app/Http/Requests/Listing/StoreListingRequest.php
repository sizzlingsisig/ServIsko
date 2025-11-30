<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|min:2|max:50',
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'budget.numeric' => 'Budget must be a number.',
            'budget.min' => 'Budget must be at least 0.',
            'category_id.exists' => 'Selected category does not exist.',
            'tags.max' => 'You can add a maximum of 10 tags.',
            'tags.*.min' => 'Each tag must be at least 2 characters.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
            'expires_at.date' => 'Expiry date must be a valid date.',
            'expires_at.after' => 'Expiry date must be a future date.',
        ];
    }
}
