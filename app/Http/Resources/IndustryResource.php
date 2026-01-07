<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IndustryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),   
            //'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
