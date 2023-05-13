<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ShopTranslation
 *
 * @property int $id
 * @property int $shop_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property string|null $address
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation actualTranslation($lang)
 * @method static \Database\Factories\ShopTranslationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ShopTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
