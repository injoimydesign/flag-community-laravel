<?php
// app/Models/Subscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'type',
        'status',
        'total_amount',
        'start_date',
        'end_date',
        'canceled_at',
        'selected_holidays',
        'special_instructions',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'canceled_at' => 'datetime',
        'selected_holidays' => 'array',
    ];

    // Relationships

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription items.
     */
    public function items()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Get the flag placements for this subscription.
     */
    public function flagPlacements()
    {
        return $this->hasMany(FlagPlacement::class);
    }

    /**
     * Get the holidays included in this subscription.
     */
    public function holidays()
    {
        return Holiday::whereIn('id', $this->selected_holidays ?: [])->active()->ordered();
    }

    // Scopes

    /**
     * Scope to get only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get subscriptions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get subscriptions expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->active()
            ->where('end_date', '<=', Carbon::now()->addDays($days))
            ->where('end_date', '>=', Carbon::now());
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now());
    }

    // Helper methods

    /**
     * Check if subscription is active.
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               $this->start_date <= Carbon::now() && 
               $this->end_date >= Carbon::now();
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired()
    {
        return $this->end_date < Carbon::now();
    }

    /**
     * Check if subscription is expiring soon.
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->isActive() && 
               $this->end_date <= Carbon::now()->addDays($days);
    }

    /**
     * Get remaining days in subscription.
     */
    public function getRemainingDaysAttribute()
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get subscription type display name.
     */
    public function getTypeDisplayAttribute()
    {
        return $this->type === 'annual' ? 'Annual Subscription' : 'One-Time Purchase';
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel($reason = null)
    {
        $this->status = 'canceled';
        $this->canceled_at = Carbon::now();
        $this->save();

        // Cancel future flag placements
        $this->flagPlacements()
            ->where('status', 'scheduled')
            ->where('placement_date', '>', Carbon::now())
            ->update(['status' => 'skipped']);

        // Cancel Stripe subscription if exists
        if ($this->stripe_subscription_id && $this->type === 'annual') {
            // Handle Stripe cancellation
            // This would be implemented in a service class
        }

        // Log cancellation reason
        if ($reason) {
            // Create cancellation log entry
        }
    }

    /**
     * Generate flag placements for the subscription.
     */
    public function generateFlagPlacements()
    {
        // Only generate if subscription is active
        if (!$this->isActive()) {
            return;
        }

        $holidays = $this->holidays()->get();
        $year = $this->start_date->year;

        // If subscription spans multiple years, handle both years
        if ($this->end_date->year > $this->start_date->year) {
            $years = [$this->start_date->year, $this->end_date->year];
        } else {
            $years = [$year];
        }

        foreach ($years as $year) {
            foreach ($holidays as $holiday) {
                // Skip if holiday is not active in this year
                if (!$holiday->isActiveInYear($year)) {
                    continue;
                }

                $placementDates = $holiday->getPlacementDatesForYear($year);
                
                // Skip if placement date is before subscription start or after end
                if ($placementDates['placement_date'] < $this->start_date || 
                    $placementDates['placement_date'] > $this->end_date) {
                    continue;
                }

                // Create placement for each subscription item (flag product)
                foreach ($this->items as $item) {
                    // Check if placement already exists
                    $existingPlacement = FlagPlacement::where([
                        'subscription_id' => $this->id,
                        'holiday_id' => $holiday->id,
                        'flag_product_id' => $item->flag_product_id,
                        'placement_date' => $placementDates['placement_date'],
                    ])->first();

                    if (!$existingPlacement) {
                        FlagPlacement::create([
                            'subscription_id' => $this->id,
                            'holiday_id' => $holiday->id,
                            'flag_product_id' => $item->flag_product_id,
                            'placement_date' => $placementDates['placement_date'],
                            'removal_date' => $placementDates['removal_date'],
                            'status' => 'scheduled',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get upcoming flag placements.
     */
    public function getUpcomingPlacements()
    {
        return $this->flagPlacements()
            ->with(['holiday', 'flagProduct.flagType', 'flagProduct.flagSize'])
            ->where('placement_date', '>=', Carbon::now())
            ->where('status', 'scheduled')
            ->orderBy('placement_date')
            ->get();
    }

    /**
     * Get past flag placements.
     */
    public function getPastPlacements()
    {
        return $this->flagPlacements()
            ->with(['holiday', 'flagProduct.flagType', 'flagProduct.flagSize'])
            ->where('placement_date', '<', Carbon::now())
            ->orderBy('placement_date', 'desc')
            ->get();
    }

    /**
     * Calculate renewal date for annual subscriptions.
     */
    public function calculateRenewalDate()
    {
        if ($this->type !== 'annual') {
            return null;
        }

        return $this->end_date->copy()->addYear();
    }

    /**
     * Renew the subscription for another year.
     */
    public function renew()
    {
        if ($this->type !== 'annual') {
            return false;
        }

        $newEndDate = $this->calculateRenewalDate();
        
        // Create new subscription for renewal
        $renewalSubscription = self::create([
            'user_id' => $this->user_id,
            'type' => 'annual',
            'status' => 'pending', // Will be activated after payment
            'total_amount' => $this->total_amount,
            'start_date' => $this->end_date->copy()->addDay(),
            'end_date' => $newEndDate,
            'selected_holidays' => $this->selected_holidays,
            'special_instructions' => $this->special_instructions,
        ]);

        // Copy subscription items
        foreach ($this->items as $item) {
            $renewalSubscription->items()->create([
                'flag_product_id' => $item->flag_product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        return $renewalSubscription;
    }
}