<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Unit
 *
 * @property int $id
 * @property int $active
 * @property string $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UnitTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UnitTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Database\Factories\UnitFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Unit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Unit extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(UnitTranslation::class);
    }

    public function translation() {
        return $this->hasOne(UnitTranslation::class);
    }

}
