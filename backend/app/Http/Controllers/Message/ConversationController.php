<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    // Get all conversations for authenticated user
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['latestMessage.user', 'participants'])
            ->get();

        return response()->json($conversations);
    }

    // Get messages for a specific conversation
public function getMessages(Conversation $conversation)
{
    // Verify user is a participant
    if (!$conversation->hasParticipant(Auth::id())) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $messages = $conversation->messages()->with('user')->get();

    return response()->json([
        'success' => true,
        'data' => $messages
    ]);
}


    // Get or create a conversation with a specific user
    public function show(User $user)
    {
        // Find existing conversation between auth user and target user
        $conversation = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->whereDoesntHave('participants', function ($query) use ($user) {
            // Ensure it's only 2 participants (1-on-1 chat)
            $query->whereNotIn('user_id', [Auth::id(), $user->id]);
        })
        ->first();

        // If no conversation exists, create one
        if (!$conversation) {
            $conversation = Conversation::create([]);
            $conversation->participants()->attach([Auth::id(), $user->id]);
        }

        // Load messages with sender info
        $conversation->load(['messages.user', 'participants']);

        return response()->json($conversation);
    }

    // Send a message in a conversation
    public function sendMessage(Request $request, Conversation $conversation)
    {
        // Verify user is a participant
        if (!$conversation->hasParticipant(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message_text' => 'required|string|max:5000'
        ]);

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'message_text' => $request->message_text
        ]);

        // Update conversation timestamp
        $conversation->touch();

        return response()->json($message->load('user'), 201);
    }

    // Mark conversation as read for current user
    public function markAsRead(Conversation $conversation)
    {
        if (!$conversation->hasParticipant(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->participants()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}
