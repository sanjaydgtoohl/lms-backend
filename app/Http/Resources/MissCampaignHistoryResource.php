<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MissCampaignHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'miss_campaign_id' => $this->miss_campaign_id,
            'assign_by' => $this->whenLoaded('assignBy', function () {
                return [
                    'id' => $this->assignBy->id,
                    'name' => $this->assignBy->name,
                ];
            }),
            'assign_to' => $this->whenLoaded('assignTo', function () {
                return [
                    'id' => $this->assignTo->id,
                    'name' => $this->assignTo->name,
                ];
            }),
            'comment' => $this->comment,
            'created_at' => $this->created_at->format('Y-m-d H:i:s A'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s A'),
        ];
    }
}
