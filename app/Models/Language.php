<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Language
 *
 * @property int $id
 * @property string|null $title
 * @property string $locale
 * @property int $backward
 * @property int $default
 * @property int $active
 * @property string|null $img
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery> $galleries
 * @property-read int|null $galleries_count
 * @method static \Database\Factories\LanguageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereBackward($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereTitle($value)
 * @mixin \Eloquent
 */
class Language extends Model
{
    use HasFactory, Loadable;
    protected $guarded = [];
    public $timestamps = false;

    public static function languagesList(){
        return cache()->remember('languages-list', 84300, function (){
           return self::orderByDesc('id')->get();
        });
    }
}
