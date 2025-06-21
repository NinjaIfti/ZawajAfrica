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
        
        // For demo purposes, if there are no conversations, add some dummy data
        if ($formattedConversations->isEmpty()) {
            $formattedConversations = collect([
                [
                    'id' => 1,
                    'name' => 'Fatenn Saeed',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'Hi, I am here to discuss some...',
                    'last_message_time' => '12:25',
                    'unread_count' => 3,
                    'is_online' => true
                ],
                [
                    'id' => 2,
                    'name' => 'Salwa Al-Qwaiz',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'Assalamu alaikum.',
                    'last_message_time' => '12:25',
                    'unread_count' => 3,
                    'is_online' => false
                ],
                [
                    'id' => 3,
                    'name' => 'Amima Kaleb',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'Okay we\'ll have a meetup.',
                    'last_message_time' => '12:25',
                    'unread_count' => 3,
                    'is_online' => true
                ],
                [
                    'id' => 4,
                    'name' => 'Hanan Hablas',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'Okay we\'ll have a meetup.',
                    'last_message_time' => '12:25',
                    'unread_count' => 0,
                    'is_online' => false
                ],
                [
                    'id' => 5,
                    'name' => 'Emma Wilson',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'Okay we\'ll have a meetup.',
                    'last_message_time' => '12:25',
                    'unread_count' => 0,
                    'is_online' => false
                ],
                [
                    'id' => 6,
                    'name' => 'John Alex',
                    'profile_photo' => '/images/placeholder.jpg',
                    'last_message' => 'How are you doing today?',
                    'last_message_time' => '12:25',
                    'unread_count' => 0,
                    'is_online' => false
                ]
            ]);
        }
        
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
        
        // For demo purposes, if there are no messages, add some dummy data
        if ($messages->isEmpty()) {
            $messages = collect([
                [
                    'id' => 1,
                    'content' => 'Hi there! How are you doing?',
                    'time' => '12:20',
                    'is_mine' => false,
                    'is_read' => true
                ],
                [
                    'id' => 2,
                    'content' => 'I\'m doing great, thanks for asking! How about you?',
                    'time' => '12:22',
                    'is_mine' => true,
                    'is_read' => true
                ],
                [
                    'id' => 3,
                    'content' => 'I\'m good too. Would you like to meet up sometime?',
                    'time' => '12:25',
                    'is_mine' => false,
                    'is_read' => true
                ]
            ]);
        }
        
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
            return response()->json([
                'error' => $freeUserCheck['message'],
                'upgrade_url' => $freeUserCheck['upgrade_url'],
                'upgrade_prompt' => true
            ], 403);
        }
        
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
        
        return back()->with('success', 'Message sent successfully.');
    }
}
