<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
// StateResource ko import karein taaki relationship mein use kar sakein
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
        // Yeh structure aapke JSON response mein jayega
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            // 'states' ko tabhi load karega jab controller se 'with('states')' bheja gaya ho
            //'states' => StateResource::collection($this->whenLoaded('states')),
        ];
    }
}
