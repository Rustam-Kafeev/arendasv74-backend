<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\CarView;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class CarController extends Controller
{
    /**
     * Список автомобилей с фильтрацией.
     */
    public function index(Request $request)
    {
        $query = Car::where('is_available', true)->with('user:id,name');

        // Фильтр по городу (без учета регистра)
        if ($request->has('city')) {
            $query->where('city', 'ilike', '%' . $request->city . '%');
        }

        // Фильтр по марке
        if ($request->has('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        // Сортировка
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $allowedSorts = ['price_per_day', 'year', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }

        $cars = $query->paginate(12);

        return response()->json($cars);
    }

    /**
     * Создание нового объявления.
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
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // до 5MB
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $photo->getRealPath(),
                        ['folder' => 'cars']
                    );
                    // Извлекаем secure_url универсальным способом
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
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                }
            }
        }

        $car = Auth::user()->cars()->create([
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'description' => $validated['description'],
            'city' => $validated['city'],
            'price_per_day' => $validated['price_per_day'],
            'buyout_price' => $validated['buyout_price'] ?? null,
            'photos' => $photos,
        ]);

        return response()->json($car, 201);
    }

    /**
     * Просмотр одного автомобиля.
     */
    public function show(Car $car)
    {
        \Log::info('Car viewed: ' . $car->id . ' by session ' . session()->getId());
        $sessionKey = 'viewed_cars_' . Carbon::today()->toDateString();
        $viewedCars = session()->get($sessionKey, []);

        // Если данный автомобиль ещё не просматривался в этой сессии за сегодня
        if (!in_array($car->id, $viewedCars)) {
            // Увеличиваем счётчик
            $today = Carbon::today();
            $carView = CarView::firstOrNew([
                'car_id' => $car->id,
                'view_date' => $today,
            ]);
            $carView->count = $carView->exists ? $carView->count + 1 : 1;
            $carView->save();

            // Добавляем ID автомобиля в список просмотренных
            $viewedCars[] = $car->id;
            session()->put($sessionKey, $viewedCars);
        }

        $car->load('user:id,name,phone');
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
        ]);

        $photos = [];
        if ($request->has('existing_photos')) {
            $existing = json_decode($request->input('existing_photos'), true);
            $photos = is_array($existing) ? $existing : [];
        }

        // Загрузка новых фото в Cloudinary
        if ($request->hasFile('photos')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            foreach ($request->file('photos') as $photo) {
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $photo->getRealPath(),
                        ['folder' => 'cars']
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
                    if ($secureUrl) {
                        $photos[] = $secureUrl;
                    }
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload failed: ' . $e->getMessage());
                }
            }
        }

        // Удаление старых фото из облака (опционально)
        // Если нужно удалить старые фото из Cloudinary, раскомментируйте и реализуйте
        // $oldPhotos = $car->photos ?? [];
        // $removedPhotos = array_diff($oldPhotos, $photos);
        // foreach ($removedPhotos as $removed) {
        //     // Извлеките public_id из URL и удалите через API Cloudinary
        // }

        $validated['photos'] = $photos;
        $car->update($validated);

        return response()->json($car);
    }

    /**
     * Удаление объявления (только владелец).
     */
    public function destroy(Car $car)
    {
        if (Auth::id() !== $car->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Удаление фото из Cloudinary (опционально, по желанию)
        // if ($car->photos) {
        //     foreach ($car->photos as $photoUrl) {
        //         // Извлеките public_id из URL и удалите через API Cloudinary
        //     }
        // }

        $car->delete();

        return response()->json(['message' => 'Объявление удалено']);
    }

    public function myCars()
    {
        $cars = Auth::user()->cars()->latest()->get();
        return response()->json($cars);
    }
}