<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use Loadable;
    use SoftDeletes;
    use Reviewable;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'birthday',
        'gender',
        'email',
        'phone',
        'img',
        'password',
        'firebase_token',
        'email_verified_at',
        'phone_verified_at',
        'deleted_at',
        'address',
        'passport_number',
        'passport_secret',
        'user_delivery_id',
        'address_email',
        'address_phone',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'birthday' => 'date',
    ];

    public function isOnline()
    {
        return Cache::has('user-online-' . $this->id);
    }

    public function getRoleAttribute(){
        return $this->role = $this->roles[0]->name ?? 'no role';
    }

    public function shop() {
        return $this->hasOne(Shop::class);
    }

    public function invite()
    {
        return $this->hasOne(Invitation::class);
    }


    public function moderatorShop() {
        return $this->hasOneThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    public function moderatorShops() {
        return $this->hasManyThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function socialProviders()
    {
        return $this->hasMany(SocialProvider::class,'user_id','id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }

    public function orderDetails()
    {
        return $this->hasManyThrough(OrderDetail::class,Order::class);
    }

    public function point()
    {
        return $this->hasOne(UserPoint::class, 'user_id');
    }

    public function pointHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointHistory::class, 'user_id');
    }

    public function likes()
    {
        return $this->belongsToMany(Banner::class, Like::class);
    }


}
