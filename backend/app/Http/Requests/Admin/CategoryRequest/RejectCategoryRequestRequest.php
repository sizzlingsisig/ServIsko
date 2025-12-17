<?php

namespace App\Http\Requests\Admin\CategoryRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectCategoryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_notes' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_notes.required' => 'Rejection reason is required.',
            'admin_notes.max' => 'Rejection reason cannot exceed 500 characters.',
        ];
    }
}
