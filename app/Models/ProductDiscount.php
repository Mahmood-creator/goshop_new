<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductDiscount
 *
 * @property int $id
 * @property int $product_id
 * @property int $discount_id
 * @method static \Database\Factories\ProductDiscountFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductDiscount whereProductId($value)
 * @mixin \Eloquent
 */
class ProductDiscount extends Model
{
    use HasFactory;
}
