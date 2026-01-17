<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $fcmService;

    public function __construct(FCMNotificationService $fcmService = null)
    {
        $this->fcmService = $fcmService ?? app(FCMNotificationService::class);
    }

    /**
     * Create a notification in the database.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @param bool $sendPush Whether to send FCM push notification (default: true)
     * @return Notification
     */
    public function create(User $user, string $type, array $data = [], bool $sendPush = true): Notification
    {
        // Get language preference
        $language = $user->language_preference ?? 'en';

        // Generate title and message based on type and language
        $content = $this->getNotificationContent($type, $language, $data);

        $notification = Notification::create([
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

        // Send FCM push notification if enabled and user has FCM token
        if ($sendPush && !empty($user->fcm_token)) {
            try {
                $title = $language === 'ar' ? $content['title_ar'] : $content['title'];
                $body = $language === 'ar' ? $content['message_ar'] : $content['message'];
                
                $pushData = [];
                if (isset($data['booking_id'])) {
                    $pushData['booking_id'] = (string) $data['booking_id'];
                }
                if (isset($data['message_id'])) {
                    $pushData['message_id'] = (string) $data['message_id'];
                }

                $this->fcmService->sendNotification($user, $type, $title, $body, $pushData);
            } catch (\Exception $e) {
                // Log error but don't fail the notification creation
                Log::error('Failed to send FCM push notification', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Create a notification for a new message.
     * Note: FCM push notification is handled separately by WebSocketMessageService.
     *
     * @param User $recipient
     * @param Message $message
     * @return Notification
     */
    public function createMessageNotification(User $recipient, Message $message): Notification
    {
        // Don't send FCM push here - WebSocketMessageService handles it separately
        return $this->create($recipient, 'new_message', [
            'message_id' => $message->id,
            'sender' => $message->sender,
            'message_text' => $message->message_text,
        ], false); // sendPush = false
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

            case 'account_rejected':
                $content['title'] = 'Account Rejected';
                $content['title_ar'] = 'تم رفض الحساب';
                $content['message'] = 'Your account registration has been rejected. Please contact support for more information.';
                $content['message_ar'] = 'تم رفض تسجيل حسابك. يرجى الاتصال بالدعم لمزيد من المعلومات.';
                break;

            case 'booking_cancelled':
                $content['title'] = 'Booking Cancelled';
                $content['title_ar'] = 'تم إلغاء الحجز';
                $content['message'] = 'A booking has been cancelled by the tenant.';
                $content['message_ar'] = 'تم إلغاء الحجز من قبل المستأجر.';
                break;

            case 'booking_modified':
                $content['title'] = 'Booking Modification Request';
                $content['title_ar'] = 'طلب تعديل الحجز';
                $content['message'] = 'The tenant has requested to modify their booking.';
                $content['message_ar'] = 'طلب المستأجر تعديل الحجز.';
                break;

            case 'modification_approved':
                $content['title'] = 'Modification Approved';
                $content['title_ar'] = 'تمت الموافقة على التعديل';
                $content['message'] = 'Your booking modification request has been approved by the owner.';
                $content['message_ar'] = 'تمت الموافقة على طلب تعديل الحجز من قبل المالك.';
                break;

            case 'modification_rejected':
                $content['title'] = 'Modification Rejected';
                $content['title_ar'] = 'تم رفض التعديل';
                $content['message'] = 'Your booking modification request has been rejected by the owner.';
                $content['message_ar'] = 'تم رفض طلب تعديل الحجز من قبل المالك.';
                break;

            case 'new_review':
                $apartmentTitle = $data['apartment_title'] ?? 'your apartment';
                $content['title'] = 'New Review Received';
                $content['title_ar'] = 'تم استلام تقييم جديد';
                $content['message'] = 'You have received a new review for ' . $apartmentTitle . '.';
                $content['message_ar'] = 'لقد تلقيت تقييماً جديداً لـ ' . $apartmentTitle . '.';
                break;

            case 'new_user_registration':
                $userName = $data['user_name'] ?? 'A new user';
                $userRole = $data['user_role'] ?? 'user';
                $content['title'] = 'New User Registration';
                $content['title_ar'] = 'تسجيل مستخدم جديد';
                $content['message'] = $userName . ' (' . ucfirst($userRole) . ') has registered and is pending approval.';
                $content['message_ar'] = $userName . ' (' . ($userRole === 'tenant' ? 'مستأجر' : ($userRole === 'owner' ? 'مالك' : 'مستخدم')) . ') قام بالتسجيل وهو في انتظار الموافقة.';
                break;

            case 'new_apartment':
                $apartmentAddress = $data['apartment_address'] ?? 'a new apartment';
                $ownerName = $data['owner_name'] ?? 'An owner';
                $content['title'] = 'New Apartment Published';
                $content['title_ar'] = 'شقة جديدة تم نشرها';
                $content['message'] = 'A new apartment at ' . $apartmentAddress . ' has been published by ' . $ownerName . '.';
                $content['message_ar'] = 'تم نشر شقة جديدة في ' . $apartmentAddress . ' بواسطة ' . $ownerName . '.';
                break;

            case 'new_booking':
                $tenantName = $data['tenant_name'] ?? 'A tenant';
                $apartmentAddress = $data['apartment_address'] ?? 'an apartment';
                $content['title'] = 'New Booking Request';
                $content['title_ar'] = 'طلب حجز جديد';
                $content['message'] = $tenantName . ' has submitted a new booking request for ' . $apartmentAddress . '.';
                $content['message_ar'] = $tenantName . ' قدم طلب حجز جديد لـ ' . $apartmentAddress . '.';
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
