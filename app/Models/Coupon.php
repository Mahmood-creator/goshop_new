<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, Loadable;
    protected $guarded = [];

    public function translations() {
        return $this->hasMany(CouponTranslation::class);
    }

    public function translation() {
        return $this->hasOne(CouponTranslation::class);
    }

    public function orderCoupon() {
        return $this->hasMany(OrderCoupon::class, 'name', 'name');
    }

    public function shop() {
        return $this->belongsTo(Shop::class);
    }


    public static function scopeCheckCoupon($query, $coupon){
        return $query->where('name', $coupon)
            ->where('qty', '>', 0)
            ->whereDate('expired_at', '>', now());
    }

    public function scopeFilter($query, $array)
    {
        $query->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', $array['type']);
            });
    }
}
