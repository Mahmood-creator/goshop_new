<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ShopPayment
 *
 * @property int $id
 * @property int $shop_id
 * @property int $payment_id
 * @property int $status
 * @property string|null $client_id
 * @property string|null $secret_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Payment $payment
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopPayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShopPayment extends Model
{
    protected $fillable = ['payment_id','shop_id','status','client_id','secret_id'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
