<?php

namespace App\Services\ProductService;

use App\Helpers\ResponseError;
use App\Models\Product;
use App\Services\CoreService;

class ProductReviewService extends CoreService
{

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function addReview($uuid, $collection): array
    {
        $product = $this->model()->firstWhere('uuid', $uuid);
        if ($product){
            $product->addReview($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
