<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Список бесед текущего пользователя
    public function index()
    {
        $conversations = Conversation::where('renter_id', Auth::id())
            ->orWhere('owner_id', Auth::id())
            ->with([
                'car',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->get();

        return response()->json($conversations);
    }

    // Получить одну беседу с сообщениями
    public function show(Conversation $conversation)
    {
        if ($conversation->renter_id !== Auth::id() && $conversation->owner_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        // Помечаем сообщения как прочитанные (если нужно)
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($conversation->load('messages.user'));
    }

    // Получить или создать беседу для автомобиля
    public function getOrCreateConversation(Car $car)
    {
        $user = Auth::user();

        // Ищем беседу между текущим пользователем и владельцем авто по этому автомобилю
        $conversation = Conversation::where('car_id', $car->id)
            ->where(function ($q) use ($user, $car) {
                $q->where('renter_id', $user->id)->where('owner_id', $car->user_id);
            })
            ->orWhere(function ($q) use ($user, $car) {
                $q->where('renter_id', $car->user_id)->where('owner_id', $user->id);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'car_id' => $car->id,
                'renter_id' => $user->id,
                'owner_id' => $car->user_id,
            ]);
        }

        return response()->json($conversation);
    }

    // Отправить сообщение в беседу
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate(['body' => 'required|string']);

        if ($conversation->renter_id !== Auth::id() && $conversation->owner_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        return response()->json($message->load('user'));
    }
}