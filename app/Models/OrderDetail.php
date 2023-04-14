<?php

namespace App\Models;

use App\Traits\Notification;
use App\Traits\Payable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OrderDetail extends Model
{
    use HasFactory, Payable, Notification, Reviewable;
    protected $guarded = [];

    const DELIVERED = 'delivered';

    const STATUS = [
        'new',
        'paid',
        'accepted',
        'ready',
        'on_a_way',
        'delivered',
        'canceled',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderStocks(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_detail_id');
    }

    public function orderStock(): HasOne
    {
        return $this->hasOne(OrderProduct::class, 'order_detail_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliveryman');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_type_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class)->withTrashed();
    }



    public function getPriceAttribute($value)
    {
        $rate = Currency::where('id',$this->order->currency_id)->first()->rate;

        if (request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTaxAttribute($value)
    {
        $rate = Currency::where('id',$this->order->currency_id)->first()->rate;

        if (request()->is('api/v1/dashboard/user/*')){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    const NETSALESSUMQUERY = 'IFNULL(TRUNCATE( CAST( SUM(price - IFNULL(tax ,0)- IFNULL(commission_fee ,0)) as decimal(7,2)) ,2) ,0)';

    public function scopeNetSales($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales");
    }

    public function scopeNetSalesSum($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales_sum");
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            });
    }
}
