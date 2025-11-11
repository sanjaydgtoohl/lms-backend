<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Define the JSON structure for the response
        return [
            'id' => $this->id,
            'name' => $this->name,
            //'slug' => $this->slug,
            
            // Convert status to a readable format
            'status' => $this->status, // Raw value (1, 2, 15)
            //'status_label' => $this->getStatusLabel(), // Readable text
            
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            //'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    /**
     * Helper function to get readable status
     */
    protected function getStatusLabel()
    {
        switch ($this->status) {
            case '1':
                return 'Active';
            case '2':
                return 'Deactivated';
            case '15':
                return 'User Soft Delete';
            default:
                return 'Unknown';
        }
    }
}