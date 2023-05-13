<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrderProduct
 *
 * @property int $id
 * @property int $order_detail_id
 * @property int $stock_id
 * @property float $origin_price
 * @property float $total_price
 * @property float $tax
 * @property float $discount
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderDetail $detail
 * @property-read \App\Models\Stock $stock
 * @property-read \App\Models\ProductTranslation|null $translation
 * @method static \Database\Factories\OrderProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereOrderDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereOriginPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderProduct extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class, 'product_id', 'product_id');
    }

    public function detail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function stock() {
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    public function getOriginPriceAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTaxAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getDiscountAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTotalPriceAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }
}
