<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // e.g., return auth()->user()->can('roles-update');
        return true;
    }

    public function rules(): array
    {
        // Get the role ID from the route
        $roleId = $this->route('role')->id;

        return [
            'name'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId) // Ignore self on update
            ],
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'required|in:1,2,15',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'integer|exists:permissions,id',
        ];
    }
}