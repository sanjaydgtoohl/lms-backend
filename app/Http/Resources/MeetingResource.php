<?php

namespace App\Http\Resources;

use App\Models\User;
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
        // Get attendees based on attendees_id JSON array
        $attendees = [];
        if ($this->attendees_id && is_array($this->attendees_id)) {
            $attendees = User::whereIn('id', $this->attendees_id)
                ->select('id', 'name')
                ->get()
                ->toArray();
        }

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
            
            // Relationships - Lead with only id and name
            'lead' => $this->when($this->lead, [
                'id' => $this->lead?->id,
                'name' => $this->lead?->name,
            ]),
            
            // Convenience fields
            'lead_id' => $this->lead_id,
            'attendees' => $attendees,
        ];
    }
}
