<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Country
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property boolean|true $status
 * @property-read CountryTranslation|null $translation
 * @property-read Collection<int, CountryTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|Country filter($filter)
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country whereCreatedAt($value)
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereName($value)
 * @method static Builder|Country whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Country extends Model
{
    use HasFactory;
    protected $fillable = ['name','status'];
    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CountryTranslation::class);
    }

    public function scopeFilter($query, $filter)
    {
        return $query->when(isset($filter['search']), function ($q) use ($filter) {
            $q->where('name', 'LIKE', '%'. $filter['search'] . '%');
        });
    }
}
