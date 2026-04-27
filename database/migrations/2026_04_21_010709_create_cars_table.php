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
      Schema::create('cars', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // владелец
    $table->string('brand');                    // марка
    $table->string('model');                    // модель
    $table->integer('year');                    // год выпуска
    $table->text('description');                // описание
    $table->string('city');                     // город
    $table->decimal('price_per_day', 10, 2);    // цена аренды в день
    $table->decimal('buyout_price', 12, 2)->nullable(); // цена выкупа
    $table->json('photos')->nullable();         // массив путей к фото
    $table->boolean('is_available')->default(true); // доступность
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
