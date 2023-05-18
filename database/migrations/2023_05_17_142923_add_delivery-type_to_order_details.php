<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryTypeToOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_type_id');
            $table->string('delivery_type');
            $table->foreignId('shop_location_id')->nullable()->constrained('shop_locations')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->foreignId('delivery_type_id')->nullable()->constrained('deliveries');
            $table->integer('delivery_id');
            $table->dropColumn('delivery_type');
            $table->dropConstrainedForeignId('shop_location_id');
        });
    }
}
