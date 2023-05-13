<?php

namespace App\Repositories\ShopPaymentRepository;

use App\Models\ShopPayment;
use App\Repositories\CoreRepository;

class ShopPaymentRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return ShopPayment::class;
    }

    public function paginate($perPage, $shop = null)
    {
        return $this->model()->with(['payment.translation'])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }


    public function getById(int $id, $shop = null)
    {
        return $this->model()->with(['payment.translation','payment.translations'])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }
}
