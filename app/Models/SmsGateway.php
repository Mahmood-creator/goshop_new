<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SmsGateway
 *
 * @property int $id
 * @property string $title
 * @property string $from
 * @property string $type
 * @property string|null $api_key
 * @property string|null $secret_key
 * @property string|null $service_id
 * @property string|null $text
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\SmsGatewayFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereSecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsGateway whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SmsGateway extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['created_at','updated_at'];


}
