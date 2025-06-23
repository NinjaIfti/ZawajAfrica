<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use App\Services\UserTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    protected UserTierService $tierService;

    public function __construct(UserTierService $tierService)
    {
        $this->tierService = $tierService;
    }
    /**
     * Display the messages index page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Format profile photo URL if it exists
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        // Get all users that the current user has conversations with
        $conversations = $user->conversations();
        
        // Format the conversations for the frontend
        $formattedConversations = $conversations->map(function ($conversationUser) use ($user) {
            // Get the latest message between these users
            $latestMessage = Message::where(function ($query) use ($user, $conversationUser) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $conversationUser->id);
            })->orWhere(function ($query) use ($user, $conversationUser) {
                $query->where('sender_id', $conversationUser->id)
                      ->where('receiver_id', $user->id);
            })
            ->latest()
            ->first();
            
            // Count unread messages
            $unreadCount = Message::where('sender_id', $conversationUser->id)
                                 ->where('receiver_id', $user->id)
                                 ->where('is_read', false)
                                 ->count();
            
            // Format profile photo URL
            $profilePhoto = $conversationUser->profile_photo 
                ? asset('storage/' . $conversationUser->profile_photo) 
                : '/images/placeholder.jpg';
            
            return [
                'id' => $conversationUser->id,
                'name' => $conversationUser->name,
                'profile_photo' => $profilePhoto,
                'last_message' => $latestMessage ? $latestMessage->content : '',
                'last_message_time' => $latestMessage ? $latestMessage->created_at->format('H:i') : '',
                'unread_count' => $unreadCount,
                'is_online' => false // Placeholder for online status
            ];
        });
        

        
        return Inertia::render('Messages/Index', [
            'user' => $user,
            'conversations' => $formattedConversations
        ]);
    }
    
    /**
     * Show a specific conversation.
     */
    public function show($id)
    {
        $user = Auth::user();
        $otherUser = User::findOrFail($id);
        
        // Format profile photos
        if ($user->profile_photo) {
            $user->profile_photo = asset('storage/' . $user->profile_photo);
        }
        
        if ($otherUser->profile_photo) {
            $otherUser->profile_photo = asset('storage/' . $otherUser->profile_photo);
        }
        
        // Get messages between the two users
        $messages = Message::where(function ($query) use ($user, $otherUser) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $otherUser->id);
        })->orWhere(function ($query) use ($user, $otherUser) {
            $query->where('sender_id', $otherUser->id)
                  ->where('receiver_id', $user->id);
        })
        ->orderBy('created_at')
        ->get()
        ->map(function ($message) use ($user) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'time' => $message->created_at->format('H:i'),
                'is_mine' => $message->sender_id === $user->id,
                'is_read' => $message->is_read
            ];
        });
        
        // Mark all unread messages as read
        Message::where('sender_id', $otherUser->id)
              ->where('receiver_id', $user->id)
              ->where('is_read', false)
              ->update(['is_read' => true, 'read_at' => now()]);
        

        
        return Inertia::render('Messages/Show', [
            'user' => $user,
            'otherUser' => $otherUser,
            'messages' => $messages
        ]);
    }
    
    /**
     * Send a new message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000'
        ]);
        
        $sender = Auth::user();
        $receiver = User::findOrFail($request->receiver_id);

        // Check if sender can send messages
        $canSend = $this->tierService->canSendMessage($sender);
        if (!$canSend['allowed']) {
            \Log::info("Message attempt blocked by tier restriction", [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'sender_tier' => $this->tierService->getUserTier($sender),
                'reason' => $canSend['reason'] ?? 'tier_restriction',
                'daily_messages_used' => $canSend['used'] ?? 0,
                'daily_limit' => $canSend['limit'] ?? 0
            ]);
            
            return response()->json([
                'error' => $canSend['message'],
                'reason' => $canSend['reason'] ?? 'tier_restriction',
                'upgrade_prompt' => true,
                'tier' => $this->tierService->getUserTier($sender)
            ], 403);
        }

        // Check for free user interaction
        $freeUserCheck = $this->tierService->checkFreeUserInteraction($sender, $receiver);
        if ($freeUserCheck['requires_upgrade']) {
            \Log::info("Message blocked by free user interaction rules", [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'sender_tier' => $this->tierService->getUserTier($sender),
                'receiver_tier' => $this->tierService->getUserTier($receiver),
                'reason' => $freeUserCheck['reason']
            ]);
            
            return response()->json([
                'error' => $freeUserCheck['message'],
                'upgrade_url' => $freeUserCheck['upgrade_url'],
                'upgrade_prompt' => true
            ], 403);
        }
        
        try {
            $message = Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => $request->content,
                'is_read' => false
            ]);

            // Record messaging activity
            $this->tierService->recordActivity($sender, 'messages_sent');
            
            // Send notification to receiver
            $receiver->notify(new NewMessage($sender, $message));
            
            \Log::info("Message sent successfully", [
                'message_id' => $message->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'sender_tier' => $this->tierService->getUserTier($sender),
                'message_length' => strlen($request->content)
            ]);
            
            return back()->with('success', 'Message sent successfully.');
            
        } catch (\Exception $e) {
            \Log::error("Message sending failed", [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}
