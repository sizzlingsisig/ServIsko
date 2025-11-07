<?php

namespace App\Http\Requests\Seeker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => 'nullable|string|max:255|min:2',
            'username' => [
                'nullable',
                'string',
                'max:255',
                'min:3',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[0-9\s\-\+\(\)]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.alpha_dash' => 'Username can only contain letters, numbers, dashes, and underscores.',
            'username.unique' => 'This username is already taken.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already in use.',
            'bio.max' => 'Bio cannot exceed 500 characters.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'phone.regex' => 'Phone number format is invalid.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
        ];
    }
}
