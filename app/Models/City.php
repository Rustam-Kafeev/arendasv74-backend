<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public function cars()
{
    return $this->belongsToMany(Car::class, 'car_city_prices')
                ->using(CarCityPrice::class)
                ->withPivot(['price_per_day', 'buyout_price', 'description', 'is_available']);
}
}
