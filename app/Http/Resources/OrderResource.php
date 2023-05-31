<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Order|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'delivery_id' => (int) $this->delivery_id,
            'price' => (double) $this->price,
            'currency_price' => (double) $this->currency_price,
            'rate' => (double) $this->rate,
            'status' => $this->status,
            'name' => $this->name,
            'phone' => $this->phone,
            'total_delivery_fee' => round($this->total_delivery_fee,2),
            'track_code' => $this->track_code,
            'declaration_id' => $this->declaration_id,
            'country_id' => $this->country_id,
            'note' => $this->when(isset($this->note), (string) $this->note),
            'order_details_count' => $this->when($this->order_details_count, (int) $this->order_details_count),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'delivery' => DeliveryResource::make($this->whenLoaded('delivery')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'details' => OrderDetailResource::collection($this->whenLoaded('orderDetails')),
            'transaction' => TransactionResource::make($this->whenLoaded('transaction')),
            'review' => ReviewResource::make($this->whenLoaded('review')),
            'order_point' => $this->whenLoaded('point'),
            'user_address' => UserAddressResource::make($this->userAddress),
            'coupon' => CouponResource::make($this->whenLoaded('coupon')),
            'deliveryman' => UserResource::make($this->whenLoaded('deliveryMan')),

        ];
    }
}
