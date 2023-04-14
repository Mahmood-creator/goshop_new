<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'wallet_uuid', 'transaction_id', 'type', 'price', 'note', 'status', 'created_by'];

    const TYPES = [
        'processed',
        'paid',
        'rejected',
        'canceled'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_uuid', 'uuid');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Wallet::class,
            'uuid', 'id', 'wallet_uuid', 'user_id');
    }
}
