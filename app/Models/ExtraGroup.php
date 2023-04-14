<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtraGroup extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'active'];
    public $timestamps = false;

    const TYPES = [
        'color',
        'text',
        'image'
    ];

    public function getTypes() {
        return self::TYPES;
    }

    public function translations() {
        return $this->hasMany(ExtraGroupTranslation::class);
    }

    public function translation() {
        return $this->hasOne(ExtraGroupTranslation::class);
    }

    public function extraValues(): HasMany {
        return $this->hasMany(ExtraValue::class);
    }

    public function extraValue()
    {
        return $this->hasOne(ExtraValue::class);
    }
}
