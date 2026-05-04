<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_city_prices', function (Blueprint $table) {
            $table->decimal('advance', 12, 2)->nullable()->after('buyout_price');
        });
    }

    public function down(): void
    {
        Schema::table('car_city_prices', function (Blueprint $table) {
            $table->dropColumn('advance');
        });
    }
};