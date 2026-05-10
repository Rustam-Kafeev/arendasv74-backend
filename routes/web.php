<?php

use Illuminate\Support\Facades\Route;

// Редирект с главной на фронтенд
Route::get('/', function () {
    return redirect('http://localhost:3000');
});

// Создание администратора (временный маршрут)
Route::get('/make-admin', function () {
    $user = \App\Models\User::where('email', 'krr12@mail.ru')->first();
    if (!$user) {
        $user = new \App\Models\User;
        $user->name = 'Admin';
        $user->email = 'krr12@mail.ru';
    }
    $user->password = bcrypt('Arina280116');
    $user->is_admin = true;
    $user->save();
    return 'Администратор создан!';
});