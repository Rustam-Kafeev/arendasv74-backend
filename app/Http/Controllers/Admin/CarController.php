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
        $cars = Car::with('user')->latest()->paginate(20);
        return view('admin.cars.index', compact('cars'));
    }

    public function create()
    {
        $users = User::all();
        return view('admin.cars.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:'.(date('Y')+1),
            'description' => 'required|string',
            'city' => 'required|string|max:255',
            'price_per_day' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'is_available' => 'boolean',
        ]);
        Car::create($validated);
        return redirect()->route('admin.cars.index')->with('success', 'Автомобиль добавлен.');
    }

    public function edit(Car $car)
    {
        $users = User::all();
        return view('admin.cars.edit', compact('car', 'users'));
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:'.(date('Y')+1),
            'description' => 'required|string',
            'city' => 'required|string|max:255',
            'price_per_day' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'is_available' => 'boolean',
        ]);
        $car->update($validated);
        return redirect()->route('admin.cars.index')->with('success', 'Автомобиль обновлён.');
    }

    public function destroy(Car $car)
    {
        $car->delete();
        return back()->with('success', 'Автомобиль удалён.');
    }
}