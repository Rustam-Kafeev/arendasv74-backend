<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\MessageController;

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('cars', CarController::class)->except('show');
    Route::resource('conversations', ConversationController::class)->only(['index','destroy']);
    Route::resource('messages', MessageController::class)->only(['index','destroy']);
});