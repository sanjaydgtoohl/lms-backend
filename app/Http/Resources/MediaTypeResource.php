<?php

/**
 * MediaType Resource
 * -----------------------------------------
 * Transforms MediaType model data into JSON API responses with standardized formatting.
 *
 * @package App\Http\Resources
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaTypeResource extends JsonResource
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
            'status' => $this->status === '1',
            //'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            //'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
