<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddColumnsToUserAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('passport_number');
            $table->dropColumn('passport_secret');
            $table->dropColumn('province');
            $table->dropColumn('name');
            $table->dropColumn('surname');

            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
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
            $table->dropColumn('entrance');
            $table->dropColumn('floor');
            $table->dropColumn('apartment');

            $table->string('passport_number')->nullable();
            $table->string('passport_secret')->nullable();
            $table->string('province')->nullable();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
        });
    }
}
