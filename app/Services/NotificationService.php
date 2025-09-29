<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send email notification.
     */
    public function sendEmail(
        string $email,
        string $subject,
        string $message,
        string $template = 'default',
        array $data = []
    ): bool {
        try {
            // For now, we'll create a notification record
            // In a real implementation, you would integrate with a mail service
            if (class_exists(Notification::class)) {
                Notification::create([
                    'type' => 'email',
                    'recipient' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'template' => $template,
                    'data' => $data,
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                ]);
            }

            Log::info('Email notification sent', [
                'email' => $email,
                'subject' => $subject,
                'template' => $template,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS notification.
     */
    public function sendSMS(string $phone, string $message): bool
    {
        try {
            // For now, we'll create a notification record
            // In a real implementation, you would integrate with an SMS service like Twilio
            if (class_exists(Notification::class)) {
                Notification::create([
                    'type' => 'sms',
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                ]);
            }

            Log::info('SMS notification sent', [
                'phone' => $phone,
                'message' => substr($message, 0, 50) . '...',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send push notification.
     */
    public function sendPush(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        try {
            // For now, we'll create a notification record
            // In a real implementation, you would integrate with Firebase or similar
            if (class_exists(Notification::class)) {
                Notification::create([
                    'type' => 'push',
                    'recipient' => $deviceToken,
                    'subject' => $title,
                    'message' => $body,
                    'data' => $data,
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                ]);
            }

            Log::info('Push notification sent', [
                'device_token' => substr($deviceToken, 0, 10) . '...',
                'title' => $title,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'device_token' => substr($deviceToken, 0, 10) . '...',
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to user.
     */
    public function notifyUser($user, string $subject, string $message, string $type = 'email'): bool
    {
        switch ($type) {
            case 'email':
                return $this->sendEmail($user->email, $subject, $message);
            case 'sms':
                if ($user->phone) {
                    return $this->sendSMS($user->phone, $message);
                }
                break;
            case 'both':
                $emailSent = $this->sendEmail($user->email, $subject, $message);
                $smsSent = $user->phone ? $this->sendSMS($user->phone, $message) : false;
                return $emailSent || $smsSent;
        }

        return false;
    }

    /**
     * Get notification templates.
     */
    public function getTemplates(): array
    {
        return [
            'customer-welcome' => [
                'subject' => 'Welcome to Flags Across Our Community!',
                'template' => 'emails.customer.welcome',
            ],
            'subscription-created' => [
                'subject' => 'Subscription Created Successfully',
                'template' => 'emails.subscription.created',
            ],
            'subscription-canceled' => [
                'subject' => 'Subscription Canceled',
                'template' => 'emails.subscription.canceled',
            ],
            'subscription-reactivated' => [
                'subject' => 'Subscription Reactivated',
                'template' => 'emails.subscription.reactivated',
            ],
            'flag-placement-reminder' => [
                'subject' => 'Flag Placement Scheduled',
                'template' => 'emails.placement.reminder',
            ],
            'flag-placement-completed' => [
                'subject' => 'Flag Placement Completed',
                'template' => 'emails.placement.completed',
            ],
            'potential-customer-contact' => [
                'subject' => 'Service Now Available in Your Area!',
                'template' => 'emails.potential-customer.contact',
            ],
            'route-assignment' => [
                'subject' => 'New Route Assignment',
                'template' => 'emails.route.assignment',
            ],
            'route-started' => [
                'subject' => 'Route Started',
                'template' => 'emails.route.started',
            ],
            'bulk-notification' => [
                'subject' => 'Important Update from Flags Across Our Community',
                'template' => 'emails.bulk.notification',
            ],
            'admin-notification' => [
                'subject' => 'Admin Notification',
                'template' => 'emails.admin.notification',
            ],
        ];
    }

    /**
     * Schedule notification for later delivery.
     */
    public function scheduleNotification(
        string $type,
        string $recipient,
        string $subject,
        string $message,
        Carbon $scheduledAt,
        array $data = []
    ) {
        if (!class_exists(Notification::class)) {
            return null;
        }

        return Notification::create([
            'type' => $type,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'data' => $data,
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Process scheduled notifications.
     */
    public function processScheduledNotifications(): int
    {
        if (!class_exists(Notification::class)) {
            return 0;
        }

        $notifications = Notification::where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        $processed = 0;

        foreach ($notifications as $notification) {
            try {
                $success = false;

                switch ($notification->type) {
                    case 'email':
                        $success = $this->sendEmail(
                            $notification->recipient,
                            $notification->subject,
                            $notification->message,
                            $notification->template ?? 'default',
                            $notification->data ?? []
                        );
                        break;
                    case 'sms':
                        $success = $this->sendSMS(
                            $notification->recipient,
                            $notification->message
                        );
                        break;
                }

                if ($success) {
                    $notification->update([
                        'status' => 'sent',
                        'sent_at' => Carbon::now(),
                    ]);
                    $processed++;
                } else {
                    $notification->update(['status' => 'failed']);
                }
            } catch (\Exception $e) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                Log::error('Failed to process scheduled notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    /**
     * Get notification statistics.
     */
    public function getStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        if (!class_exists(Notification::class)) {
            return [
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
                'scheduled' => 0,
                'by_type' => [],
            ];
        }

        $query = Notification::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'scheduled' => $query->where('status', 'scheduled')->count(),
            'by_type' => $query->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }
}