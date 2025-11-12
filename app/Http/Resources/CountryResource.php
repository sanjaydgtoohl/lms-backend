<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
// Import StateResource for relationship handling
use App\Http\Resources\StateResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Define the structure for JSON response
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            // States will only be loaded when eager loaded using with('states') in the controller
            //'states' => StateResource::collection($this->whenLoaded('states')),
        ];
    }
}
