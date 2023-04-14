<?php

namespace App\Http\Resources;

use App\Models\UserAddress;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'country_id' => $this->country_id,
            'province' => $this->province,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'apartment' => $this->apartment,
            'company_name' => $this->company_name,
            'postcode' => $this->postcode,
            'number' => $this->number,
            'city' => $this->city,
            'note' => $this->note,
            'address' => (string) $this->title,
            'location' => [
                'latitude' => $this->location['latitude'],
                'longitude' => $this->location['longitude'],
            ],
            'default' => (boolean) $this->default,
            'active' => (boolean) $this->active,
            'country' => $this->country,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),
        ];
    }
}
