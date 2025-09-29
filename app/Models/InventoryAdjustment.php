<?php
// app/Models/InventoryAdjustment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'flag_product_id',
        'adjustment_type',
        'quantity',
        'previous_inventory',
        'new_inventory',
        'reason',
        'adjusted_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_inventory' => 'integer',
        'new_inventory' => 'integer',
    ];

    /**
     * Get the flag product that owns the adjustment.
     */
    public function flagProduct()
    {
        return $this->belongsTo(FlagProduct::class);
    }

    /**
     * Get the user who made the adjustment.
     */
    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Scope adjustments by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('adjustment_type', $type);
    }

    /**
     * Scope recent adjustments.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get formatted adjustment type for display.
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->adjustment_type) {
            'initial' => 'Initial Stock',
            'increase' => 'Stock Added',
            'decrease' => 'Stock Removed',
            'set' => 'Stock Set',
            'usage' => 'Used for Placement',
            'restock' => 'Restocked',
            default => ucfirst($this->adjustment_type),
        };
    }

    /**
     * Get the net change in inventory.
     */
    public function getNetChangeAttribute(): int
    {
        if ($this->new_inventory !== null && $this->previous_inventory !== null) {
            return $this->new_inventory - $this->previous_inventory;
        }

        return $this->quantity;
    }

    /**
     * Check if adjustment increases inventory.
     */
    public function isIncrease(): bool
    {
        return in_array($this->adjustment_type, ['initial', 'increase', 'restock']) ||
               $this->quantity > 0;
    }

    /**
     * Check if adjustment decreases inventory.
     */
    public function isDecrease(): bool
    {
        return in_array($this->adjustment_type, ['decrease', 'usage']) ||
               $this->quantity < 0;
    }

    /**
     * Get color class for UI display.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->adjustment_type) {
            'initial', 'increase', 'restock' => 'text-green-600',
            'decrease', 'usage' => 'text-red-600',
            'set' => 'text-blue-600',
            default => 'text-gray-600',
        };
    }
}
