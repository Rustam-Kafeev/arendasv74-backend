<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Cloudinary\Cloudinary;

class ProfileController extends Controller
{
    /**
     * Получить данные текущего пользователя.
     */
    public function show()
    {
        return response()->json(Auth::user()->load('cars'));
    }

    /**
     * Обновить профиль (имя, телефон, email, аватар).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Загрузка аватара в Cloudinary
        if ($request->hasFile('avatar')) {
            try {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('avatar')->getRealPath(),
                    ['folder' => 'avatars']
                );

                $secureUrl = null;
                if ($uploadResult instanceof \ArrayObject) {
                    $data = $uploadResult->getArrayCopy();
                    $secureUrl = $data['secure_url'] ?? null;
                } elseif (is_array($uploadResult)) {
                    $secureUrl = $uploadResult['secure_url'] ?? null;
                } elseif (is_object($uploadResult)) {
                    $data = json_decode(json_encode($uploadResult), true);
                    $secureUrl = $data['secure_url'] ?? null;
                }

                if (!$secureUrl) {
                    \Log::error('Cloudinary upload did not return secure_url', ['response' => $uploadResult]);
                    return response()->json(['message' => 'Ошибка загрузки аватара'], 500);
                }

                $validated['avatar'] = $secureUrl;
            } catch (\Exception $e) {
                \Log::error('Avatar upload failed: ' . $e->getMessage());
                return response()->json(['message' => 'Ошибка загрузки аватара'], 500);
            }
        }

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Сменить пароль.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Пароль успешно изменён']);
    }
}