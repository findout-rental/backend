<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $serviceAccountPath = config('services.fcm.service_account_path');
        
        if (!file_exists($serviceAccountPath)) {
            Log::error('FCM Service Account file not found', ['path' => $serviceAccountPath]);
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize FCM', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send FCM push notification for a new message.
     *
     * @param User $recipient
     * @param Message $message
     * @param bool $skipIfInChat Whether to skip if user is actively viewing the conversation
     * @return array
     */
    public function sendMessageNotification(User $recipient, Message $message, bool $skipIfInChat = false): array
    {
        // Skip if user is in chat (they'll receive via WebSocket)
        if ($skipIfInChat) {
            return [
                'success' => true,
                'skipped' => true,
                'reason' => 'User is actively viewing conversation',
            ];
        }

        // Check if recipient has FCM token
        if (empty($recipient->fcm_token)) {
            Log::info('FCM token not available for user', ['user_id' => $recipient->id]);
            return [
                'success' => false,
                'message' => 'FCM token not available',
            ];
        }

        // Check if messaging is initialized
        if (!$this->messaging) {
            return [
                'success' => false,
                'message' => 'FCM not initialized',
            ];
        }

        try {
            $sender = $message->sender;
            $senderName = $sender->first_name . ' ' . $sender->last_name;
            
            // Truncate message preview (max 100 chars)
            $messagePreview = mb_substr($message->message_text, 0, 100);
            if (mb_strlen($message->message_text) > 100) {
                $messagePreview .= '...';
            }

            // Determine notification content based on user's language preference
            $language = $recipient->language_preference ?? 'en';
            
            if ($language === 'ar') {
                $title = 'رسالة جديدة';
                $body = $senderName . ': ' . $messagePreview;
            } else {
                $title = 'New Message';
                $body = $senderName . ': ' . $messagePreview;
            }

            $notification = Notification::create($title, $body);

            $cloudMessage = CloudMessage::withTarget('token', $recipient->fcm_token)
                ->withNotification($notification)
                ->withData([
                    'type' => 'new_message',
                    'message_id' => (string) $message->id,
                    'sender_id' => (string) $message->sender_id,
                    'sender_name' => $senderName,
                    'conversation_id' => (string) min($message->sender_id, $message->recipient_id) . '_' . max($message->sender_id, $message->recipient_id),
                ]);

            $result = $this->messaging->send($cloudMessage);

            Log::info('FCM notification sent successfully', [
                'user_id' => $recipient->id,
                'message_id' => $message->id,
                'fcm_result' => $result,
            ]);

            return [
                'success' => true,
                'message' => 'FCM notification sent',
                'result' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'user_id' => $recipient->id,
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send FCM notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if user is actively viewing a conversation (presence check).
     * This is a simple implementation - in production, you'd use Redis presence channels.
     *
     * @param int $userId
     * @param int $conversationPartnerId
     * @return bool
     */
    public function isUserViewingConversation(int $userId, int $conversationPartnerId): bool
    {
        // TODO: Implement Redis presence channel check
        // For now, return false (always send FCM)
        // In production, check if user is subscribed to: user.{userId} or conversation.{userId}.{partnerId}
        return false;
    }
}

