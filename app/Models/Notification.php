<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'message',
        'status',
        'sent_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    /**
     * Scope to get notifications by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get notifications by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get email notifications.
     */
    public function scopeEmail($query)
    {
        return $query->where('type', 'email');
    }

    /**
     * Scope to get SMS notifications.
     */
    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    // Static methods

    /**
     * Create a flag placement notification.
     */
    public static function createPlacementNotification($userId, $placement)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'email',
            'subject' => "Your {$placement->holiday->name} flag has been placed!",
            'message' => "We've placed your {$placement->flagProduct->display_name} for {$placement->holiday->name}. It will be removed on {$placement->removal_date->format('F j, Y')}.",
            'metadata' => [
                'placement_id' => $placement->id,
                'notification_type' => 'flag_placed',
                'holiday_id' => $placement->holiday_id,
            ]
        ]);
    }

    /**
     * Create a flag removal notification.
     */
    public static function createRemovalNotification($userId, $placement)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'email',
            'subject' => "Your {$placement->holiday->name} flag has been removed",
            'message' => "We've removed your {$placement->flagProduct->display_name} from {$placement->holiday->name}. Thank you for displaying our flag!",
            'metadata' => [
                'placement_id' => $placement->id,
                'notification_type' => 'flag_removed',
                'holiday_id' => $placement->holiday_id,
            ]
        ]);
    }

    /**
     * Create a subscription renewal reminder.
     */
    public static function createRenewalReminder($userId, $subscription)
    {
        $daysUntilExpiry = Carbon::now()->diffInDays($subscription->end_date);
        
        return self::create([
            'user_id' => $userId,
            'type' => 'email',
            'subject' => "Your flag subscription expires in {$daysUntilExpiry} days",
            'message' => "Your annual flag subscription will expire on {$subscription->end_date->format('F j, Y')}. Renew now to continue your flag service for next year!",
            'metadata' => [
                'subscription_id' => $subscription->id,
                'notification_type' => 'renewal_reminder',
                'days_until_expiry' => $daysUntilExpiry,
            ]
        ]);
    }

    /**
     * Create a service area expansion notification.
     */
    public static function createServiceAreaNotification($userId)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'email',
            'subject' => "Great news! Flag service is now available in your area",
            'message' => "We're excited to let you know that Flags Across Our Community is now serving your area! You can now sign up for our flag subscription service.",
            'metadata' => [
                'notification_type' => 'service_area_expansion',
            ]
        ]);
    }

    /**
     * Create a welcome notification for new customers.
     */
    public static function createWelcomeNotification($userId)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'email',
            'subject' => "Welcome to Flags Across Our Community!",
            'message' => "Thank you for joining Flags Across Our Community! We're honored to help you display your patriotism throughout the year. You'll receive notifications before each flag placement.",
            'metadata' => [
                'notification_type' => 'welcome',
            ]
        ]);
    }

    // Helper methods

    /**
     * Check if notification is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if notification was sent.
     */
    public function isSent()
    {
        return $this->status === 'sent';
    }

    /**
     * Check if notification failed.
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent()
    {
        $this->status = 'sent';
        $this->sent_at = Carbon::now();
        $this->save();
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        
        if ($reason) {
            $metadata = $this->metadata ?: [];
            $metadata['failure_reason'] = $reason;
            $this->metadata = $metadata;
        }
        
        $this->save();
    }

    /**
     * Get notification type display name.
     */
    public function getTypeDisplayAttribute()
    {
        return strtoupper($this->type);
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'text-yellow-600',
            'sent' => 'text-green-600',
            'failed' => 'text-red-600',
        ][$this->status] ?? 'text-gray-600';
    }

    /**
     * Get the notification category from metadata.
     */
    public function getCategoryAttribute()
    {
        return $this->metadata['notification_type'] ?? 'general';
    }

    /**
     * Check if this is an email notification.
     */
    public function isEmail()
    {
        return $this->type === 'email';
    }

    /**
     * Check if this is an SMS notification.
     */
    public function isSms()
    {
        return $this->type === 'sms';
    }

    /**
     * Retry sending the notification.
     */
    public function retry()
    {
        if ($this->isFailed()) {
            $this->status = 'pending';
            $this->save();
        }
    }
}