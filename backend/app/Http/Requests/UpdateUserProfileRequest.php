<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'bio.max' => 'Bio must not exceed 500 characters.',
            'location.max' => 'Location must not exceed 255 characters.',
        ];
    }
}
