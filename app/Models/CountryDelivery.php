<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CountryDelivery
 *
 * @property-read Country|null $country
 * @property-read Delivery|null $delivery
 * @method static Builder|CountryDelivery newModelQuery()
 * @method static Builder|CountryDelivery newQuery()
 * @method static Builder|CountryDelivery query()
 * @mixin Eloquent
 */
class CountryDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['country_id','delivery_id','price'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
