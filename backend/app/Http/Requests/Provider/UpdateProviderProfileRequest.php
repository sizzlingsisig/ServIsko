<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Profile title is required.',
            'title.string' => 'Profile title must be a string.',
            'title.min' => 'Profile title must be at least 3 characters.',
            'title.max' => 'Profile title cannot exceed 255 characters.',
        ];
    }
}
