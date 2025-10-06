<?php
// app/Models/FlagPlacement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlagPlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'holiday_id',
        'flag_product_id',
        'placement_date',
        'removal_date',
        'status',
        'placed_at',
        'removed_at',
        'skipped_at',
        'notes',
        'skip_reason',
        'placed_by',
        'removed_by',
        // Add placement address fields
        'placement_address',
        'placement_city',
        'placement_state',
        'placement_zip_code',
        'placement_latitude',
        'placement_longitude',
    ];

    protected $casts = [
        'placement_date' => 'date',
        'removal_date' => 'date',
        'placed_at' => 'datetime',
        'removed_at' => 'datetime',
        'skipped_at' => 'datetime',
        'placement_latitude' => 'decimal:8',
        'placement_longitude' => 'decimal:8',
    ];


    /**
     * Get the subscription that owns the placement.
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
        return $this->belongsTo(User::class, 'placed_by');
    }

    /**
     * Get the user who removed the flag.
     */
    public function removedByUser()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    /**
     * Scope for scheduled placements.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for placed flags.
     */
    public function scopePlaced($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope for removed flags.
     */
    public function scopeRemoved($query)
    {
        return $query->where('status', 'removed');
    }

    /**
     * Scope for skipped placements.
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    /**
     * Scope for overdue placements.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('placement_date', '<', Carbon::now());
    }

    /**
     * Scope for upcoming placements.
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', 'scheduled')
                    ->whereBetween('placement_date', [
                        Carbon::now(),
                        Carbon::now()->addDays($days)
                    ]);
    }

    /**
     * Check if placement is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' &&
               $this->placement_date->isPast();
    }

    /**
     * Check if placement is due today.
     */
    public function isDueToday(): bool
    {
        return $this->status === 'scheduled' &&
               $this->placement_date->isToday();
    }

    /**
     * Check if placement is upcoming.
     */
    public function isUpcoming(int $days = 7): bool
    {
        return $this->status === 'scheduled' &&
               $this->placement_date->isFuture() &&
               $this->placement_date->diffInDays(Carbon::now()) <= $days;
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'scheduled' => $this->isOverdue() ? 'red' : ($this->isDueToday() ? 'yellow' : 'blue'),
            'placed' => 'green',
            'removed' => 'gray',
            'skipped' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        $status = ucfirst($this->status);

        if ($this->status === 'scheduled' && $this->isOverdue()) {
            $status .= ' (Overdue)';
        } elseif ($this->status === 'scheduled' && $this->isDueToday()) {
            $status .= ' (Due Today)';
        }

        return $status;
    }

    /**
     * Get days until placement.
     */
    public function getDaysUntilPlacement(): int
    {
        return Carbon::now()->diffInDays($this->placement_date, false);
    }

    /**
     * Get days since placement.
     */
    public function getDaysSincePlacement(): int
    {
        if (!$this->placed_at) {
            return 0;
        }

        return $this->placed_at->diffInDays(Carbon::now());
    }

    /**
     * Mark placement as placed.
     */
    public function markAsPlaced(int $placedBy = null, string $notes = null): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $this->update([
            'status' => 'placed',
            'placed_at' => Carbon::now(),
            'placed_by' => $placedBy ?: auth()->id(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return true;
    }

    /**
     * Mark placement as removed.
     */
    public function markAsRemoved(int $removedBy = null, string $notes = null): bool
    {
        if ($this->status !== 'placed') {
            return false;
        }

        $this->update([
            'status' => 'removed',
            'removed_at' => Carbon::now(),
            'removed_by' => $removedBy ?: auth()->id(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return true;
    }

    /**
     * Mark placement as skipped.
     */
    public function markAsSkipped(string $reason, string $notes = null): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $this->update([
            'status' => 'skipped',
            'skipped_at' => Carbon::now(),
            'skip_reason' => $reason,
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return true;
    }

    /**
     * Reschedule placement.
     */
    public function reschedule(Carbon $newDate, string $reason = null): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $oldDate = $this->placement_date;

        $this->update([
            'placement_date' => $newDate,
            'notes' => ($this->notes ? $this->notes . "\n" : '') .
                      "Rescheduled from {$oldDate->format('Y-m-d')} to {$newDate->format('Y-m-d')}" .
                      ($reason ? ": {$reason}" : ''),
        ]);

        return true;
    }

    /**
     * Get the full placement address.
     */
    public function getFullPlacementAddressAttribute()
    {
        if (!$this->placement_address) {
            // Fallback to subscription user address
            return $this->subscription->user->address ?? 'N/A';
        }

        return trim(sprintf(
            '%s, %s, %s %s',
            $this->placement_address,
            $this->placement_city,
            $this->placement_state,
            $this->placement_zip_code
        ));
    }


    /**
     * Get completion percentage for a set of placements.
     */
    public static function getCompletionRate($placements): float
    {
        if ($placements->isEmpty()) {
            return 0;
        }

        $completed = $placements->whereIn('status', ['placed', 'removed'])->count();
        $total = $placements->count();

        return round(($completed / $total) * 100, 2);
    }
}
