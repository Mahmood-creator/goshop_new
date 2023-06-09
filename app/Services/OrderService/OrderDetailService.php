<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use App\Services\CoreService;
use Throwable;

class OrderDetailService extends CoreService
{
    private float $rate;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return OrderDetail::class;
    }

    public function create($order, $collection): bool
    {
        $this->rate = $order->rate;
        $order->orderDetails()->delete();
        foreach ($collection as $item) {

            /** @var Shop $shop */
            $shop = Shop::with('subscription')->find($item['shop_id']);

            if (!$shop) {
                continue;
            }

            $commissionFee = $shop->subscription ? 0 :
                (collect($item['products'])->sum('total_price') / 100) * $shop->percentage;

            $detail = $order->orderDetails()->create($this->setDetailParams($item + ['commission_fee' => $commissionFee]));

            if ($detail) {

                if (isset($collection['shop_location_id'])) {
                    $shopLocation = ShopLocation::find($collection['shop_location_id']);

                    if ($shopLocation) {
                        $detail->update(['delivery_fee' => $shopLocation->delivery_fee]);
                    }

                }

                $detail->orderStocks()->delete();

                foreach ($item['products'] as $product) {
                    $detail->orderStocks()->create($this->setProductParams($product));
                }
            }
        }
        return true;
    }

    public function updateStatus(int $id, $status): array
    {
        if (!isset($status)) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }
        $detail = $this->model()->find($id);
        if ($detail) {
            // Order Status change logic
            return (new OrderStatusUpdateService())->statusUpdate($detail, $status);
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param int|null $id
     * @return array
     */
    public function attachDeliveryMan(?int $id): array
    {
        /** @var Order $order */
        /** @var User $user */
        try {
            $user = auth('sanctum')->user();
            $orderDetail = OrderDetail::with('user')->find($id);

            if (empty($orderDetail) || ($orderDetail?->deliveryType?->type == Delivery::TYPE_PICKUP)) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_404,
                    'message' => 'Invalid deliveryman or token not found'
                ];
            }

            if (!empty($orderDetail->deliveryman)) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_210,
                    'message' => 'Delivery already attached'
                ];
            }

            if (!$user?->invitations?->where('shop_id', $orderDetail->shop_id)?->first()?->id) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_212,
                    'message' => 'Not your shop. Check your other account'
                ];
            }

            $orderCount = OrderDetail::where('deliveryman', $user->id)->whereIn('status', '!=', [
                OrderDetail::DELIVERED,
                OrderDetail::COMPLETED,
                OrderDetail::CANCELED,
            ])->count();

            if ($user?->deliveryManSetting?->order_quantity > $orderCount) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_213,
                    'message' => 'Your order amount is full'
                ];
            }

            $orderDetail->update([
                'deliveryman' => auth('sanctum')->id(),
            ]);

            return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $orderDetail];
        } catch (Throwable) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }


    private function setDetailParams($detail): array
    {
        return [
            'shop_id' => $detail['shop_id'],
            'price' => round(collect($detail['products'])->sum('total_price') / $this->rate, 2),
            'tax' => round($detail['tax'] / $this->rate, 2),
            'commission_fee' => round($detail['commission_fee'] / $this->rate, 2),
            'status' => $detail['status'] ?? OrderDetail::NEW,
            'delivery_type' => $detail['delivery_type'] ?? null,
            'delivery_fee' => $detail['delivery_fee'] / $this->rate,
            'delivery_address_id' => $detail['delivery_address_id'] ?? null,
            'deliveryman' => $detail['deliveryman'] ?? null,
            'delivery_date' => $detail['delivery_date'] ?? null,
            'delivery_time' => $detail['delivery_time'] ?? null,
            'shop_location_id' => $detail['shop_location_id'] ?? null,
            'point_delivery_id' => $detail['point_delivery_id'] ?? null,
        ];
    }

    private function setProductParams($product)
    {
        return [
            'stock_id' => $product['id'],
            'origin_price' => round($product['price'] / $this->rate, 2),
            'total_price' => round($product['total_price'] / $this->rate, 2),
            'tax' => round($product['tax'] / $this->rate, 2),
            'discount' => round($product['discount'] / $this->rate, 2),
            'quantity' => $product['qty'],
        ];
    }


}
