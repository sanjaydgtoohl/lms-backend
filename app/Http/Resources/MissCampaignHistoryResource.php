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
                return $this->assignBy ? [
                    'id' => $this->assignBy->id,
                    'name' => $this->assignBy->name,
                ] : null;
            }),
            'assign_to' => $this->whenLoaded('assignTo', function () {
                return $this->assignTo ? [
                    'id' => $this->assignTo->id,
                    'name' => $this->assignTo->name,
                ] : null;
            }),
            'comment' => $this->comment,
            'created_at' => $this->created_at->format('Y-m-d H:i:s A'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s A'),
        ];
    }
}
