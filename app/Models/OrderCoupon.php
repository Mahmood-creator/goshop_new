<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderCoupon
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float|null $price
 * @property int $order_id
 * @method static \Database\Factories\OrderCouponFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCoupon whereUserId($value)
 * @mixin \Eloquent
 */
class OrderCoupon extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
}
