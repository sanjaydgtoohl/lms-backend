<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_person_id' => $this->contact_person_id,
            'agency_id' => $this->agency_id,
            'brand_type' => $this->brandType->name ?? null,
            'industry' =>  $this->industry->name ?? null,
            'country' => $this->country->name ?? null,
            'state' => $this->state->name ?? null,
            'city' => $this->city->name ?? null,
            'region' => $this->region->name ?? null,
            'subregion' => $this->subregions->name ?? null,
            'website' => $this->website,
            'postal_code' => $this->postal_code,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
        ];
    }
}


