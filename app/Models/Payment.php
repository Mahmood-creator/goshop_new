<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string|null $tag
 * @property int $input
 * @property string|null $client_id
 * @property string|null $secret_id
 * @property int $sandbox
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PaymentTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Database\Factories\PaymentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereInput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSandbox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];
    const ATB = 'atb';
    const CASH = 'cash';
    const WALLET = 'wallet';
    const PAYPAL = 'paypal';
    const STRIPE = 'stripe';
    const PAYSTACK = 'paystack';
    const RAZORPAY = 'razorpay';
    public function translations() {
        return $this->hasMany(PaymentTranslation::class);
    }

    public function translation() {
        return $this->hasOne(PaymentTranslation::class);
    }
}
