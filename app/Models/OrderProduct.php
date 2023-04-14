<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
