<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UnitTranslation
 *
 * @property int $id
 * @property int $unit_id
 * @property string $locale
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation actualTranslation($lang)
 * @method static \Database\Factories\UnitTranslationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitTranslation whereUnitId($value)
 * @mixin \Eloquent
 */
class UnitTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'title'];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
