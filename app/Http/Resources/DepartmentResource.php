<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class DepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'slug' => $this->slug, // Optionally include slug in response
            //'description' => $this->description,
            //'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s A'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s A'),   
        ];
    }
}

