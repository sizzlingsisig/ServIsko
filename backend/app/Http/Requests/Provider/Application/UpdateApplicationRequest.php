<?php

namespace App\Http\Requests\Provider\Application;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'message.max' => 'Message cannot exceed 1000 characters.',
        ];
    }
}
