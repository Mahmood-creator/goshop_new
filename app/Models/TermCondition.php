<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TermCondition
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TermConditionTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TermConditionTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Database\Factories\TermConditionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TermCondition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TermCondition extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(TermConditionTranslation::class);
    }

    public function translation() {
        return $this->hasOne(TermConditionTranslation::class);
    }

}
