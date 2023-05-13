<?php

namespace App\Models;

use Database\Factories\UserAddressFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserAddress
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property array|null $location
 * @property int $default
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $country_id
 * @property int|null $region_id
 * @property int|null $city_id
 * @property string|null $note
 * @property-read Country|null $country
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read User $user
 * @method static UserAddressFactory factory(...$parameters)
 * @method static Builder|UserAddress newModelQuery()
 * @method static Builder|UserAddress newQuery()
 * @method static Builder|UserAddress onlyTrashed()
 * @method static Builder|UserAddress query()
 * @method static Builder|UserAddress whereActive($value)
 * @method static Builder|UserAddress whereApartment($value)
 * @method static Builder|UserAddress whereCity($value)
 * @method static Builder|UserAddress whereCompanyName($value)
 * @method static Builder|UserAddress whereCountryId($value)
 * @method static Builder|UserAddress whereCreatedAt($value)
 * @method static Builder|UserAddress whereDefault($value)
 * @method static Builder|UserAddress whereDeletedAt($value)
 * @method static Builder|UserAddress whereEmail($value)
 * @method static Builder|UserAddress whereId($value)
 * @method static Builder|UserAddress whereLocation($value)
 * @method static Builder|UserAddress whereName($value)
 * @method static Builder|UserAddress whereNote($value)
 * @method static Builder|UserAddress whereNumber($value)
 * @method static Builder|UserAddress wherePostcode($value)
 * @method static Builder|UserAddress whereProvince($value)
 * @method static Builder|UserAddress whereSurname($value)
 * @method static Builder|UserAddress whereTitle($value)
 * @method static Builder|UserAddress whereUpdatedAt($value)
 * @method static Builder|UserAddress whereUserId($value)
 * @method static Builder|UserAddress withTrashed()
 * @method static Builder|UserAddress withoutTrashed()
 * @mixin Eloquent
 */
class UserAddress extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'location' => 'array'
    ];

    protected $fillable = ['user_id','title','location','default','active',
        'country_id','apartment',
        'note','name','region_id','city_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
