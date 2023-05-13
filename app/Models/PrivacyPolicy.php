<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PrivacyPolicy
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PrivacyPolicyTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrivacyPolicyTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyPolicy whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PrivacyPolicy extends Model
{
    use HasFactory;

    // Translations
    public function translations() {
        return $this->hasMany(PrivacyPolicyTranslation::class);
    }

    public function translation() {
        return $this->hasOne(PrivacyPolicyTranslation::class);
    }
}
