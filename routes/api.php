<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhoneVerificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
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

// Восстановление пароля (публичные)
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Защищённые маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Профиль
    Route::post('/profile', [ProfileController::class, 'update']);

    // Дашборд
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Мои автомобили
    Route::get('/my-cars', [CarController::class, 'myCars']);

    // Аутентификация
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Верификация
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/phone/send-code', [PhoneVerificationController::class, 'sendCode']);
    Route::post('/phone/verify', [PhoneVerificationController::class, 'verify']);

    // Автомобили
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{car}', [CarController::class, 'update']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);

    // Чат
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::post('/conversations/mark-read', [ChatController::class, 'markAllAsRead']);
    Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::delete('/conversations/{conversation}', [ChatController::class, 'destroy']);
    Route::get('/cars/{car}/conversation', [ChatController::class, 'getOrCreateConversation']);
    Route::post('/upload-photo', [CarController::class, 'uploadPhoto']);

    // Админ-панель
    Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class)->except('show');
        Route::apiResource('cars', \App\Http\Controllers\Admin\CarController::class)->except('show');
        Route::apiResource('conversations', \App\Http\Controllers\Admin\ConversationController::class)->only(['index', 'destroy']);
        Route::apiResource('messages', \App\Http\Controllers\Admin\MessageController::class)->only(['index', 'destroy']);
    });
});