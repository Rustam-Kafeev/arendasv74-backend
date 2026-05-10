<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with(['user', 'conversation.car'])
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function show(Message $message)
    {
        $message->load(['user', 'conversation.car']);
        return response()->json($message);
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return response()->json(['message' => 'Сообщение удалено']);
    }
}