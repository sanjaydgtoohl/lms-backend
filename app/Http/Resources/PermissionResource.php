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
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'url' => $this->url ?? null,
            'icon_file' => $this->icon_file,
            'icon_text' => $this->icon_text ?? null,
            'description' => $this->description,
            'children' => $this->when(
                $this->relationLoaded('children') && $this->children->isNotEmpty(),
                fn() => PermissionResource::collection($this->children)
            ),
        ];
    }
}

