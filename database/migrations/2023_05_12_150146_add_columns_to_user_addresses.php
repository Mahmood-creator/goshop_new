<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUserAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('postcode');
            $table->dropColumn('company_name');
            $table->dropColumn('city');
            $table->dropColumn('number');
            $table->dropColumn('name');
            $table->dropColumn('surname');
            $table->dropColumn('email');
            $table->integer('region_id')->nullable();
            $table->integer('city_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('region_id');
            $table->dropColumn('city_id');
            $table->string('postcode');
            $table->string('company_name');
            $table->string('city');
            $table->string('number');
            $table->string('surname');
            $table->string('email');
        });
    }
}
