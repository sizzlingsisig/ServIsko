<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('service-provider');
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Title must not exceed 255 characters.',
        ];
    }
}
