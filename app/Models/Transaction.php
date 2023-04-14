<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
