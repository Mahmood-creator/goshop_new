<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Point
 *
 * @property int $id
 * @property int|null $shop_id
 * @property string $type
 * @property float $price
 * @property int $value
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Point newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Point newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Point query()
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Point whereValue($value)
 * @mixin \Eloquent
 */
class Point extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function getActualPoint(string $amount)
    {
        $point = self::where('active', 1)->where('value', '<=', (int) $amount)->orderByDesc('value')->first();

        if (isset($point) && $point->type == 'percent') {
            $price = ($amount / 100) * $point->price;
        } elseif(isset($point) && $point->type == 'fix') {
            $price = $point->price;
        } else {
            $price = 0;
        }
        
        return $price;
    }
}
