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

/**
 * App\Models\OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property int $shop_id
 * @property float $price
 * @property float $tax
 * @property float|null $commission_fee
 * @property string $status
 * @property int|null $delivery_address_id
 * @property int|null $delivery_type_id
 * @property float $delivery_fee
 * @property int|null $deliveryman
 * @property string|null $delivery_date
 * @property string|null $delivery_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\UserAddress|null $deliveryAddress
 * @property-read \App\Models\User|null $deliveryMan
 * @property-read \App\Models\Delivery|null $deliveryType
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\OrderProduct|null $orderStock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderProduct> $orderStocks
 * @property-read int|null $order_stocks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\Shop $shop
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\OrderDetailFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail netSales()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail netSalesSum()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail updatedDate($updatedDate)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereCommissionFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereDeliveryman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
