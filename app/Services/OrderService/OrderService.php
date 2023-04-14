<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Services\CoreService;
use App\Services\FindexService\FindexService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\ProductService\StockService;
use App\Traits\Notification;

class OrderService extends CoreService implements OrderServiceInterface
{
    protected function getModelClass()
    {
        return Order::class;
    }

    public function create($collection)
    {
        try {
            $collection->rate = Currency::where('id', $collection->currency_id)->first()->rate;
            
            $order = $this->model()->create($this->setOrderParams($collection));

            if ($order) {

                $this->checkCoupon($collection['coupon'] ?? null, $order);

                $this->setUsdPrice($collection,$order);

                (new OrderDetailService)->create($order, $collection->shops);

                (new StockService)->decrementStocksQuantity($collection->shops);
                return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => Order::query()->find(data_get($order, 'id'))];
            }

            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    public function update(int $id, $collection)
    {
        try {
            $order = $this->model()->find($id);
            if ($order) {
                $order->update($this->setOrderParams($collection));
                (new StockService)->incrementStocksQuantity($order->load('orderDetails')->orderDetails);
                (new OrderDetailService)->create($order, $collection->shops);
                (new StockService)->decrementStocksQuantity($collection->shops);

                return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    private function setOrderParams($collection)
    {
        return [
            'user_id' => $collection->user_id,
            'price' => round($collection->total / $collection->rate, 2),
            'currency_id' => $collection->currency_id ?? Currency::whereDefault(1)->pluck('id')->first(),
            'rate' => $collection->rate,
            'note' => $collection->note ?? null,
            'status' => $collection->status ?? Order::NEW,
            'total_delivery_fee' => round($collection->total_delivery_fee / $collection->rate, 2) ?? null,
            'tax' => $collection->tax ?? null,
            'user_address_id' => $collection->user_address_id,
            'delivery_id' => $collection->delivery_id,
            'country_id' => $collection->country_id,
        ];
    }

    private function checkCoupon($coupon, $order){

        if (isset($coupon)) {
            $result = Coupon::checkCoupon($coupon)->first();
            if ($result) {
                switch ($result->type) {
                    case 'percent':
                        $couponPrice = ($order->price / 100) * $result->price;
                        break;
                    default:
                        $couponPrice = $result->price;
                        break;
                }
                $order->update(['price' => $order->price - $couponPrice]);

                $order->coupon()->create([
                    'user_id' => $order->user_id,
                    'name' => $result->name,
                    'price' => $couponPrice,
                ]);
                $result->decrement('qty');
            }
        }
    }

    private function setUsdPrice($collection,$order){

        $currency = Currency::where('short_code','USD')->first();

        $currencyRate = $currency->rate;

        if ($currency->id == $collection->currency_id)
        {
            $currencyRate = 1;
        }
        $order->update(['usd_price' => round($order->price * $currencyRate, 2)]);
    }
}
