<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
