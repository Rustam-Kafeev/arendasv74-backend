<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CarCityPrice extends Pivot
{
    protected $table = 'car_city_prices';
    protected $fillable = ['price_per_day', 'buyout_price', 'advance', 'description', 'is_available'];
    public $incrementing = true;
}