<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // Translations
    public function translations() {
        return $this->hasMany(CountryTranslation::class);
    }

    public function translation() {
        return $this->hasOne(CountryTranslation::class);
    }

    public function delivery()
    {
        return $this->belongsTo(CountryDelivery::class,'id','country_id');
    }
}
