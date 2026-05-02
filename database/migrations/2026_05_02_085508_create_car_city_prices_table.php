<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_city_prices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('car_id')->constrained()->onDelete('cascade');
    $table->foreignId('city_id')->constrained()->onDelete('cascade');
    $table->decimal('price_per_day', 10, 2); // Цена для конкретного города
    $table->decimal('buyout_price', 12, 2)->nullable(); // Цена выкупа для города
    $table->text('description')->nullable(); // Уникальное описание для города
    $table->boolean('is_available')->default(true); // Доступность в городе
    $table->unique(['car_id', 'city_id']); // Один город — одна запись для авто
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_city_prices');
    }
};
