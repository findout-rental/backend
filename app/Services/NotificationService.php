<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification in the database.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @return Notification
     */
    public function create(User $user, string $type, array $data = []): Notification
    {
        // Get language preference
        $language = $user->language_preference ?? 'en';

        // Generate title and message based on type and language
        $content = $this->getNotificationContent($type, $language, $data);

        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $content['title'],
            'title_ar' => $content['title_ar'],
            'message' => $content['message'],
            'message_ar' => $content['message_ar'],
            'booking_id' => $data['booking_id'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'is_read' => false,
        ]);
    }

    /**
     * Create a notification for a new message.
     *
     * @param User $recipient
     * @param Message $message
     * @return Notification
     */
    public function createMessageNotification(User $recipient, Message $message): Notification
    {
        return $this->create($recipient, 'new_message', [
            'message_id' => $message->id,
            'sender' => $message->sender,
            'message_text' => $message->message_text,
        ]);
    }

    /**
     * Get notification content based on type and language.
     *
     * @param string $type
     * @param string $language
     * @param array $data
     * @return array
     */
    protected function getNotificationContent(string $type, string $language, array $data): array
    {
        $content = [
            'title' => '',
            'title_ar' => '',
            'message' => '',
            'message_ar' => '',
        ];

        switch ($type) {
            case 'new_message':
                $sender = $data['sender'] ?? null;
                $senderName = $sender ? ($sender->first_name . ' ' . $sender->last_name) : 'Someone';
                $messagePreview = mb_substr($data['message_text'] ?? '', 0, 100);

                $content['title'] = 'New Message';
                $content['title_ar'] = 'رسالة جديدة';
                $content['message'] = $senderName . ' sent you a message: ' . $messagePreview;
                $content['message_ar'] = $senderName . ' أرسل لك رسالة: ' . $messagePreview;
                break;

            case 'booking_approved':
                $content['title'] = 'Booking Approved';
                $content['title_ar'] = 'تم الموافقة على الحجز';
                $content['message'] = 'Your booking request has been approved by the owner.';
                $content['message_ar'] = 'تمت الموافقة على طلب الحجز من قبل المالك.';
                break;

            case 'booking_rejected':
                $content['title'] = 'Booking Rejected';
                $content['title_ar'] = 'تم رفض الحجز';
                $content['message'] = 'Your booking request has been rejected by the owner.';
                $content['message_ar'] = 'تم رفض طلب الحجز من قبل المالك.';
                break;

            case 'booking_request_received':
                $content['title'] = 'New Booking Request';
                $content['title_ar'] = 'طلب حجز جديد';
                $content['message'] = 'You have received a new booking request.';
                $content['message_ar'] = 'لقد تلقيت طلب حجز جديد.';
                break;

            case 'account_approved':
                $content['title'] = 'Account Approved';
                $content['title_ar'] = 'تمت الموافقة على الحساب';
                $content['message'] = 'Your account has been approved. You can now use all features.';
                $content['message_ar'] = 'تمت الموافقة على حسابك. يمكنك الآن استخدام جميع الميزات.';
                break;

            default:
                $content['title'] = 'Notification';
                $content['title_ar'] = 'إشعار';
                $content['message'] = 'You have a new notification.';
                $content['message_ar'] = 'لديك إشعار جديد.';
                break;
        }

        return $content;
    }
}
