<?php

namespace App\Http\Controllers\ProfileMenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{User, Chat, Message, MessageStatus};
use Carbon\Carbon;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', null);
        $authUserId = $request->user()->id;

        // ✅ Step 1: Fetch chats with last message
        $chats = Chat::with(['messages' => function ($q) {
            $q->latest('time')->limit(1);
        }])
            ->whereJsonContains('chat_users', [$authUserId])
            ->get();

        // ✅ Step 2: Format chats into user data
        $chatUsers = $chats->map(function ($chat) use ($authUserId) {
            $otherUserId = collect(json_decode($chat->chat_users))
                ->reject(fn($id) => $id == $authUserId)
                ->first();

            $otherUser = User::find($otherUserId);

            if (!$otherUser) {
                return null;
            }

            $lastMessage = $chat->messages->first();
            $unseenCount = $this->chatNotSeenCount($authUserId, $otherUserId);

            $avatar = $otherUser->avatar
                ? asset("avatars/{$otherUser->avatar}")
                : asset("avatars/avatar-" . rand(1, 10) . ".jpg");

            return [
                'id'              => $otherUser->id,
                'image'           => $avatar,
                'name'            => $otherUser->name,
                'unseen'          => $unseenCount,
                'lastMessageTime' => $lastMessage?->time,
            ];
        })->filter()->values();

        // ✅ Step 3: If searching, include matching users even without chats
        if ($search) {
            $extraUsers = User::where('id', '!=', $authUserId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->get()
                ->map(function ($user) {
                    $avatar = $user->avatar
                        ? asset("storage/avatars/{$user->avatar}")
                        : asset("storage/avatars/avatar-" . rand(1, 10) . ".jpg");

                    return [
                        'id'              => $user->id,
                        'image'           => $avatar,
                        'name'            => $user->name,
                        'unseen'          => 0,
                        'lastMessageTime' => null,
                    ];
                });

            // ✅ Only add users not already in chats
            $chatUsersIds = $chatUsers->pluck('id')->toArray();
            $extraUsers = $extraUsers->reject(fn($user) => in_array($user['id'], $chatUsersIds));

            // Use concat instead of merge (since items are arrays, not models)
            $chatUsers = $chatUsers->concat($extraUsers)->values();
        }

        // ✅ Step 4: Sort by last message time (newest first)
        $chatUsers = $chatUsers->sortByDesc(function ($user) {
            return $user['lastMessageTime'] ?? now()->subYears(10);
        })->values();

        return response()->json($chatUsers);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'message' => 'required|string',
            'senderId' => 'required|integer',
            'receiverId' => 'required|integer',
            'msgStatus' => 'required|array',
            'msgStatus.isSent' => 'boolean',
            'msgStatus.isDelivered' => 'boolean',
            'msgStatus.isSeen' => 'boolean',
        ]);

        $receiverId = $validatedData['receiverId'];
        $senderId = $validatedData['senderId'];

        DB::beginTransaction();
        try {
            $chat = Chat::whereJsonContains('chat_users', [$receiverId])
                ->whereJsonContains('chat_users', [$senderId])
                ->first();

            if (!$chat) {
                $chat = Chat::create([
                    'user_id' => $receiverId,
                    'chat_users' => json_encode([$senderId, $receiverId]),
                    'unseen_msgs' => 0,
                ]);
            }

            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $senderId,
                'message' => $validatedData['message'],
                'time' => now(),
            ]);

            MessageStatus::create([
                'message_id' => $message->id,
                'is_sent' => $validatedData['msgStatus']['isSent'],
                'is_delivered' => $validatedData['msgStatus']['isDelivered'],
                'is_seen' => $validatedData['msgStatus']['isSeen'],
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully.',
                'chatId' => $chat->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to send message.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, string $otherUserId)
    {
        $accountActive = $request->user()->id;
        $otherUserId = (int) $otherUserId;

        $this->chatSeen($accountActive, $otherUserId);

        $chat = Chat::with(['messages.sender', 'messages.messageStatus'])
            ->whereJsonContains('chat_users', [$accountActive])
            ->whereJsonContains('chat_users', [$otherUserId])
            ->first();

        if (!$chat) {
            return response()->json([], 200);
        }

        $unseenMsgCount = $chat->messages->where('sender_id', $otherUserId)
            ->filter(fn($msg) => !$msg->messageStatus->is_seen)
            ->count();

        return response()->json([
            'id' => $chat->id,
            'userId' => $otherUserId,
            'unseenMsgs' => $unseenMsgCount,
            'chat' => $chat->messages->map(function ($message) {
                return [
                    'message' => $message->message,
                    'time' => Carbon::parse($message->time)->diffForHumans(),
                    'senderId' => $message->sender_id,
                    'msgStatus' => [
                        'isSent' => $message->messageStatus->is_sent ?? false,
                        'isDelivered' => $message->messageStatus->is_delivered ?? false,
                        'isSeen' => $message->messageStatus->is_seen ?? false,
                    ],
                ];
            }),
        ]);
    }

    private function chatSeen($accountActive, $otherUserId)
    {
        $chat = Chat::with(['messages.messageStatus'])
            ->whereJsonContains('chat_users', [$accountActive])
            ->whereJsonContains('chat_users', [$otherUserId])
            ->first();

        if ($chat) {
            $messages = $chat->messages->where('sender_id', $otherUserId);

            foreach ($messages as $message) {
                MessageStatus::where('message_id', $message->id)->update([
                    'is_seen' => true,
                    'is_delivered' => true,
                ]);
            }
        }
    }

    /**
     * Count unseen messages from one user to another
     */
    private function chatNotSeenCount($accountActive, $otherUserId)
    {
        $chat = Chat::with(['messages.messageStatus'])
            ->whereJsonContains('chat_users', [$accountActive])
            ->whereJsonContains('chat_users', [$otherUserId])
            ->first();

        if (!$chat) {
            return 0;
        }

        return $chat->messages->where('sender_id', $otherUserId)
            ->filter(fn($msg) => !$msg->messageStatus->is_seen)
            ->count();
    }

    public function destroy(Request $request, string $otherUserId)
    {
        $accountActive = $request->user()->id;
        $otherUserId = (int) $otherUserId;

        DB::beginTransaction();
        try {
            // Find chat between both users
            $chat = Chat::whereJsonContains('chat_users', [$accountActive])
                ->whereJsonContains('chat_users', [$otherUserId])
                ->first();

            if (!$chat) {
                return response()->json([
                    'status' => false,
                    'message' => 'No conversation found.',
                ], 404);
            }

            // Delete messages + statuses
            MessageStatus::whereIn('message_id', $chat->messages->pluck('id'))->delete();
            Message::where('chat_id', $chat->id)->delete();

            // Delete chat
            $chat->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Conversation deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete conversation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
