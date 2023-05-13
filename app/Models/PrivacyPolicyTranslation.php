<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PrivacyPolicyTranslation
 *
 * @property int $id
 * @property int $privacy_policy_id
 * @property string $title
 * @property string $description
 * @property string $locale
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation actualTranslation($lang)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation wherePrivacyPolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicyTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PrivacyPolicyTranslation extends Model
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
