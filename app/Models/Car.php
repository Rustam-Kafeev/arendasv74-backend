<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\CarView;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'year',
        'description',
        // 'city' удалено — город теперь через связь cities()
        // 'price_per_day' удалено — цена теперь индивидуальна для каждого города
        // 'buyout_price' удалено — цена выкупа теперь индивидуальна для каждого города
        'photos',
        'is_available',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_available' => 'boolean',
    ];

    // Автоматическое добавление поля views_today в JSON
    protected $appends = ['views_today'];

    /**
     * Связь с владельцем автомобиля
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с просмотрами
     */
    public function views(): HasMany
    {
        return $this->hasMany(CarView::class);
    }

    /**
     * Связь с городами через промежуточную таблицу car_city_prices
     */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'car_city_prices')
                    ->using(CarCityPrice::class)
                    ->withPivot([
                        'price_per_day',
                        'buyout_price',
                        'description',
                        'is_available',
                        'price_period',
                    ])
                    ->withTimestamps();
    }

    /**
     * Аксессор: количество просмотров за сегодня
     */
    public function getViewsTodayAttribute(): int
    {
        $today = Carbon::today();
        return $this->views()->where('view_date', $today)->value('count') ?? 0;
    }

    /**
     * Аксессор: получение цены для города по умолчанию (для списка)
     * Замените 1 на ID вашего основного города
     */
    public function getDefaultPriceAttribute()
    {
        return $this->cities()->where('city_id', 1)->first()?->pivot->price_per_day;
    }
}