<?php

/**
 * Lead Type Resource
 * -----------------------------------------
 * Transforms LeadType model data into a structured JSON format for API responses.
 * This resource ensures that only relevant fields are exposed to the client,
 * providing a consistent and clean API response structure for lead type data.
 *
 * @package App\Http\Resources
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
        ];
    }
}
