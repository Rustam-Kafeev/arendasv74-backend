<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
       try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Этот email уже используется',
            'phone.unique' => 'Этот телефон уже используется',
            'password.min' => 'Пароль должен быть не менее 6 символов',
        ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt($validated['password']),
                'verification_code' => rand(100000, 999999),
            ]);

            // Отправка кода
            try {
                Mail::raw("Ваш код подтверждения: {$user->verification_code}", function ($message) use ($user) {
                    $message->to($user->email)->subject('Код подтверждения Arendasv74');
                });
            } catch (\Exception $e) {
                // Почта не настроена — не критично
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Регистрация успешна',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при регистрации. Попробуйте позже.',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Неверный email или пароль'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Вы вышли']);
    }

    // Верификация почты
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])
            ->where('verification_code', $validated['code'])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Неверный код'], 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'Почта подтверждена']);
    }

    // Запросить сброс пароля
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Если такой email существует, инструкция отправлена'], 200);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        Mail::raw("Код для сброса пароля: {$user->verification_code}", function ($message) use ($user) {
            $message->to($user->email)->subject('Сброс пароля Arendasv74');
        });

        return response()->json(['message' => 'Код отправлен на почту']);
    }

    // Сбросить пароль
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $validated['email'])
            ->where('verification_code', $validated['code'])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Неверный код'], 400);
        }

        $user->password = bcrypt($validated['password']);
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'Пароль изменён']);
    }
}