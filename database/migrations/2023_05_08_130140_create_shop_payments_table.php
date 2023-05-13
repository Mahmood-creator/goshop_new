<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('status')->default(false);
            $table->string('client_id')->nullable();
            $table->string('secret_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_payments');
    }
}
