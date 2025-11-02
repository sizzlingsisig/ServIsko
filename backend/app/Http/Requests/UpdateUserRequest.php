<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => 'sometimes|string|max:255',
            'username' => "sometimes|string|max:255|unique:users,username,{$userId}",
            'email' => "sometimes|email|unique:users,email,{$userId}",
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'profile_pic' => 'nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name must not exceed 255 characters.',
            'username.string' => 'Username must be a string.',
            'username.max' => 'Username must not exceed 255 characters.',
            'username.unique' => 'Username is already taken.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'Email is already taken.',
            'bio.string' => 'Bio must be a string.',
            'bio.max' => 'Bio must not exceed 500 characters.',
            'location.string' => 'Location must be a string.',
            'location.max' => 'Location must not exceed 255 characters.',
            'profile_pic.url' => 'Profile picture must be a valid URL.',
        ];
    }
}
