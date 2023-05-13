<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property float $price
 * @property int|null $user_id
 * @property int|null $payment_sys_id
 * @property string|null $payment_trx_id
 * @property string|null $note
 * @property string|null $perform_time
 * @property string|null $refund_time
 * @property string $status
 * @property string $status_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Model|\Eloquent $payable
 * @property-read \App\Models\Payment|null $paymentSystem
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TransactionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction filter($array = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePayableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePayableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePaymentSysId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePaymentTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePerformTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRefundTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatusDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUserId($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use HasFactory,SetCurrency;
    protected $guarded = [];

    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const PROGRESS = 'progress';
    const PENDING = 'pending';
    const CANCELED = 'canceled';

    public function payable()
    {
        return $this->morphTo('payable');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentSystem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_sys_id');
    }

    public function getPriceAttribute($value)
    {
        return $value * $this->currency();
    }

    public function scopeFilter($query, $array = [])
    {
        return $query
            ->when(isset($array['model']) && $array['model'] == 'orders' , function ($q) {
                $q->where(['payable_type' => OrderDetail::class]);
            })
            ->when(isset($array['model']) && $array['model'] == 'wallet' , function ($q) {
                $q->where(['payable_type' => Wallet::class]);
            })
            ->when(isset($array['user_id']), function ($q) use($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['status']), function ($q)  use($array)  {
                $q->where('status', $array['status']);
            });
    }
}
