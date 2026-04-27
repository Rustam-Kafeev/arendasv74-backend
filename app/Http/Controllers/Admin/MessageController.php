<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with(['user', 'conversation.car'])->latest()->paginate(50);
        return view('admin.messages.index', compact('messages'));
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return back()->with('success', 'Сообщение удалено');
    }
}