<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     * Returns a list of users the authenticated user has conversations with,
     * along with the last message and unread count.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all unique users the authenticated user has conversations with
        // Get all messages where user is sender or recipient
        $allMessages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id);
        })->get();

        // Extract unique partner IDs
        $partnerIds = $allMessages->map(function ($message) use ($user) {
            return $message->sender_id == $user->id
                ? $message->recipient_id
                : $message->sender_id;
        })->unique()->values();

        $conversations = [];

        foreach ($partnerIds as $partnerId) {
            $partnerUser = User::find($partnerId);
            if (!$partnerUser) {
                continue;
            }

            // Get last message in this conversation
            $lastMessage = Message::where(function ($query) use ($user, $partnerId) {
                $query->where(function ($q) use ($user, $partnerId) {
                    $q->where('sender_id', $user->id)
                        ->where('recipient_id', $partnerId);
                })->orWhere(function ($q) use ($user, $partnerId) {
                    $q->where('sender_id', $partnerId)
                        ->where('recipient_id', $user->id);
                });
            })
                ->orderBy('created_at', 'desc')
                ->first();

            // Count unread messages
            $unreadCount = Message::where('sender_id', $partnerId)
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => [
                    'id' => $partnerUser->id,
                    'first_name' => $partnerUser->first_name,
                    'last_name' => $partnerUser->last_name,
                    'personal_photo' => $partnerUser->personal_photo
                        ? Storage::url($partnerUser->personal_photo)
                        : null,
                    'role' => $partnerUser->role,
                ],
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'message_text' => $lastMessage->message_text,
                    'sender_id' => $lastMessage->sender_id,
                    'is_read' => $lastMessage->is_read,
                    'created_at' => $lastMessage->created_at->toISOString(),
                ] : null,
                'unread_count' => $unreadCount,
                'last_message_at' => $lastMessage ? $lastMessage->created_at->toISOString() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $conversations,
            ],
        ], 200);
    }

    /**
     * Get conversation with a specific user.
     * Returns all messages between the authenticated user and the specified user.
     *
     * @param int $user_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $user_id, Request $request): JsonResponse
    {
        $user = $request->user();

        // Verify the other user exists
        $otherUser = User::find($user_id);
        if (!$otherUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Prevent messaging yourself
        if ($user_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message yourself',
            ], 400);
        }

        // Get all messages between the two users
        $messages = Message::where(function ($query) use ($user, $user_id) {
            $query->where(function ($q) use ($user, $user_id) {
                $q->where('sender_id', $user->id)
                    ->where('recipient_id', $user_id);
            })->orWhere(function ($q) use ($user, $user_id) {
                $q->where('sender_id', $user_id)
                    ->where('recipient_id', $user->id);
            });
        })
            ->with(['sender:id,first_name,last_name,personal_photo', 'recipient:id,first_name,last_name,personal_photo'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read (messages sent to the authenticated user)
        Message::where('sender_id', $user_id)
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $formattedMessages = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'sender' => [
                    'id' => $message->sender->id,
                    'first_name' => $message->sender->first_name,
                    'last_name' => $message->sender->last_name,
                    'personal_photo' => $message->sender->personal_photo
                        ? Storage::url($message->sender->personal_photo)
                        : null,
                ],
                'recipient' => [
                    'id' => $message->recipient->id,
                    'first_name' => $message->recipient->first_name,
                    'last_name' => $message->recipient->last_name,
                    'personal_photo' => $message->recipient->personal_photo
                        ? Storage::url($message->recipient->personal_photo)
                        : null,
                ],
                'message_text' => $message->message_text,
                'attachment_path' => $message->attachment_path
                    ? Storage::url($message->attachment_path)
                    : null,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_with' => [
                    'id' => $otherUser->id,
                    'first_name' => $otherUser->first_name,
                    'last_name' => $otherUser->last_name,
                    'personal_photo' => $otherUser->personal_photo
                        ? Storage::url($otherUser->personal_photo)
                        : null,
                    'role' => $otherUser->role,
                ],
                'messages' => $formattedMessages,
            ],
        ], 200);
    }

    /**
     * Upload attachment file.
     * This is kept as HTTP endpoint because file uploads are easier with multipart/form-data.
     * After upload, client sends the file path via WebSocket message.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'attachment' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        $user = $request->user();

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('messages/attachments', 'public');

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully',
                'data' => [
                    'attachment_path' => Storage::url($attachmentPath),
                    'storage_path' => $attachmentPath, // For use in WebSocket message
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No attachment provided',
        ], 400);
    }
}
