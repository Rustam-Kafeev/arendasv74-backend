<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CarCityPrice extends Pivot
{
    protected $table = 'car_city_prices'; // Указываем таблицу, если имя модели не совпадает
    protected $fillable = ['price_per_day', 'buyout_price', 'description', 'is_available'];
    public $incrementing = true; // Если у вас в таблице есть первичный ключ id
}