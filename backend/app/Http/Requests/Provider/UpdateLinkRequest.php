<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'url' => 'required|url|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Link title is required.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'url.required' => 'Link URL is required.',
            'url.url' => 'URL must be a valid URL.',
            'url.max' => 'URL cannot exceed 2048 characters.',
        ];
    }
}
