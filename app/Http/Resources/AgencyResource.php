<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_parent' => $this->getParentAgencyData(),
            'agency_type' => $this->getAgencyTypeData(),
            'brand' => $this->getBrandData(),
            'contact_person_count' => $this->getContactPersonCount(),
            'status' => $this->status,
            'childs' => $this->childs,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s A'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s A'),
        ];
    }

    /**
     * Get parent agency data with minimal fields to prevent circular references.
     */
    private function getParentAgencyData()
    {
        if (!$this->parentAgency) {
            return null;
        }

        // Ensure relationships are loaded
        if (!$this->parentAgency->relationLoaded('brand')) {
            $this->parentAgency->load('brand');
        }
        if (!$this->parentAgency->relationLoaded('agencyType')) {
            $this->parentAgency->load('agencyType');
        }

        return [
            'id' => $this->parentAgency->id,
            'name' => $this->parentAgency->name,
            'is_parent' => null,
            'agency_type' => $this->formatAgencyType($this->parentAgency->agencyType),
            'brand' => $this->formatBrandCollection($this->parentAgency->brand),
        ];
    }

    /**
     * Get agency type data if loaded.
     */
    private function getAgencyTypeData()
    {
        return $this->whenLoaded('agencyType', fn () => $this->formatAgencyType($this->agencyType));
    }

    /**
     * Get brand data for this agency.
     */
    private function getBrandData()
    {
        if (!$this->relationLoaded('brand') || !$this->brand || count($this->brand) === 0) {
            return null;
        }

        return $this->formatBrandCollection($this->brand);
    }

    /**
     * Format a single agency type.
     */
    private function formatAgencyType($agencyType)
    {
        if (!$agencyType) {
            return null;
        }

        return [
            'id' => $agencyType->id,
            'name' => $agencyType->name,
        ];
    }

    /**
     * Format brand collection with minimal fields.
     */
    private function formatBrandCollection($brands)
    {
        if (!$brands || count($brands) === 0) {
            return null;
        }

        return $brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
            ];
        });
    }
}