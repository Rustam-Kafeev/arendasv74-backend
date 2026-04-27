<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarView extends Model
{
    protected $fillable = ['car_id', 'view_date', 'count'];
    
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}