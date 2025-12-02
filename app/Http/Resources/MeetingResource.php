<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'location' => $this->location,
            'agenda' => $this->agenda,
            'link' => $this->link,
            'meeting_date' => $this->meeting_date,
            'meeting_time' => $this->meeting_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            // Relationships
            'lead' => new LeadResource($this->whenLoaded('lead')),
            'attendee' => new UserResource($this->whenLoaded('attendee')),
            
            // Convenience fields
            'lead_id' => $this->lead_id,
            'attendees_id' => $this->attendees_id,
        ];
    }
}
