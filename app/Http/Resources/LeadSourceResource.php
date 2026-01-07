<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadSourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            //'created_at' => $this->created_at->toIso8601String(),
            //'updated_at' => $this->updated_at->toIso8601String(),   
            //'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
