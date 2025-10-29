<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandTypeResource extends JsonResource
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
            'name' => $this->name,
            //'slug' => $this->slug,
            //'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            //'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
