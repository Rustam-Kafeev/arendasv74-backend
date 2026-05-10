<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarView;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CarController extends Controller
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Список автомобилей с фильтрацией по городу через car_city_prices.
     */
    public function index(Request $request)
    {
        $query = Car::with(['user:id,name']);

        if ($request->has('city') && $request->city) {
            $query->whereHas('cities', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->city . '%');
            });

            $query->with([
                'cities' => function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->city . '%');
                }
            ]);
        } else {
            $query->with('cities');
        }

        $query->where('is_available', true);

        if ($request->has('brand') && $request->brand) {
            $query->where('brand', 'ilike', '%' . $request->brand . '%');
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
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
        try {
            $validated = $request->validate([
                'brand' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'description' => 'nullable|string',
                'is_available' => 'boolean',
                'cities' => 'required|json',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $car = Auth::user()->cars()->create([
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'year' => $validated['year'],
                'description' => $validated['description'] ?? '',
                'is_available' => $validated['is_available'] ?? true,
            ]);

            // Привязываем города
            $cities = json_decode($request->input('cities'), true);
            foreach ($cities as $cityData) {
                $car->cities()->attach($cityData['id'], [
                    'price_per_day' => $cityData['price'] ?? $cityData['price_per_day'] ?? 0,
                    'buyout_price' => $cityData['advance'] ?? $cityData['buyout_price'] ?? null,
                    'description' => $cityData['description'] ?? '',
                    'is_available' => true,
                    'price_period' => $cityData['price_period'] ?? 'day',
                ]);
            }

            // Загрузка фото через ImageService
            if ($request->hasFile('photos')) {
                $car->photos = $this->imageService->uploadMultiple($request->file('photos'));
                $car->save();
            }

            return response()->json($car->load('cities'), 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при создании объявления. Проверьте данные.',
            ], 500);
        }
    }

    /**
     * Просмотр одного автомобиля с учётом городов.
     */
    public function show(Car $car)
    {
        $car->load(['user:id,name,phone', 'cities']);

        // Один атомарный запрос вместо трёх
        $today = Carbon::today();
        $updated = CarView::where('car_id', $car->id)
            ->where('view_date', $today)
            ->increment('count', 1, ['view_date' => $today]);

        $car->views_today = CarView::where('car_id', $car->id)
            ->where('view_date', $today)
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

        try {
            $validated = $request->validate([
                'brand' => 'sometimes|string|max:255',
                'model' => 'sometimes|string|max:255',
                'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
                'description' => 'nullable|string',
                'is_available' => 'sometimes|boolean',
                'cities' => 'nullable|json',
                'existing_photos' => 'nullable|json',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            // Обновляем связи с городами
            if ($request->has('cities')) {
                $citiesData = json_decode($request->input('cities'), true);
                $car->cities()->detach();
                foreach ($citiesData as $cityData) {
                    $car->cities()->attach($cityData['id'], [
                        'price_per_day' => $cityData['price_per_day'] ?? $cityData['price'] ?? 0,
                        'buyout_price' => $cityData['buyout_price'] ?? $cityData['advance'] ?? null,
                        'description' => $cityData['description'] ?? '',
                        'is_available' => true,
                        'price_period' => $cityData['price_period'] ?? 'day',
                    ]);
                }
            }

            // Обработка фотографий
            $photos = [];
            if ($request->has('existing_photos')) {
                $photos = json_decode($request->input('existing_photos'), true);
            }

            if ($request->hasFile('photos')) {
                $newPhotos = $this->imageService->uploadMultiple($request->file('photos'));
                $photos = array_merge($photos, $newPhotos);
            }

            // Обновляем только поля самой машины
            $carData = collect($validated)
                ->except(['cities', 'existing_photos', 'photos'])
                ->toArray();

            $car->update($carData);
            $car->photos = $photos;
            $car->save();

            return response()->json($car->load('cities'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при сохранении. Проверьте данные.',
            ], 500);
        }
    }

    /**
     * Удаление объявления.
     */
    public function destroy(Car $car)
    {
        if (Auth::id() !== $car->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $car->delete();

        return response()->json(['message' => 'Объявление удалено']);
    }

    /**
     * Список автомобилей текущего пользователя.
     */
    public function myCars()
    {
        $cars = Auth::user()->cars()
            ->with('cities:id,name')
            ->latest()
            ->paginate(50); // ← пагинация вместо get()

        return response()->json($cars);
    }

    /**
     * Загрузка одного фото (для чата)
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        try {
            $url = $this->imageService->upload($request->file('photo'), 'chat');
            if ($url) {
                return response()->json(['url' => $url]);
            }
            return response()->json(['message' => 'Не удалось загрузить фото'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка загрузки: ' . $e->getMessage()], 500);
        }
    }
}