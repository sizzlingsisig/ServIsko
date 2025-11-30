<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['active', 'closed', 'expired'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'min_budget' => ['nullable', 'numeric', 'min:0'],
            'max_budget' => ['nullable', 'numeric', 'min:0', 'gte:min_budget'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'has_hired_user' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The status must be either active, closed, or expired.',
            'category_id.exists' => 'The selected category does not exist.',
            'search.max' => 'The search query cannot exceed 255 characters.',
            'min_budget.numeric' => 'The minimum budget must be a valid number.',
            'max_budget.numeric' => 'The maximum budget must be a valid number.',
            'max_budget.gte' => 'The maximum budget must be greater than or equal to the minimum budget.',
            'tags.array' => 'Tags must be provided as an array.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
            'per_page.min' => 'Results per page must be at least 1.',
            'per_page.max' => 'Results per page cannot exceed 100.',
            'page.min' => 'Page number must be at least 1.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'min_budget' => 'minimum budget',
            'max_budget' => 'maximum budget',
            'per_page' => 'results per page',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string "true"/"false" to boolean for has_hired_user
        if ($this->has('has_hired_user')) {
            $this->merge([
                'has_hired_user' => filter_var($this->has_hired_user, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
