<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public function cars()
{
    return $this->hasMany(Car::class);
}

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Аксессор для аватара (возвращает полный URL)
public function getAvatarUrlAttribute(): string
{
    if (empty($this->avatar)) {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&size=128';
    }

    // Если уже полный URL, возвращаем как есть
    if (str_starts_with($this->avatar, 'http')) {
        return $this->avatar;
    }

    // Если путь уже содержит '/storage/', добавляем базовый URL приложения
    if (str_starts_with($this->avatar, '/storage/')) {
        return url($this->avatar);
    }

    // Иначе считаем, что это относительный путь от корня диска (avatars/...)
    return Storage::url($this->avatar);
}

// Чтобы поле автоматически добавлялось в JSON, добавьте:
protected $appends = ['avatar_url'];
}