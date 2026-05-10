<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    public function index()
    {
        $cities = Cache::remember('cities_all', 60 * 24, function () {
            return City::orderBy('name')->get()->toArray();
        });

        return response()->json($cities);
    }
}