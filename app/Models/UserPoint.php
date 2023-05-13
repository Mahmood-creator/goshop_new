<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserPoint
 *
 * @property int $id
 * @property int $user_id
 * @property float $price
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPoint whereUserId($value)
 * @mixin \Eloquent
 */
class UserPoint extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
