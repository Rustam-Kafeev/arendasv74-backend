<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarView;
use App\Models\City; // Убедитесь, что модель City существует и таблица заполнена
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class CarController extends Controller
{
    /**
     * Список автомобилей с фильтрацией.
     * Теперь фильтр по городу работает через таблицу car_city_prices.
     */
    public function index(Request $request)
    {
        $query = Car::whereHas('cities', function ($q) {
            $q->where('car_city_prices.is_available', true);
        })
            ->with(['user:id,name', 'cities:id,name']); // загружаем города для отображения

        // Фильтр по городу (без учёта регистра)
        if ($request->has('city')) {
            $query->whereHas('cities', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->city . '%');
            });
        }

        // Фильтр по марке
        if ($request->has('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
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
     * Создание нового объявления.
     * Автоматически делает автомобиль доступным во всех городах,
     * если не передан массив 'cities'.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'description' => 'required|string',
            'city' => 'required|string|max:255',        // базовый город (может быть основным)
            'price_per_day' => 'required|numeric|min:0',  // базовая цена
            'buyout_price' => 'nullable|numeric|min:0',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            // Необязательный массив городов с индивидуальными параметрами
            'cities' => 'nullable|array',
            'cities.*.id' => 'required|exists:cities,id',
            'cities.*.price_per_day' => 'required|numeric|min:0',
            'cities.*.buyout_price' => 'nullable|numeric|min:0',
            'cities.*.description' => 'nullable|string',
        ]);

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

        // Создаём автомобиль с базовыми полями
        $car = Auth::user()->cars()->create([
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'description' => $validated['description'],
            'city' => $validated['city'],               // остаётся для быстрой навигации
            'price_per_day' => $validated['price_per_day'],
            'buyout_price' => $validated['buyout_price'] ?? null,
            'photos' => $photos,
            'is_available' => true,
        ]);

        // Определяем, какие города привязывать
        if (!empty($validated['cities'])) {
            // Привязываем только указанные города с их параметрами
            $citiesToAttach = [];
            foreach ($validated['cities'] as $cityData) {
                $citiesToAttach[$cityData['id']] = [
                    'price_per_day' => $cityData['price_per_day'],
                    'buyout_price' => $cityData['buyout_price'] ?? null,
                    'description' => $cityData['description'] ?? $validated['description'],
                    'is_available' => true,
                ];
            }
        } else {
            // Автоматически делаем доступным во ВСЕХ городах с базовыми значениями
            $allCities = City::all();
            $citiesToAttach = [];
            foreach ($allCities as $city) {
                $citiesToAttach[$city->id] = [
                    'price_per_day' => $validated['price_per_day'],
                    'buyout_price' => $validated['buyout_price'] ?? null,
                    'description' => $validated['description'],
                    'is_available' => true,
                ];
            }
        }

        // Сохраняем связи
        $car->cities()->sync($citiesToAttach);

        return response()->json($car->load('cities'), 201);
    }

    /**
     * Просмотр одного автомобиля.
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
     * Обновление объявления (только владелец).
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
            // При желании можно добавить обновление параметров для городов
            'cities' => 'nullable|array',
            'cities.*.id' => 'required|exists:cities,id',
            'cities.*.price_per_day' => 'required|numeric|min:0',
            'cities.*.buyout_price' => 'nullable|numeric|min:0',
            'cities.*.description' => 'nullable|string',
        ]);

        $photos = $this->handlePhotosUpdate($request, $car, $validated);

        // Обновляем основные поля
        $car->update($validated);

        // Если переданы города – обновляем связи
        if (isset($validated['cities'])) {
            $citiesToAttach = [];
            foreach ($validated['cities'] as $cityData) {
                $citiesToAttach[$cityData['id']] = [
                    'price_per_day' => $cityData['price_per_day'],
                    'buyout_price' => $cityData['buyout_price'] ?? null,
                    'description' => $cityData['description'] ?? $car->description,
                    'is_available' => true,
                ];
            }
            $car->cities()->sync($citiesToAttach);
        }

        return response()->json($car->load('cities'));
    }

    /**
     * Удаление объявления (только владелец).
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
     * Мои объявления (для личного кабинета).
     */
    public function myCars()
    {
        $cars = Auth::user()->cars()->with('cities:id,name')->latest()->get();
        return response()->json($cars);
    }

    // --- Вспомогательные методы ---

    /**
     * Извлекает secure_url из ответа Cloudinary.
     */
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

    /**
     * Обрабатывает обновление фотографий.
     */
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

    /**
     * Учитывает уникальный просмотр автомобиля за сегодня.
     */
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