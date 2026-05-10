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
    // Список бесед текущего пользователя с информацией о новых сообщениях
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('renter_id', $userId)
            ->orWhere('owner_id', $userId)
            ->with([
                'car:id,brand,model',
                'owner:id,name',
                'renter:id,name',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($userId) {
                    $q->where('user_id', '!=', $userId)
                        ->where('is_read', false);
                }
            ])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get()
            ->map(function ($conv) use ($userId) {
                $interlocutor = $conv->renter_id === $userId ? $conv->owner : $conv->renter;

                return [
                    'id' => $conv->id,
                    'car_id' => $conv->car_id,
                    'car_name' => $conv->car ? $conv->car->brand . ' ' . $conv->car->model : 'Автомобиль удалён',
                    'interlocutor_name' => $interlocutor->name ?? 'Пользователь',
                    'interlocutor_id' => $interlocutor->id ?? null,
                    'last_message' => $conv->messages->first()?->body ?? null,
                    'last_message_time' => $conv->messages->first()?->created_at?->diffForHumans() ?? null,
                    'unread_count' => $conv->unread_count,
                    'created_at' => $conv->created_at,
                ];
            });

        return response()->json($conversations);
    }

    // Получить одну беседу с сообщениями
    public function show(Conversation $conversation)
    {
        if ($conversation->renter_id !== Auth::id() && $conversation->owner_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        // Помечаем сообщения как прочитанные
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Загружаем связи с пагинацией сообщений
        $conversation->load([
            'owner:id,name,phone',
            'renter:id,name,phone',
            'car:id,brand,model',
            'messages' => function ($query) {
                $query->with('user:id,name')
                    ->latest()
                    ->paginate(30);
            },
        ]);

        return response()->json($conversation);
    }

    // Получить или создать беседу для автомобиля
    public function getOrCreateConversation(Car $car)
    {
        $user = Auth::user();

        $renterId = $user->id;
        $ownerId = $car->user_id;

        // Ищем существующий диалог для этого автомобиля между этими двумя пользователями
        $conversation = Conversation::where('car_id', $car->id)
            ->where(function ($q) use ($renterId, $ownerId) {
                $q->where(function ($sub) use ($renterId, $ownerId) {
                    $sub->where('renter_id', $renterId)
                        ->where('owner_id', $ownerId);
                })->orWhere(function ($sub) use ($renterId, $ownerId) {
                    $sub->where('renter_id', $ownerId)
                        ->where('owner_id', $renterId);
                });
            })
            ->first();

        // Если диалог не найден, создаём новый
        if (!$conversation) {
            $conversation = Conversation::create([
                'car_id' => $car->id,
                'renter_id' => $renterId,
                'owner_id' => $ownerId,
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

    // Отметить все диалоги как прочитанные
    public function markAllAsRead()
    {
        $userId = Auth::id();

        $conversationIds = Conversation::where('renter_id', $userId)
            ->orWhere('owner_id', $userId)
            ->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // Удалить диалог
    public function destroy(Conversation $conversation)
    {
        if ($conversation->renter_id !== Auth::id() && $conversation->owner_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        $conversation->delete();

        return response()->json(['message' => 'Диалог удалён']);
    }
}