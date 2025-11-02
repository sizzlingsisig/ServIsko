<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ReorderLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'link_ids' => 'required|array|min:1',
            'link_ids.*' => 'required|string|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'link_ids.required' => 'Link IDs are required.',
            'link_ids.array' => 'Link IDs must be an array.',
            'link_ids.min' => 'At least one link ID is required.',
            'link_ids.*.required' => 'Each link ID is required.',
            'link_ids.*.numeric' => 'Each link ID must be numeric.',
        ];
    }
}
