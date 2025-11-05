<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Change this to your real authorization logic
        // e.g., return auth()->user()->can('roles-create');
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'required|in:1,2,15',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'integer|exists:permissions,id', // Validates each item in the array
        ];
    }
}