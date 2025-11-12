<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MissCampaignResource extends JsonResource
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
            //'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'image_path' => $this->image_path,
            'image_url' => $this->when($this->image_path, function() {
                return $this->getMediaUrl($this->image_path);
            }),

            // Relationships (IDs)
            'brand_id' => $this->brand_id,
            'lead_source_id' => $this->lead_source_id,
            'lead_sub_source_id' => $this->lead_sub_source_id,

            // Relationships (Objects)
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),
            'lead_source' => $this->whenLoaded('leadSource', function () {
                return [
                    'id' => $this->leadSource->id,
                    'name' => $this->leadSource->name,
                ];
            }),
            'lead_sub_source' => $this->whenLoaded('leadSubSource', function () {
                return $this->leadSubSource ? [
                    'id' => $this->leadSubSource->id,
                    'name' => $this->leadSubSource->name,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            //'deleted_at' => $this->deleted_at,
        ];
    }
}