<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class FilterListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category'   => ['nullable', 'integer', 'exists:categories,id'],
            'search'     => ['nullable', 'string', 'max:255'],
            'minBudget'  => ['nullable', 'numeric', 'min:0'],
            'maxBudget'  => ['nullable', 'numeric', 'min:0'],
            'sort_by'    => ['nullable', 'in:newest,oldest,price_low,price_high'],
            'page'       => ['nullable', 'integer', 'min:1'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'category.exists' => 'Selected category does not exist.',
            'search.max' => 'Search term cannot exceed 255 characters.',
            'minBudget.numeric' => 'Minimum budget must be a valid number.',
            'minBudget.min' => 'Minimum budget cannot be negative.',
            'maxBudget.numeric' => 'Maximum budget must be a valid number.',
            'maxBudget.min' => 'Maximum budget cannot be negative.',
            'sort_by.in' => 'Sort by must be one of: newest, oldest, price_low, price_high.',
            'page.integer' => 'Page must be a valid integer.',
            'page.min' => 'Page must be at least 1.',
            'per_page.integer' => 'Per page must be a valid integer.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}
