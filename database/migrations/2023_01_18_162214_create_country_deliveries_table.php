<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountryDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained('deliveries')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->double('price');
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
        Schema::dropIfExists('country_deliveries');
    }
}
