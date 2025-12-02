<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BriefResource extends JsonResource
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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'product_name' => $this->product_name,
            'mode_of_campaign' => $this->mode_of_campaign,
            'media_type' => $this->media_type,
            'budget' => $this->budget,
            'comment' => $this->comment,
            'submission_date' => $this->submission_date?->toIso8601String(),
            'status' => $this->status,

            // Foreign Key IDs
            'contact_person_id' => $this->contact_person_id,
            'brand_id' => $this->brand_id,
            'agency_id' => $this->agency_id,
            'assign_user_id' => $this->assign_user_id,
            'created_by' => $this->created_by,
            'brief_status_id' => $this->brief_status_id,
            'priority_id' => $this->priority_id,

            // Relationships (Objects)
            'contact_person' => $this->whenLoaded('contactPerson', function () {
                return [
                    'id' => $this->contactPerson?->id,
                    'name' => $this->contactPerson?->name,
                    'email' => $this->contactPerson?->email,
                ];
            }),
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand?->id,
                    'name' => $this->brand?->name,
                ];
            }),
            'agency' => $this->whenLoaded('agency', function () {
                return [
                    'id' => $this->agency?->id,
                    'name' => $this->agency?->name,
                ];
            }),
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser?->id,
                    'name' => $this->assignedUser?->name,
                    'email' => $this->assignedUser?->email,
                ];
            }),
            'created_by_user' => $this->whenLoaded('createdByUser', function () {
                return [
                    'id' => $this->createdByUser?->id,
                    'name' => $this->createdByUser?->name,
                    'email' => $this->createdByUser?->email,
                ];
            }),
            'brief_status' => $this->whenLoaded('briefStatus', function () {
                return [
                    'id' => $this->briefStatus?->id,
                    'name' => $this->briefStatus?->name,
                ];
            }),
            'priority' => $this->whenLoaded('priority', function () {
                return [
                    'id' => $this->priority?->id,
                    'name' => $this->priority?->name,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
