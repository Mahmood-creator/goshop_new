<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductProperties
 *
 * @property int $id
 * @property int $product_id
 * @property string $locale
 * @property string $key
 * @property string|null $value
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties actualTranslation($lang)
 * @method static \Database\Factories\ProductPropertiesFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProperties whereValue($value)
 * @mixin \Eloquent
 */
class ProductProperties extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'key', 'value'];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = self::where('locale', $lang)->pluck('locale')->first() ?? self::pluck('locale')->first();
        return $query->where('locale', $lang);
    }
}
