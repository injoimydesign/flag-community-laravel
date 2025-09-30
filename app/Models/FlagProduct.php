<?php
// app/Models/FlagProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlagProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flag_type_id',
        'flag_size_id',
        'one_time_price',
        'annual_subscription_price',
        'stripe_price_id_onetime',
        'stripe_price_id_annual',
        'inventory_count',
        'min_inventory_alert',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'one_time_price' => 'decimal:2',
        'annual_subscription_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    // Relationships

    /**
     * Get the flag type for this product.
     */
    public function flagType()
    {
        return $this->belongsTo(FlagType::class);
    }

    /**
     * Get the flag size for this product.
     */
    public function flagSize()
    {
        return $this->belongsTo(FlagSize::class);
    }

    /**
     * Get subscription items for this product.
     */
    public function subscriptionItems()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Get flag placements for this product.
     */
    public function flagPlacements()
    {
        return $this->hasMany(FlagPlacement::class);
    }

    // Scopes

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }


    /**
     * Scope to get products by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('flagType', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }

    // Helper methods

    /**
     * Get the product name.
     */
    public function getNameAttribute()
    {
        return $this->flagType->name . ' - ' . $this->flagSize->name;
    }

    /**
     * Get the product display name.
     */
    public function getDisplayNameAttribute()
    {
        return $this->flagType->name . ' (' . $this->flagSize->dimensions . ')';
    }

    /**
     * Check if product has low inventory.
     */
    public function hasLowInventory()
    {
        return $this->inventory_count <= $this->min_inventory_alert;
    }

    /**
     * Check if product is in stock.
     */
    public function inStock()
    {
        return $this->inventory_count > 0;
    }


    /**
     * Get savings amount for annual subscription.
     */
    public function getAnnualSavingsAttribute()
    {
        // Assume one-time price would be paid 5 times per year for holidays
        $annualOnetime = $this->one_time_price * 5;
        return max(0, $annualOnetime - $this->annual_subscription_price);
    }

    /**
     * Get formatted annual savings.
     */
    public function getFormattedAnnualSavingsAttribute()
    {
        return '$' . number_format($this->annual_savings, 2);
    }


/**
 * Get inventory adjustments for this product.
 */
public function inventoryAdjustments()
{
    return $this->hasMany(InventoryAdjustment::class);
}

/**
 * Scope for low inventory products.
 */
public function scopeLowInventory($query)
{
    return $query->whereRaw('current_inventory <= low_inventory_threshold');
}

/**
 * Check if product is low on inventory.
 */
public function isLowInventory(): bool
{
    return $this->current_inventory <= $this->low_inventory_threshold;
}

/**
 * Check if product is out of stock.
 */
public function isOutOfStock(): bool
{
    return $this->current_inventory <= 0;
}

/**
 * Get formatted one-time price.
 */
public function getFormattedOneTimePriceAttribute(): string
{
    return '$' . number_format($this->one_time_price / 100, 2);
}

/**
 * Get formatted annual subscription price.
 */
public function getFormattedAnnualPriceAttribute(): string
{
    return '$' . number_format($this->annual_subscription_price / 100, 2);
}

/**
 * Get formatted cost per unit.
 */
public function getFormattedCostPerUnitAttribute(): string
{
    return '$' . number_format($this->cost_per_unit / 100, 2);
}

/**
 * Get inventory value.
 */
public function getInventoryValueAttribute(): float
{
    return ($this->current_inventory * $this->cost_per_unit) / 100;
}

/**
 * Get active subscription count for this product.
 */
public function getActiveSubscriptionCount(): int
{
    return $this->subscriptionItems()
        ->whereHas('subscription', function ($query) {
            $query->where('status', 'active');
        })
        ->count();
}

/**
 * Get total placement count for this product.
 */
public function getTotalPlacementCount(): int
{
    return FlagPlacement::where('flag_product_id', $this->id)
        ->where('status', 'placed')
        ->count();
}

/**
 * Get monthly usage for this product.
 */
public function getMonthlyUsage(): int
{
    return FlagPlacement::where('flag_product_id', $this->id)
        ->whereMonth('placement_date', Carbon::now()->month)
        ->whereYear('placement_date', Carbon::now()->year)
        ->count();
}

/**
 * Adjust inventory and log the change.
 * This method is in the FlagProduct model (app/Models/FlagProduct.php)
 *
 * FIXES: Updated column names from previous_inventory/new_inventory to previous_quantity/new_quantity
 */
public function adjustInventory(int $quantity, string $type, string $reason, int $adjustedBy = null): bool
{
    $previousInventory = $this->current_inventory;

    // Map the type to database enum values
    $dbAdjustmentType = match($type) {
        'increase' => 'restock',
        'decrease' => 'sale',
        'set' => 'correction',
        'restock' => 'restock',
        'sale' => 'sale',
        'damage' => 'damage',
        'loss' => 'loss',
        'return' => 'return',
        'correction' => 'correction',
        default => 'correction'
    };

    switch ($type) {
        case 'increase':
        case 'restock':
        case 'return':
            $newInventory = $previousInventory + $quantity;
            $actualQuantity = $quantity;
            break;
        case 'decrease':
        case 'sale':
        case 'damage':
        case 'loss':
            $newInventory = max(0, $previousInventory - $quantity);
            $actualQuantity = -$quantity;
            break;
        case 'set':
        case 'correction':
            $newInventory = $quantity;
            $actualQuantity = $quantity - $previousInventory; // Actual change
            break;
        default:
            return false;
    }

    // Update inventory
    $this->update(['current_inventory' => $newInventory]);

    // Log adjustment with CORRECT column names
    InventoryAdjustment::create([
        'flag_product_id' => $this->id,
        'adjustment_type' => $dbAdjustmentType,
        'quantity' => $actualQuantity,
        'previous_quantity' => $previousInventory,  // FIXED: Changed from 'previous_inventory'
        'new_quantity' => $newInventory,            // FIXED: Changed from 'new_inventory'
        'reason' => $reason,
        'adjusted_by' => $adjustedBy ?: auth()->id(),
    ]);

    return true;
}

/**
 * Use inventory for flag placement.
 */
public function useForPlacement(int $quantity = 1, string $reason = 'Used for flag placement'): bool
{
    if ($this->current_inventory < $quantity) {
        return false; // Not enough inventory
    }

    return $this->adjustInventory($quantity, 'sale', $reason);  // Use 'sale' enum value
}
}
