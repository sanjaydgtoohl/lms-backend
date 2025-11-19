<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            // Basic Information
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug ?? null,
            'display_name' => $this->display_name,
            'description' => $this->description,
            
            // Navigation & UI
            'url' => $this->url ?? null,
            'icon_file' => $this->icon_file,
            'icon_url' => $this->when($this->icon_file, function() {
                // Use HandlesFileUploads trait helper on the underlying model
                return $this->getFileUrl($this->icon_file, 'public');
            }),
            'icon_text' => $this->icon_text ?? null,
            
            // Status & Hierarchy
            'is_parent' => $this->is_parent,
            'status' => $this->status ?? null,
            
            // Relationships
            'roles' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->roles->pluck('id')->toArray()
            ),
            'users' => $this->when(
                $this->relationLoaded('users'),
                fn() => $this->users->pluck('id')->toArray()
            ),
            
            // Timestamps
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
            'deleted_at' => optional($this->deleted_at)->toDateTimeString(),
        ];
    }
}
