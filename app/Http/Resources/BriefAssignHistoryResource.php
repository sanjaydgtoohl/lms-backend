<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BriefAssignHistoryResource extends JsonResource
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
            'status' => $this->status,

            // Brief Information
            'brief' => $this->when($this->brief, new BriefResource($this->brief)),
            'brief_id' => $this->brief_id,

            // Assignment Information
            'assigned_by' => new UserResource($this->whenLoaded('assignedBy')),
            'assign_by_id' => $this->assign_by_id,

            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'assign_to_id' => $this->assign_to_id,

            // Status Information
            'brief_status' => new BriefStatusResource($this->whenLoaded('briefStatus')),
            'brief_status_id' => $this->brief_status_id,
            'brief_status_time' => $this->brief_status_time ? $this->brief_status_time->format('d-m-Y H:i:s') : null,

            // Dates
            'submission_date' => $this->submission_date ? $this->submission_date->format('d-m-Y H:i:s') : null,
            'comment' => $this->comment,

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s A'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s A'),   
            //'deleted_at' => $this->deleted_at->toIso8601String(),
        ];
    }
}
