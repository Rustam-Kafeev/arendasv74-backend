<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    /**
     * Отправка кода подтверждения на телефон текущего пользователя.
     */
    public function sendCode(Request $request)
    {
        $user = Auth::user();

        if (!$user->phone) {
            throw ValidationException::withMessages([
                'phone' => ['У вас не указан номер телефона.'],
            ]);
        }

        // Удаляем старые коды для этого пользователя
        PhoneVerification::where('user_id', $user->id)->delete();

        // Генерируем 6-значный код
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Создаём запись без массового присвоения
        $verification = new PhoneVerification();
        $verification->user_id = $user->id;
        $verification->phone = $user->phone;
        $verification->code = $code;
        $verification->expires_at = Carbon::now()->addMinutes(5);
        $verification->save();

        // Логируем код (временно)
        \Log::info("SMS-код для {$user->phone}: $code");

        return response()->json(['message' => 'Код отправлен']);
    }

    /**
     * Проверка кода и подтверждение телефона.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        $verification = PhoneVerification::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$verification) {
            throw ValidationException::withMessages([
                'code' => ['Неверный или истекший код.'],
            ]);
        }

        // Подтверждаем телефон
        $user->phone_verified_at = Carbon::now();
        $user->save();

        // Удаляем использованный код
        $verification->delete();

        return response()->json(['message' => 'Телефон успешно подтверждён']);
    }
}