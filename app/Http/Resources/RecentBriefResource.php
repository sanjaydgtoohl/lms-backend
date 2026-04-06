<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recent Brief Resource
 *
 * Formats brief data for recent briefs endpoint.
 * Includes basic brief info, brand, contact person, assigned user, and brief status.
 */
class RecentBriefResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_name' => $this->product_name,
            'budget' => $this->budget,
            'brand_name' => $this->brand ? $this->brand->name : null,
            'contact_person_name' => $this->contactPerson ? $this->contactPerson->name : null,
            'assign_to' => [
                'id' => $this->assignedUser ? $this->assignedUser->id : null,
                'name' => $this->assignedUser ? $this->assignedUser->name : null,
                'email' => $this->assignedUser ? $this->assignedUser->email : null,
            ],
            'brief_status' => [
                'name' => $this->briefStatus ? $this->briefStatus->name : null,
                'percentage' => $this->briefStatus ? $this->briefStatus->percentage : null,
            ],
        ];
    }
}