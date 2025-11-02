<?php

namespace App\Http\Requests\Seeker;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_picture' => [
                'required',
                'file',
                'mimes:jpeg,png,gif,webp',
                'max:5120', // 5MB in kilobytes
                'dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Profile picture is required.',
            'profile_picture.file' => 'Profile picture must be a valid file.',
            'profile_picture.mimes' => 'Profile picture must be a JPEG, PNG, GIF, or WebP image.',
            'profile_picture.max' => 'Profile picture cannot exceed 5MB.',
            'profile_picture.dimensions' => 'Profile picture must be between 100x100 and 5000x5000 pixels.',
        ];
    }
}
