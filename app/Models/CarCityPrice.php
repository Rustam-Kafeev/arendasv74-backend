<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CarCityPrice extends Pivot
{
    protected $table = 'car_city_prices';
    protected $fillable = [
        'car_id',
        'city_id',
        'price_per_day',
        'buyout_price',
        'description',
        'is_available',
        'advance',
        'price_period', // ← должно быть здесь
    ];
    public $incrementing = true;
}