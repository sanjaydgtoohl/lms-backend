<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class AgencyGroupResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
        ];
    }
}
