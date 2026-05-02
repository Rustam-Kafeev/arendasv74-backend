<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhoneVerificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;   // теперь используется
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Публичные маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/cities', [CityController::class, 'index']);

Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{car}', [CarController::class, 'show']);

// Защищённые маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Профиль
    Route::post('/profile', [ProfileController::class, 'update']);   // теперь с алиасом
    // Если метод updateProfile в AuthController больше не нужен, удалите следующую строку:
    // Route::post('/profile/update', [AuthController::class, 'updateProfile']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/my-cars', [CarController::class, 'myCars']);

    // Аутентификация
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    // Верификация телефона
    Route::post('/phone/send-code', [PhoneVerificationController::class, 'sendCode']);
    Route::post('/phone/verify', [PhoneVerificationController::class, 'verify']);

    // Автомобили
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{car}', [CarController::class, 'update']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);

    // Чат
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
    Route::get('/cars/{car}/conversation', [ChatController::class, 'getOrCreateConversation']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
});