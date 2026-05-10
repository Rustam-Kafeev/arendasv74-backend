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

        return response()->json($conversations);
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['car', 'renter', 'owner', 'messages.user']);
        return response()->json($conversation);
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();
        return response()->json(['message' => 'Беседа удалена']);
    }
}