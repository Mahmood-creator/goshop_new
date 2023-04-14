<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\Point;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;

class OrderStatusUpdateService extends CoreService
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return OrderDetail::class;
    }

    public function statusUpdate($detail, $status)
    {
        if ($detail->status == $status) {
            return ['status' => false, 'code' => ResponseError::ERROR_252];
        } elseif ($detail->status == Order::CANCELED) {
            return ['status' => false, 'code' => ResponseError::ERROR_254];
        }

        try {
            $detail->update(['status' => $status]);

            // Top up Seller && Deliveryman Wallets when Order Status was delivered
            if ($status == 'delivered') {
                // SELLER TOP UP
                $this->sellerWalletTopUp($detail);

                // USER POINT TOP UP
                $this->userCashbackTopUp($detail->order, $detail->order->user);

                // DELIVERYMAN TOP UP
                if (isset($detail->deliveryman)){
                    $this->deliverymanWalletTopUp($detail);
                }
            }
            if ($status == Order::CANCELED) {

                $user = $detail->order->user;

                if ($user->wallet && data_get($detail->transaction()->where('status', 'paid')->first(),'id'))
                {
                    $user->wallet()->update(['price' => $user->wallet->price + ($detail->price + $detail->delivery_fee + $detail->tax)]);
                }

                $detail->orderStocks->map(function (OrderProduct $orderProduct){
                   $orderProduct->stock()->increment('quantity',$orderProduct->quantity);
                });

            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $detail];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    // Seller Order price topup function
    private function sellerWalletTopUp($detail)
    {
        $seller = $detail->shop->seller;
        $seller->wallet()->update(['price' => $seller->wallet->price + ($detail->price - $detail->commission_fee)]);

        $request = request()->merge([
            'type' => 'topup',
            'price' => $detail->price - $detail->commission_fee,
            'note' => 'For Order #' . $detail->order->id,
            'status' => 'paid',
        ]);
        return (new WalletHistoryService())->create($seller, $request);
    }

    // Deliveryman  Order price topup function
    private function deliverymanWalletTopUp($detail)
    {
        $deliveryman = $detail->deliveryMan;
        $deliveryman->wallet()->update(['price' => $deliveryman->wallet->price + $detail->delivery_fee]);

        $request = request()->merge([
            'type' => 'topup',
            'price' => $detail->delivery_fee,
            'note' => 'For Order #' . $detail->order->id,
            'status' => 'paid',
        ]);
       return (new WalletHistoryService())->create($deliveryman, $request);
    }

    // User Point topup function
    private function userCashbackTopUp($order, $user)
    {
        if ($order->orderDetails()->count() == $order->orderDetails()->where('status', 'delivered')->count()) {
            $price = Point::getActualPoint($order->price);
            if ($price > 0) {
                $user->wallet()->update(['price' => $user->wallet->price + $price]);

                $request = request()->merge([
                    'type' => 'topup',
                    'price' => $price,
                    'note' => 'Cashback for Order #' . $order->id,
                    'status' => 'paid',
                ]);

                return (new WalletHistoryService())->create($user, $request);
            }
        }


    }
}
