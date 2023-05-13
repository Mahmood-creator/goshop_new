<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PaymentTranslation
 *
 * @property int $id
 * @property int $payment_id
 * @property string $locale
 * @property string $title
 * @property string|null $client_title
 * @property string|null $secret_title
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation actualTranslation($lang)
 * @method static \Database\Factories\PaymentTranslationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation whereClientTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation whereSecretTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class PaymentTranslation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
