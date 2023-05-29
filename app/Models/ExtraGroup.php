<?php

namespace App\Models;

use Database\Factories\ExtraGroupFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ExtraGroup
 *
 * @property int $id
 * @property string|null $type
 * @property int $active
 * @property-read ExtraValue|null $extraValue
 * @property-read Collection<int, ExtraValue> $extraValues
 * @property-read int|null $extra_values_count
 * @property-read ExtraGroupTranslation|null $translation
 * @property-read Collection<int, ExtraGroupTranslation> $translations
 * @property-read int|null $translations_count
 * @method static ExtraGroupFactory factory(...$parameters)
 * @method static Builder|ExtraGroup newModelQuery()
 * @method static Builder|ExtraGroup newQuery()
 * @method static Builder|ExtraGroup query()
 * @method static Builder|ExtraGroup whereActive($value)
 * @method static Builder|ExtraGroup whereId($value)
 * @method static Builder|ExtraGroup whereType($value)
 * @mixin Eloquent
 */
class ExtraGroup extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'active','created_by'];
    public $timestamps = false;

    const TYPES = [
        'color',
        'text',
        'image'
    ];

    public function getTypes(): array
    {
        return self::TYPES;
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ExtraGroupTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ExtraGroupTranslation::class);
    }

    public function extraValues(): HasMany
    {
        return $this->hasMany(ExtraValue::class);
    }

    public function extraValue(): HasOne
    {
        return $this->hasOne(ExtraValue::class);
    }
}
