<?php

namespace App\Http\Resources;

use App\Models\ShopLocation;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShopLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @mixin ShopLocation
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        /** @var ShopLocation|JsonResource $this */

        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'country_id' => $this->country_id,
            'region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'delivery_fee' => $this->delivery_fee,
            'pickup' => $this->pickup,
            'delivery' => $this->delivery,
            'deleted_at' => $this->deleted_at,

            // Relation
            'country' => CountryResource::make($this->whenLoaded('country')),
            'region' => RegionResource::make($this->whenLoaded('region')),
            'city' => CityResource::make($this->whenLoaded('city')),
        ];
    }
}
