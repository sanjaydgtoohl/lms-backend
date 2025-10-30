<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadSubSourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'slug' => $this->slug,
            //'description' => $this->description,
            'status' => $this->status,
            'lead_source' => optional($this->leadSource)->name,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            
        ];
    }
}