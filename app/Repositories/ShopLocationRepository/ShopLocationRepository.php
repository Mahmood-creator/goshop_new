<?php

namespace App\Repositories\ShopLocationRepository;

use App\Models\ShopLocation;
use App\Repositories\CoreRepository;

class ShopLocationRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return ShopLocation::class;
    }

    public function paginate($collection)
    {
        return $this->model()->with(['country', 'region', 'city'])
            ->when(isset($collection['shop_id']), function ($q) use ($collection) {
                $q->where('shop_id', $collection['shop_id']);
            })
            ->orderByDesc('id')
            ->paginate($collection['perPage']);
    }


    public function show(int $id, int $shop = null)
    {
        return $this->model()->with(['country:id,name', 'region:name', 'city:name'])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->select('id','shop_id','delivery_fee','pickup','country_id','region_id','city_id')
            ->find($id);
    }
}
