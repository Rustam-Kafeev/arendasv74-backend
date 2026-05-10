<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::with(['user:id,name', 'cities:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json($cars);
    }

    public function show(Car $car)
    {
        $car->load(['user', 'cities']);
        return response()->json($car);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'is_available' => 'boolean',
        ]);

        $car = Car::create($validated);

        return response()->json($car, 201);
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'is_available' => 'boolean',
        ]);

        $car->update($validated);

        return response()->json($car);
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return response()->json(['message' => 'Автомобиль удалён']);
    }
}