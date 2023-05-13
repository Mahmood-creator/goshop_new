<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * App\Models\ShopLocation
 *
 * @property int $id
 * @property int $shop_id
 * @property int|null $country_id
 * @property int|null $region_id
 * @property int|null $city_id
 * @property float $delivery_fee
 * @property int $pickup
 * @property int $delivery
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read City|null $city
 * @property-read Country|null $country
 * @property-read Region|null $region
 * @method static Builder|ShopLocation newModelQuery()
 * @method static Builder|ShopLocation newQuery()
 * @method static Builder|ShopLocation query()
 * @method static Builder|ShopLocation whereCityId($value)
 * @method static Builder|ShopLocation whereCountryId($value)
 * @method static Builder|ShopLocation whereCreatedAt($value)
 * @method static Builder|ShopLocation whereDeletedAt($value)
 * @method static Builder|ShopLocation whereDeliveryFee($value)
 * @method static Builder|ShopLocation whereId($value)
 * @method static Builder|ShopLocation wherePickup($value)
 * @method static Builder|ShopLocation whereRegionId($value)
 * @method static Builder|ShopLocation whereShopId($value)
 * @method static Builder|ShopLocation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ShopLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'shop_id',
        'country_id',
        'region_id',
        'city_id',
        'delivery_fee',
        'pickup',
        'delivery',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
