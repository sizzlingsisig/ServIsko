<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Profile picture is required.',
            'profile_picture.image' => 'File must be a valid image.',
            'profile_picture.mimes' => 'Image must be JPEG, PNG, JPG, GIF, or WebP format.',
            'profile_picture.max' => 'Image size must not exceed 5MB.',
        ];
    }
}
