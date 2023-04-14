<?php

namespace App\Repositories\ProductTypeRepository;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProductTypeRepository
{
    const FINDEX_URL = 'https://api2.findex.az/api/partners';
    const SECRET_KEY = 'pasMallSi';
    const TTL = 604800; // 7 days


    public function productsTypeList($search = null)
    {
        $productType = Cache::remember('product_types', self::TTL, function () {
            return collect(Http::withHeaders(['application/json'])
                ->get(self::FINDEX_URL . '/product_types?secret='.self::SECRET_KEY)
                ->json('data'));
        });

        if ($search)
            return $productType->filter(function ($item) use ($search){
               return preg_match('/'.strtolower($search).'/',strtolower($item['name']));
            });

        return $productType;
    }
}

