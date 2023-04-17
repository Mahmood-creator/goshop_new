<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Repositories\ProductTypeRepository\ProductTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'uuid' => (string) $this->uuid,
            'keywords' => $this->when($this->keywords, (string) $this->keywords),
            'parent_id' => (int) $this->parent_id,
            'type' => $this->when($this->type, (string) $this->type),
            'img' => $this->when(isset($this->img), (string) $this->img),
            'active' => $this->when($this->active, (bool) $this->active),
            'product_type_id' => $this->when($this->product_type_id,$this->productType($this->product_type_id)),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' =>  $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'products_count' =>  $this->when($this->products_count, (int) $this->products_count),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),

        ];
    }

    protected function productType($product_type_id)
    {
        return (new ProductTypeRepository())->productsTypeList()->where('id',$product_type_id)->first();
    }
}
