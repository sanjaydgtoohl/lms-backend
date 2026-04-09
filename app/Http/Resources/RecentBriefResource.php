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
            'assigned_user' => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ] : null,
            'brief_status' => $this->briefStatus ? [
                'name' => $this->briefStatus->name,
                'percentage' => $this->briefStatus->percentage,
            ] : null,
        ];
    }
}