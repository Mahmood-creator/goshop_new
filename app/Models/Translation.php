<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Translation
 *
 * @property int $id
 * @property int $status
 * @property string $locale
 * @property string $group
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Translation filter($array = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Translation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereValue($value)
 * @mixin \Eloquent
 */
class Translation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeFilter($query, $array = [])
    {
        return $query->when(isset($array['group']), function ($q)  use ($array) {
            $q->where('group', $array['group']);
        })->when(isset($array['locale']), function ($q)  use ($array) {
            $q->where('locale', $array['locale']);
        });
    }
}
