<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Сразу выдаём токен после регистрации
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Логин и выдача токена.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Выход (отзыв текущего токена).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function updateProfile(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'phone' => 'sometimes|string|max:20',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // до 2 МБ
    ]);

    // Обработка аватара
    if ($request->hasFile('avatar')) {
        // Удаляем старый аватар, если он был
        if ($user->avatar) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $validated['avatar'] = Storage::url($path);
    }

    $user->update($validated);

    return response()->json($user);
}
}