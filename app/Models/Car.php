<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon; // ← добавлен импорт Carbon
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
        'city',
        'price_per_day',
        'buyout_price',
        'photos',
        'is_available',
    ];

    protected $casts = [
        'photos' => 'array',
        'price_per_day' => 'decimal:2',
        'buyout_price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Автоматическое добавление поля views_today в JSON
    protected $appends = ['views_today'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(CarView::class);
    }

    public function getViewsTodayAttribute(): int
    {
        $today = Carbon::today();
        // Исправлено: views() вместо view()
        return $this->views()->where('view_date', $today)->value('count') ?? 0;
    }
}