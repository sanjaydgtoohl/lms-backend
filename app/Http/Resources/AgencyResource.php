<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'agency_group' => new AgencyGroupResource($this->whenLoaded('agencyGroup')),
            'agency_type' => new AgencyTypeResource($this->whenLoaded('agencyType')),
            'brands' => AgencyBrandResource::collection($this->whenLoaded('brands')),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
