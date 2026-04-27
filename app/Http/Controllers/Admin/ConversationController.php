<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['car', 'renter', 'owner'])
        ->withCount('messages')
        ->latest()
        ->paginate(20);
        return view('admin.conversations.index', compact('conversations'));
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();
        return back()->with('success', 'Беседа удалена');
    }
}