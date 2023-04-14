<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
