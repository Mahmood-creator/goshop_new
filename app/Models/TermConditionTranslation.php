<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TermConditionTranslation
 *
 * @property int $id
 * @property int $term_condition_id
 * @property string $title
 * @property string $description
 * @property string $locale
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation actualTranslation($lang)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereTermConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermConditionTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TermConditionTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['title','description','locale'];

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
