<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];
    const ATB = 'atb';
    const CASH = 'cash';
    const WALLET = 'wallet';
    const PAYPAL = 'paypal';
    const STRIPE = 'stripe';
    const PAYSTACK = 'paystack';
    const RAZORPAY = 'razorpay';
    public function translations() {
        return $this->hasMany(PaymentTranslation::class);
    }

    public function translation() {
        return $this->hasOne(PaymentTranslation::class);
    }
}
