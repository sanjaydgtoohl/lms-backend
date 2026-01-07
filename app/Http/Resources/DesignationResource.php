<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DesignationResource extends JsonResource
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
            'name' => $this->title,
            //'slug' => $this->slug,
            //'description' => $this->description,
            //'status' => $this->status,
            //'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),   
            //'deleted_at' => $this->deleted_at->toIso8601String(),
        ];
    }
}
