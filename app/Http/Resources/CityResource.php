<?php

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var City|JsonResource $this */

        return [
            'id' => $this->id,
            'name' => $this->name,

            'region' => RegionResource::make($this->whenLoaded('region')),
        ];
    }
}
