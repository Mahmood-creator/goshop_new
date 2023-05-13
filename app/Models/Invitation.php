<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Invitation
 *
 * @property int $id
 * @property int $shop_id
 * @property int $user_id
 * @property string|null $role
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\InvitationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invitation whereUserId($value)
 * @mixin \Eloquent
 */
class Invitation extends Model
{
    use HasFactory;
    protected $guarded = [];

    const STATUS = [
        'new' => 1,
        'viewed' => 2,
        'excepted' => 3,
        'rejected' => 4
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getStatusKey($value)
    {
        foreach (self::STATUS as $index => $status) {
            if ($value == $status){
                return $index;
            }
        }
    }

    public function scopeFilter($query, $array)
    {
        $query->when(isset($array['user_id']), function ($q) use($array) {
            $q->where('user_id', $array['user_id']);
        })->when(isset($array['shop_id']), function ($q) use($array) {
            $q->where('shop_id', $array['shop_id']);
        });
    }
}
