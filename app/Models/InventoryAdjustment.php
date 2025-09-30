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
        'previous_quantity',  // CHANGED from 'previous_inventory'
        'new_quantity',       // CHANGED from 'new_inventory'
        'reason',
        'adjusted_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',  // CHANGED from 'previous_inventory'
        'new_quantity' => 'integer',       // CHANGED from 'new_inventory'
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
            'restock' => 'Stock Added / Restocked',
            'sale' => 'Sale / Used',
            'damage' => 'Damaged',
            'loss' => 'Lost',
            'return' => 'Returned',
            'correction' => 'Correction',
            default => ucfirst($this->adjustment_type),
        };
    }

    /**
     * Get the net change in inventory.
     */
    public function getNetChangeAttribute(): int
    {
        if ($this->new_quantity !== null && $this->previous_quantity !== null) {
            return $this->new_quantity - $this->previous_quantity;
        }

        return $this->quantity;
    }

    /**
     * Check if adjustment increases inventory.
     */
    public function isIncrease(): bool
    {
        return in_array($this->adjustment_type, ['initial', 'restock', 'return']) ||
               $this->quantity > 0;
    }

    /**
     * Check if adjustment decreases inventory.
     */
    public function isDecrease(): bool
    {
        return in_array($this->adjustment_type, ['sale', 'damage', 'loss']) ||
               $this->quantity < 0;
    }

    /**
     * Get color class for UI display.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->adjustment_type) {
            'initial', 'restock', 'return' => 'text-green-600',
            'sale', 'damage', 'loss' => 'text-red-600',
            'correction' => 'text-blue-600',
            default => 'text-gray-600',
        };
    }
}
