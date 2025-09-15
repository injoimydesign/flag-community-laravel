<?php
// app/Models/FlagPlacement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlagPlacement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'subscription_id',
        'holiday_id',
        'flag_product_id',
        'placement_date',
        'removal_date',
        'status',
        'placed_by_user_id',
        'removed_by_user_id',
        'placed_at',
        'removed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'placement_date' => 'date',
        'removal_date' => 'date',
        'placed_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the subscription for this placement.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the holiday for this placement.
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Get the flag product for this placement.
     */
    public function flagProduct()
    {
        return $this->belongsTo(FlagProduct::class);
    }

    /**
     * Get the user who placed the flag.
     */
    public function placedByUser()
    {
        return $this->belongsTo(User::class, 'placed_by_user_id');
    }

    /**
     * Get the user who removed the flag.
     */
    public function removedByUser()
    {
        return $this->belongsTo(User::class, 'removed_by_user_id');
    }

    /**
     * Get the customer for this placement.
     */
    public function customer()
    {
        return $this->hasOneThrough(User::class, Subscription::class, 'id', 'id', 'subscription_id', 'user_id');
    }

    // Scopes

    /**
     * Scope to get placements by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get scheduled placements.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get placed flags.
     */
    public function scopePlaced($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope to get removed flags.
     */
    public function scopeRemoved($query)
    {
        return $query->where('status', 'removed');
    }

    /**
     * Scope to get placements for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('placement_date', $date);
    }

    /**
     * Scope to get placements between dates.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('placement_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get placements due for placement today.
     */
    public function scopeDueForPlacementToday($query)
    {
        return $query->scheduled()
            ->where('placement_date', Carbon::today());
    }

    /**
     * Scope to get placements due for removal today.
     */
    public function scopeDueForRemovalToday($query)
    {
        return $query->placed()
            ->where('removal_date', Carbon::today());
    }

    /**
     * Scope to get overdue placements.
     */
    public function scopeOverduePlacement($query)
    {
        return $query->scheduled()
            ->where('placement_date', '<', Carbon::today());
    }

    /**
     * Scope to get overdue removals.
     */
    public function scopeOverdueRemoval($query)
    {
        return $query->placed()
            ->where('removal_date', '<', Carbon::today());
    }

    // Helper methods

    /**
     * Check if placement is scheduled.
     */
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if flag is currently placed.
     */
    public function isPlaced()
    {
        return $this->status === 'placed';
    }

    /**
     * Check if flag has been removed.
     */
    public function isRemoved()
    {
        return $this->status === 'removed';
    }

    /**
     * Check if placement was skipped.
     */
    public function isSkipped()
    {
        return $this->status === 'skipped';
    }

    /**
     * Check if placement is overdue for placement.
     */
    public function isOverduePlacement()
    {
        return $this->isScheduled() && $this->placement_date < Carbon::today();
    }

    /**
     * Check if placement is overdue for removal.
     */
    public function isOverdueRemoval()
    {
        return $this->isPlaced() && $this->removal_date < Carbon::today();
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Get status with color class for UI.
     */
    public function getStatusColorAttribute()
    {
        return [
            'scheduled' => 'text-blue-600',
            'placed' => 'text-green-600',
            'removed' => 'text-gray-600',
            'skipped' => 'text-yellow-600',
        ][$this->status] ?? 'text-gray-600';
    }

    /**
     * Mark flag as placed.
     */
    public function markAsPlaced($userId = null, $notes = null)
    {
        $this->status = 'placed';
        $this->placed_by_user_id = $userId;
        $this->placed_at = Carbon::now();
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();

        // Update inventory
        $this->flagProduct->adjustInventory(-1, 'Flag placed for placement #' . $this->id);

        // Send notification to customer
        $this->sendPlacementNotification();
    }

    /**
     * Mark flag as removed.
     */
    public function markAsRemoved($userId = null, $notes = null)
    {
        $this->status = 'removed';
        $this->removed_by_user_id = $userId;
        $this->removed_at = Carbon::now();
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . $notes;
        }
        
        $this->save();

        // Update inventory
        $this->flagProduct->adjustInventory(1, 'Flag removed from placement #' . $this->id);

        // Send notification to customer
        $this->sendRemovalNotification();
    }

    /**
     * Mark placement as skipped.
     */
    public function markAsSkipped($reason = null)
    {
        $this->status = 'skipped';
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . 'Skipped: ' . $reason;
        }
        
        $this->save();
    }

    /**
     * Send placement notification to customer.
     */
    private function sendPlacementNotification()
    {
        $customer = $this->subscription->user;
        $subject = "Your {$this->holiday->name} flag has been placed!";
        $message = "We've placed your {$this->flagProduct->display_name} for {$this->holiday->name}. It will be removed on {$this->removal_date->format('F j, Y')}.";

        Notification::create([
            'user_id' => $customer->id,
            'type' => 'email',
            'subject' => $subject,
            'message' => $message,
            'metadata' => [
                'placement_id' => $this->id,
                'type' => 'flag_placed'
            ]
        ]);
    }

    /**
     * Send removal notification to customer.
     */
    private function sendRemovalNotification()
    {
        $customer = $this->subscription->user;
        $subject = "Your {$this->holiday->name} flag has been removed";
        $message = "We've removed your {$this->flagProduct->display_name} from {$this->holiday->name}. Thank you for displaying our flag!";

        Notification::create([
            'user_id' => $customer->id,
            'type' => 'email',
            'subject' => $subject,
            'message' => $message,
            'metadata' => [
                'placement_id' => $this->id,
                'type' => 'flag_removed'
            ]
        ]);
    }

    /**
     * Get days until placement date.
     */
    public function getDaysUntilPlacementAttribute()
    {
        return Carbon::now()->diffInDays($this->placement_date, false);
    }

    /**
     * Get days until removal date.
     */
    public function getDaysUntilRemovalAttribute()
    {
        return Carbon::now()->diffInDays($this->removal_date, false);
    }
}