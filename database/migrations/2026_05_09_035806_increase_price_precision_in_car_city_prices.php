<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('car_city_prices', function (Blueprint $table) {
        $table->decimal('price_per_day', 12, 2)->change();
    });
}

public function down()
{
    Schema::table('car_city_prices', function (Blueprint $table) {
        $table->decimal('price_per_day', 10, 2)->change();
    });
}
};
