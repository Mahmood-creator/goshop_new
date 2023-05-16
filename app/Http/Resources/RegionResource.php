<?php

namespace App\Http\Resources;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Region|JsonResource $this */

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,

            'country' => CountryResource::make($this->whenLoaded('country')),
        ];
    }
}
