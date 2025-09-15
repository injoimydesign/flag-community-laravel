<?php
// app/Models/FlagProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Scope to get products with low inventory.
     */
    public function scopeLowInventory($query)
    {
        return $query->whereColumn('inventory_count', '<=', 'min_inventory_alert');
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
     * Get formatted one-time price.
     */
    public function getFormattedOneTimePriceAttribute()
    {
        return '$' . number_format($this->one_time_price, 2);
    }

    /**
     * Get formatted annual subscription price.
     */
    public function getFormattedAnnualPriceAttribute()
    {
        return '$' . number_format($this->annual_subscription_price, 2);
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
     * Update inventory count.
     */
    public function adjustInventory($quantity, $reason = null)
    {
        $this->inventory_count += $quantity;
        $this->save();

        // Log inventory change if needed
        // InventoryLog::create([
        //     'flag_product_id' => $this->id,
        //     'quantity_change' => $quantity,
        //     'reason' => $reason,
        //     'new_count' => $this->inventory_count
        // ]);
    }
}