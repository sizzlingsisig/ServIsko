<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class GetSkillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string|in:name,created_at',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Per page must not exceed 100.',
            'sort.in' => 'Sort must be name or created_at.',
        ];
    }
}
