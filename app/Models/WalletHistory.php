<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WalletHistory
 *
 * @property int $id
 * @property string $uuid
 * @property string $wallet_uuid
 * @property int|null $transaction_id
 * @property string $type
 * @property float $price
 * @property string|null $note
 * @property string $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Wallet|null $wallet
 * @method static \Database\Factories\WalletHistoryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletHistory whereWalletUuid($value)
 * @mixin \Eloquent
 */
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
