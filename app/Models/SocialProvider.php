<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SocialProvider
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialProvider whereUserId($value)
 * @mixin \Eloquent
 */
class SocialProvider extends Model
{
    use HasFactory;
    protected $fillable = ['provider','provider_id','user_id','avatar'];
    protected $hidden = ['created_at','updated_at'];
}
