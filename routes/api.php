<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhoneVerificationController;
use App\Http\Controllers\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты аутентификации
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Публичный просмотр автомобилей
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{car}', [CarController::class, 'show']);

// Защищённые маршруты (требуют токен Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/my-cars', [CarController::class, 'myCars']);
    // Пользователь
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    // Верификация телефона
    Route::post('/phone/send-code', [PhoneVerificationController::class, 'sendCode']);
    Route::post('/phone/verify', [PhoneVerificationController::class, 'verify']);

    // Управление автомобилями
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{car}', [CarController::class, 'update']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);

    Route::get('/conversations', [ChatController::class, 'index']);
Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
Route::get('/cars/{car}/conversation', [ChatController::class, 'getOrCreateConversation']);
Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
});