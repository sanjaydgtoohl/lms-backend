<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'description' => $role->description,
                    ];
                });
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'description' => $permission->description,
                    ];
                });
            }),
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toIso8601String() : null,
            'last_login_at' => $this->last_login_at ? $this->last_login_at->toIso8601String() : null,
            'full_name' => $this->full_name,
            'is_active' => $this->isActive(),
            'is_admin' => $this->isAdmin(),
            'profile' => $this->whenLoaded('profile', function () {
                return new ProfileResource($this->profile);
            }),
        ]);
    }
}
