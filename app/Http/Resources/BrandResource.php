<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            // Basic Information
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,

            // Relationships (IDs)
            'brand_type_id' => $this->brand_type_id,
            'industry_id' => $this->industry_id,
            'agency_id' => $this->agency_id,
            'zone_id' => $this->zone_id,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,

            // Location Information
            'website' => $this->website,
            'postal_code' => $this->postal_code,

            // Relationships (Objects)
            'brand_type' => $this->whenLoaded('brandType', function () {
                return $this->brandType->name ?? null;
            }),
            'industry' => $this->whenLoaded('industry', function () {
                return $this->industry->name ?? null;
            }),
            'agency' => $this->whenLoaded('agency', function () {
                return [
                    'id' => $this->agency->id,
                    'name' => $this->agency->name,
                ];
            }),
            'zone' => $this->whenLoaded('zone', function () {
                return $this->zone->name ?? null;
            }),
            'country' => $this->whenLoaded('country', function () {
                return $this->country->name ?? null;
            }),
            'state' => $this->whenLoaded('state', function () {
                return $this->state->name ?? null;
            }),
            'city' => $this->whenLoaded('city', function () {
                return $this->city->name ?? null;
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name ?? null,
                ];
            }),
            'contact_person_count' => $this->getContactPersonCount(),
            'created_at' => $this->created_at,
        ];
    }
}