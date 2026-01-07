<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
			//'uuid' => $this->uuid,
			'name' => $this->name,
			'slug' => $this->slug ?? null,
			//'display_name' => $this->display_name,
			'description' => $this->description,
			'status' => $this->status ?? null,
			'permissions' => $this->when(
				$this->relationLoaded('permissions'),
				fn() => $this->permissions->pluck('id')->toArray()
			),
			'created_at' => optional($this->created_at)->toDateTimeString(),
			'updated_at' => optional($this->updated_at)->toDateTimeString(),
		];
	}
}

