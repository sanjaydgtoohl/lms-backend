<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource class for transforming Department model data.
 * 
 * This resource handles the transformation of Department model instances
 * into a standardized JSON response format, controlling which attributes
 * are exposed in the API.
 *
 * @package App\Http\Resources
 */
class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request The current request instance
     * @return array<string, mixed> An array of transformed department data
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'slug' => $this->slug, // Optionally include slug in response
            //'description' => $this->description,
            //'status' => $this->status,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            //'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}

