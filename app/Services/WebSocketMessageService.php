<?php

namespace App\Services;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WebSocketMessageService
{
    protected FCMNotificationService $fcmService;
    protected NotificationService $notificationService;

    public function __construct(
        FCMNotificationService $fcmService,
        NotificationService $notificationService
    ) {
        $this->fcmService = $fcmService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle incoming WebSocket message from client.
     *
     * @param array $data Message data from WebSocket
     * @param User $user Authenticated user sending the message
     * @return array Response data
     */
    public function handleMessage(array $data, User $user): array
    {
        $type = $data['type'] ?? null;

        return match ($type) {
            'send_message' => $this->handleSendMessage($data, $user),
            'mark_read' => $this->handleMarkRead($data, $user),
            'typing' => $this->handleTyping($data, $user),
            'stop_typing' => $this->handleStopTyping($data, $user),
            default => [
                'success' => false,
                'message' => 'Unknown message type',
                'type' => 'error',
            ],
        };
    }

    /**
     * Handle send_message type.
     */
    protected function handleSendMessage(array $data, User $user): array
    {
        $validator = Validator::make($data, [
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'message_text' => ['required', 'string', 'max:2000'],
            'attachment_path' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'type' => 'error',
            ];
        }

        $recipientId = $data['recipient_id'];

        // Prevent messaging yourself
        if ($recipientId == $user->id) {
            return [
                'success' => false,
                'message' => 'You cannot message yourself',
                'type' => 'error',
            ];
        }

        // Verify recipient exists
        $recipient = User::find($recipientId);
        if (!$recipient) {
            return [
                'success' => false,
                'message' => 'Recipient not found',
                'type' => 'error',
            ];
        }

        // Create message
        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'message_text' => $data['message_text'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'is_read' => false,
        ]);

        $message->load(['sender:id,first_name,last_name,personal_photo', 'recipient:id,first_name,last_name,personal_photo']);

        // Create DB notification
        $this->notificationService->createMessageNotification($recipient, $message);

        // Broadcast WebSocket event to recipient (real-time delivery)
        broadcast(new MessageSent($message))->toOthers();

        // Check if recipient is viewing conversation
        $isInChat = $this->fcmService->isUserViewingConversation($recipientId, $user->id);

        // Send FCM push notification (only if not in chat)
        $this->fcmService->sendMessageNotification($recipient, $message, $isInChat);

        // Return success response to sender
        return [
            'success' => true,
            'message' => 'Message sent successfully',
            'type' => 'message_sent',
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'message_text' => $message->message_text,
                    'attachment_path' => $message->attachment_path
                        ? Storage::url($message->attachment_path)
                        : null,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toISOString(),
                ],
            ],
        ];
    }

    /**
     * Handle mark_read type.
     */
    protected function handleMarkRead(array $data, User $user): array
    {
        $validator = Validator::make($data, [
            'message_id' => ['required', 'integer', 'exists:messages,id'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'type' => 'error',
            ];
        }

        $message = Message::where('id', $data['message_id'])
            ->where('recipient_id', $user->id)
            ->first();

        if (!$message) {
            return [
                'success' => false,
                'message' => 'Message not found or you do not have permission to mark it as read',
                'type' => 'error',
            ];
        }

        if ($message->is_read) {
            return [
                'success' => false,
                'message' => 'Message is already marked as read',
                'type' => 'error',
            ];
        }

        $message->update(['is_read' => true]);

        // Broadcast read status to sender (so they see ✓✓ Read)
        broadcast(new MessageRead($message));

        return [
            'success' => true,
            'message' => 'Message marked as read',
            'type' => 'message_read',
            'data' => [
                'message_id' => $message->id,
                'is_read' => true,
            ],
        ];
    }

    /**
     * Handle typing indicator.
     */
    protected function handleTyping(array $data, User $user): array
    {
        $validator = Validator::make($data, [
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'type' => 'error',
            ];
        }

        // Broadcast typing indicator to recipient
        broadcast(new \App\Events\UserTyping($user->id, $data['recipient_id'], true))
            ->toOthers();

        return [
            'success' => true,
            'type' => 'typing_sent',
        ];
    }

    /**
     * Handle stop typing indicator.
     */
    protected function handleStopTyping(array $data, User $user): array
    {
        $validator = Validator::make($data, [
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'type' => 'error',
            ];
        }

        // Broadcast stop typing to recipient
        broadcast(new \App\Events\UserTyping($user->id, $data['recipient_id'], false))
            ->toOthers();

        return [
            'success' => true,
            'type' => 'typing_stopped',
        ];
    }
}

