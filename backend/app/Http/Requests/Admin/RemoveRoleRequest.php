<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RemoveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => 'required|string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role is required.',
            'role.exists' => 'The selected role does not exist.',
        ];
    }
}
