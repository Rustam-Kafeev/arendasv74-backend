<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarView;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class CarController extends Controller
{
    /**
     * Список автомобилей с фильтрацией по городу через car_city_prices.
     */
    public function index(Request $request)
    {
        $query = Car::whereHas('cities', function ($q) {
            $q->where('car_city_prices.is_available', true);
        })
            ->with(['user:id,name', 'cities:id,name']);

        if ($request->has('city')) {
            $query->whereHas('cities', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->city . '%');
            });
        }

        if ($request->has('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (in_array($sort, ['created_at', 'year'])) {
            $query->orderBy($sort, $direction);
        }

        return response()->json($query->paginate(12));
    }

    /**
     * Создание объявления с поддержкой мультигородов.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'required|string',
            'city' => 'required|string|max:255',
            'price_per_day' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|min:0',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Принимаем как JSON-строку, а не массив (из-за FormData)
            'cities' => 'nullable|json',
        ]);

        // Парсим города, если они переданы
        $citiesData = [];
        if ($request->filled('cities')) {
            $citiesData = json_decode($request->input('cities'), true);
            if (!is_array($citiesData)) {
                return response()->json(['message' => 'Поле cities должно быть массивом'], 422);
            }
            // Дополнительная валидация каждого элемента
            $validator = Validator::make($citiesData, [
                '*.id' => 'required|exists:cities,id',
                '*.price_per_day' => 'required|numeric|min:0',
                '*.buyout_price' => 'nullable|numeric|min:0',
                '*.description' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        // Загрузка фото в Cloudinary
        $photos = [];
        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $photo->getRealPath(),
                        ['folder' => 'cars', 'quality' => 'auto', 'fetch_format' => 'auto']
                    );
                    $secureUrl = $this->extractSecureUrl($uploadResult);
                    if ($secureUrl) {
                        $photos[] = $secureUrl;
                    }
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                }
            }
        }

        // Создаём автомобиль
        $car = Auth::user()->cars()->create([
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'description' => $validated['description'],
            'city' => $validated['city'],
            'price_per_day' => $validated['price_per_day'],
            'buyout_price' => $validated['buyout_price'] ?? null,
            'photos' => $photos,
            'is_available' => true,
        ]);

        // Привязка городов
        if (!empty($citiesData)) {
            // Только указанные города
            $attach = [];
            foreach ($citiesData as $cityData) {
                $attach[$cityData['id']] = [
                    'price_per_day' => $cityData['price_per_day'],
                    'buyout_price' => $cityData['buyout_price'] ?? null,
                    'description' => $cityData['description'] ?? $validated['description'],
                    'is_available' => true,
                ];
            }
            $car->cities()->sync($attach);
        } else {
            // Если города не переданы – привязываем все города РФ с базовыми параметрами
            $allCities = City::all();
            $attach = [];
            foreach ($allCities as $city) {
                $attach[$city->id] = [
                    'price_per_day' => $validated['price_per_day'],
                    'buyout_price' => $validated['buyout_price'] ?? null,
                    'description' => $validated['description'],
                    'is_available' => true,
                ];
            }
            $car->cities()->sync($attach);
        }

        return response()->json($car->fresh('cities'), 201);
    }

    /**
     * Просмотр автомобиля с учётом просмотров.
     */
    public function show(Car $car)
    {
        $this->trackView($car);
        $car->load(['user:id,name,phone', 'cities:id,name']);
        $car->views_today = CarView::where('car_id', $car->id)
            ->where('view_date', Carbon::today())
            ->value('count') ?? 0;

        return response()->json($car);
    }

    /**
     * Обновление объявления.
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
            'description' => 'sometimes|string',
            'city' => 'sometimes|string|max:255',
            'price_per_day' => 'sometimes|numeric|min:0',
            'buyout_price' => 'nullable|numeric|min:0',
            'existing_photos' => 'nullable|json',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_available' => 'sometimes|boolean',
            'cities' => 'nullable|json',
        ]);

        $photos = $this->handlePhotosUpdate($request, $car, $validated);

        // Обновляем основные поля
        $car->update($validated);

        // Обработка городов
        if ($request->filled('cities')) {
            $citiesData = json_decode($request->input('cities'), true);
            if (is_array($citiesData)) {
                $validator = Validator::make($citiesData, [
                    '*.id' => 'required|exists:cities,id',
                    '*.price_per_day' => 'required|numeric|min:0',
                    '*.buyout_price' => 'nullable|numeric|min:0',
                    '*.description' => 'nullable|string',
                ]);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $attach = [];
                foreach ($citiesData as $cityData) {
                    $attach[$cityData['id']] = [
                        'price_per_day' => $cityData['price_per_day'],
                        'buyout_price' => $cityData['buyout_price'] ?? null,
                        'description' => $cityData['description'] ?? $car->description,
                        'is_available' => true,
                    ];
                }
                $car->cities()->sync($attach);
            }
        }

        return response()->json($car->fresh('cities'));
    }

    /**
     * Удаление автомобиля.
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
     * Автомобили текущего пользователя.
     */
    public function myCars()
    {
        return response()->json(
            Auth::user()->cars()->with('cities:id,name')->latest()->get()
        );
    }

    // ─── Вспомогательные методы ─────────────────────────────────────────────

    private function extractSecureUrl($uploadResult): ?string
    {
        if ($uploadResult instanceof \ArrayObject) {
            $data = $uploadResult->getArrayCopy();
            return $data['secure_url'] ?? null;
        }
        if (is_array($uploadResult)) {
            return $uploadResult['secure_url'] ?? null;
        }
        if (is_object($uploadResult)) {
            $data = json_decode(json_encode($uploadResult), true);
            return $data['secure_url'] ?? null;
        }
        return null;
    }

    private function handlePhotosUpdate(Request $request, Car $car, array &$validated): array
    {
        $photos = [];
        if ($request->has('existing_photos')) {
            $existing = json_decode($request->input('existing_photos'), true);
            $photos = is_array($existing) ? $existing : [];
        }

        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $photo->getRealPath(),
                        ['folder' => 'cars', 'quality' => 'auto', 'fetch_format' => 'auto']
                    );
                    $secureUrl = $this->extractSecureUrl($uploadResult);
                    if ($secureUrl) {
                        $photos[] = $secureUrl;
                    }
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                }
            }
        }

        $validated['photos'] = $photos;
        return $photos;
    }

    private function trackView(Car $car): void
    {
        $sessionKey = 'viewed_cars_' . Carbon::today()->toDateString();
        $viewedCars = session()->get($sessionKey, []);

        if (!in_array($car->id, $viewedCars)) {
            $today = Carbon::today();
            $carView = CarView::firstOrNew([
                'car_id' => $car->id,
                'view_date' => $today,
            ]);
            $carView->count = ($carView->exists ? $carView->count : 0) + 1;
            $carView->save();

            $viewedCars[] = $car->id;
            session()->put($sessionKey, $viewedCars);
        }
    }
}