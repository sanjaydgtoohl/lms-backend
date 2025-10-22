<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IndustryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
        ]);
    }
}
