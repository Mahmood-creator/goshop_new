<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Country|JsonResource $this */
        return [
            'id' => $this->id,
            'name' => $this->name,

            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
