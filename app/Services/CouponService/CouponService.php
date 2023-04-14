<?php

namespace App\Services\CouponService;

use App\Helpers\ResponseError;
use App\Models\Coupon;

class CouponService extends \App\Services\CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Coupon::class;
    }

    public function create($collection)
    {
        try {
            $coupon = $this->model()->create($this->setCouponParams($collection));
            $this->setTranslations($coupon, $collection);

            if ($coupon && isset($collection->images)) {
                $coupon->update(['img' => $collection->images[0]]);
                $coupon->uploads($collection->images);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param int $id
     * @param $collection
     * @return array
     */
    public function update(int $id, $collection): array
    {
        $coupon = $this->model()->find($id);
        if ($coupon) {
            try {
                $coupon->update($this->setCouponParams($collection));
                $this->setTranslations($coupon, $collection);

                if (isset($collection->images)) {
                    $coupon->galleries()->delete();
                    $coupon->update(['img' => $collection->images[0]]);
                    $coupon->uploads($collection->images);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];
            }
            catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * Set Category params for Create & Update function
     * @param $collection
     * @return array
     */
    private function setCouponParams($collection): array
    {
        return [
            'shop_id' => $collection->shop_id ?? null,
            'name' => $collection->name ?? 0,
            'type' => $collection->type ?? 'fix',
            'qty' => $collection->qty ?? 0,
            'price' => $collection->price ?? 0,
            'expired_at' => $collection->expired_at ?? now(),
        ];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection->title as $index => $value){
            $model->translation()->create([
                'title' => $value,
                'description' => $collection->description[$index] ?? null,
                'locale' => $index,
            ]);
        }
    }
}
