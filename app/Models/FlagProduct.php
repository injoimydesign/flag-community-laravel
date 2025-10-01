<?php
// app/Models/FlagProduct.php
// CRITICAL FIX: Add current_inventory, cost_per_unit, and low_inventory_threshold to $fillable

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlagProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * FIXED: Added current_inventory, low_inventory_threshold, and cost_per_unit
     */
    protected $fillable = [
        'flag_type_id',
        'flag_size_id',
        'one_time_price',
        'annual_subscription_price',
        'cost_per_unit',              // ADDED
        'current_inventory',          // ADDED - THIS WAS MISSING!
        'low_inventory_threshold',    // ADDED
        'stripe_price_id_onetime',
        'stripe_price_id_annual',
        'inventory_count',            // Keep for backwards compatibility
        'min_inventory_alert',        // Keep for backwards compatibility
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
     protected $casts = [
       'one_time_price' => 'integer',  // Changed from decimal:2
       'annual_subscription_price' => 'integer',  // Changed from decimal:2
       'cost_per_unit' => 'integer',  // ADD THIS
       'current_inventory' => 'integer',  // ADD THIS
       'low_inventory_threshold' => 'integer',  // ADD THIS
       'inventory_count' => 'integer',
       'min_inventory_alert' => 'integer',
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

    /**
     * Get inventory adjustments for this product.
     */
    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
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

    /**
     * Scope for low inventory products.
     */
    public function scopeLowInventory($query)
    {
        return $query->whereRaw('current_inventory <= low_inventory_threshold');
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
        return $this->current_inventory <= $this->low_inventory_threshold;
    }

    /**
     * Check if product is in stock.
     */
    public function inStock()
    {
        return $this->current_inventory > 0;
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
        return $this->flagPlacements()
            ->where('status', 'placed')
            ->count();
    }

    /**
     * Get monthly usage for this product.
     */
    public function getMonthlyUsage(): int
    {
        return $this->flagPlacements()
            ->whereMonth('placement_date', Carbon::now()->month)
            ->whereYear('placement_date', Carbon::now()->year)
            ->count();
    }

    /**
     * Adjust inventory and log the change.
     *
     * FIXED: Updated to use correct column names
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
                $actualQuantity = $quantity - $previousInventory;
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
            'previous_quantity' => $previousInventory,
            'new_quantity' => $newInventory,
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

        return $this->adjustInventory($quantity, 'sale', $reason);
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
}
