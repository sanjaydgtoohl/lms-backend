<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\AgencyTypeResource;

class AgencyResource extends JsonResource
{
    public function toArray($request) {
        $brandData = null;
        
        if ($this->relationLoaded('brand') && $this->brand && count($this->brand) > 0) {
            $brandData = BrandResource::collection($this->brand);
        }
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_parent' => $this->parentAgency ? new AgencyResource($this->parentAgency) : null,
            'agency_type' => new AgencyTypeResource($this->whenLoaded('agencyType')),
            'status' => $this->status,
            'contact_person_count' => $this->getContactPersonCount(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'brand' => $brandData,
        ];
    }
}
