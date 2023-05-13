<?php

namespace App\Http\Resources;

use App\Models\Region;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var Region|JsonResource $this */

        return [
            'id' => $this->id,
            'name' => $this->name,

            'country' => CountryResource::make($this->whenLoaded('country')),
        ];
    }
}
