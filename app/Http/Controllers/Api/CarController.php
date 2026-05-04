<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class CarController extends Controller
{
    /**
     * Список автомобилей с фильтрацией по городу через car_city_prices.
     */
    public function index(Request $request)
    {
        $query = Car::with(['user:id,name', 'cities']);

        // Фильтр по городу через связующую таблицу
        if ($request->has('city') && $request->city) {
            $query->whereHas('cities', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->city . '%');
            });
        }

        // Только доступные
        $query->where('is_available', true);

        // Фильтр по марке
        if ($request->has('brand') && $request->brand) {
            $query->where('brand', 'ilike', '%' . $request->brand . '%');
        }

        // Сортировка
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $allowedSorts = ['created_at', 'year'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }

        $cars = $query->paginate(12);

        return response()->json($cars);
    }

    /**
     * Создание нового объявления с привязкой к городам.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'cities' => 'required|json', // JSON-строка с массивом городов
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Создаём автомобиль
        $car = Auth::user()->cars()->create([
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'description' => '', // общее описание (можно расширить)
            'is_available' => $validated['is_available'] ?? true,
        ]);

        // Привязываем города с индивидуальными параметрами
        $cities = json_decode($request->input('cities'), true);
        foreach ($cities as $cityData) {
            $car->cities()->attach($cityData['id'], [
                'price_per_day' => $cityData['price_per_day'],
                'buyout_price' => $cityData['buyout_price'] ?? null,
                'advance' => $cityData['advance'] ?? null,
                'description' => $cityData['description'] ?? '',
                'is_available' => true,
            ]);
        }

        // Загрузка фото в Cloudinary
        $photos = [];
        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $photo->getRealPath(),
                    ['folder' => 'cars', 'quality' => 'auto', 'fetch_format' => 'auto']
                );
                // Извлекаем secure_url
                $secureUrl = null;
                if ($uploadResult instanceof \ArrayObject) {
                    $data = $uploadResult->getArrayCopy();
                    $secureUrl = $data['secure_url'] ?? null;
                } elseif (is_array($uploadResult)) {
                    $secureUrl = $uploadResult['secure_url'] ?? null;
                } elseif (is_object($uploadResult)) {
                    $data = json_decode(json_encode($uploadResult), true);
                    $secureUrl = $data['secure_url'] ?? null;
                }
                if ($secureUrl) {
                    $photos[] = $secureUrl;
                }
            }
            $car->photos = $photos;
            $car->save();
        }

        return response()->json($car->load('cities'), 201);
    }

    /**
     * Просмотр одного автомобиля с учётом городов.
     */
    public function show(Car $car)
    {
        $car->load(['user:id,name,phone', 'cities']);

        // Увеличиваем счётчик просмотров
        $sessionKey = 'viewed_cars_' . Carbon::today()->toDateString();
        $viewedCars = session()->get($sessionKey, []);
        if (!in_array($car->id, $viewedCars)) {
            $carView = CarView::firstOrNew([
                'car_id' => $car->id,
                'view_date' => Carbon::today(),
            ]);
            $carView->count = $carView->exists ? $carView->count + 1 : 1;
            $carView->save();
            session()->put($sessionKey, array_merge($viewedCars, [$car->id]));
        }

        $car->views_today = CarView::where('car_id', $car->id)
            ->where('view_date', Carbon::today())
            ->value('count') ?? 0;

        return response()->json($car);
    }

    /**
     * Обновление объявления (города, фото и др.)
     */
    public function update(Request $request, Car $car)
    {
        if (Auth::id() !== $car->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'brand' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'cities' => 'nullable|json',
            'existing_photos' => 'nullable|json',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Обновление связей с городами
        if ($request->has('cities')) {
            $citiesData = json_decode($request->input('cities'), true);
            $car->cities()->detach(); // удаляем старые связи
            foreach ($citiesData as $cityData) {
                $car->cities()->attach($cityData['id'], [
                    'price_per_day' => $cityData['price_per_day'],
                    'buyout_price' => $cityData['buyout_price'] ?? null,
                    'advance' => $cityData['advance'] ?? null,
                    'description' => $cityData['description'] ?? '',
                    'is_available' => true,
                ]);
            }
        }

        // Обработка фотографий (добавление новых)
        $photos = [];
        if ($request->has('existing_photos')) {
            $photos = json_decode($request->input('existing_photos'), true);
        }
        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $photo->getRealPath(),
                    ['folder' => 'cars', 'quality' => 'auto', 'fetch_format' => 'auto']
                );
                $secureUrl = null;
                if ($uploadResult instanceof \ArrayObject) {
                    $data = $uploadResult->getArrayCopy();
                    $secureUrl = $data['secure_url'] ?? null;
                } elseif (is_array($uploadResult)) {
                    $secureUrl = $uploadResult['secure_url'] ?? null;
                } elseif (is_object($uploadResult)) {
                    $data = json_decode(json_encode($uploadResult), true);
                    $secureUrl = $data['secure_url'] ?? null;
                }
                if ($secureUrl)
                    $photos[] = $secureUrl;
            }
        }

        $car->update($validated);
        $car->photos = $photos;
        $car->save();

        return response()->json($car->load('cities'));
    }

    /**
     * Удаление объявления.
     */
    public function destroy(Car $car)
    {
        if (Auth::id() !== $car->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // При желании можно удалить фото из Cloudinary (по желанию)
        $car->delete();

        return response()->json(['message' => 'Объявление удалено']);
    }

    /**
     * Список автомобилей текущего пользователя.
     */
    public function myCars()
    {
        $cars = Auth::user()->cars()->with('cities')->latest()->get();
        return response()->json($cars);
    }
}