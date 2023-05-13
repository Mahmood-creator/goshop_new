<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('country_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->double('delivery_fee')->nullable();
            $table->boolean('pickup')->default(true);
            $table->boolean('delivery')->default(true);
            $table->softDeletes();
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
        Schema::dropIfExists('shop_locations');
    }
}
