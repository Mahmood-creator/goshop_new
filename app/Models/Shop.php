<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\SetCurrency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes, Loadable, SetCurrency;
    protected $guarded = [];

    const NEW = 'new';
    const EDITED = 'edited';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const INACTIVE = 'inactive';

    const STATUS = [
        'new',
        'edited',
        'approved',
        'rejected',
        'inactive'
    ];

    protected $casts = [
        'location' => 'array',
        'open_time' => 'datetime',
        'close_time' => 'datetime',
    ];

    public function getWorkingStatusAttribute($value)
    {
        return $this->open && (now()->greaterThan(Carbon::parse($this->open_time)) && now()->lessThan(Carbon::parse($this->close_time)));
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function translations() {
        return $this->hasMany(ShopTranslation::class);
    }

    public function translation() {
        return $this->hasOne(ShopTranslation::class);
    }

    public function deliveries() {
        return $this->hasMany(Delivery::class, 'shop_id');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users() {
        return $this->hasManyThrough(User::class, Invitation::class,
            'shop_id', 'id', 'id', 'user_id');
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function invitations() {
        return $this->hasMany(Invitation::class);
    }

    public function orders() {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews() {
        return $this->hasManyThrough(Review::class, OrderDetail::class,
        'shop_id', 'reviewable_id');
    }

    public function subscription()
    {
        return $this->hasOne(ShopSubscription::class, 'shop_id')
            ->whereDate('expired_at', '>=', today())->where(['active' => 1])->orderByDesc('id');
    }

    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['user_id']), function ($q) use ($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            })
            ->when(isset($array['visibility']), function ($q) use ($array) {
                $q->where('visibility', $array['visibility']);
            })
            ->when(isset($array['open']), function ($q) use ($array) {
                $q->where('open_time', '<=', now()->format('H:i'))
                    ->where('close_time', '>=', now()->format('H:i'));
            })->when(isset($array['always_open']), function ($q) use ($array) {
                $q->whereColumn('open_time', '=', 'close_time');
            })
            ->when(isset($array['delivery']), function ($q) use ($array) {
                $q->whereHas('deliveries', function ($q) use($array) {
                    $q->where('type', $array['delivery']);
                });
            });
    }
}
