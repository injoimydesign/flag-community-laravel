<?php
// app/Models/FlagType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlagType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
        'image_path',
        'design_file_path',
        'category',
        'active',
        'featured',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    // Relationships

    /**
     * Get all products for this flag type.
     */
    public function products()
    {
        return $this->hasMany(FlagProduct::class);
    }

    /**
     * Get all flag products for this flag type (alias for products).
     */
    public function flagProducts()
    {
        return $this->hasMany(FlagProduct::class);
    }

    /**
     * Get active products for this flag type.
     */
    public function activeProducts()
    {
        return $this->hasMany(FlagProduct::class)->where('active', true);
    }

    /**
     * Get available products for this flag type.
     */
    public function availableProducts()
    {
        return $this->hasMany(FlagProduct::class)->where('active', true);
    }

    /**
     * Get flag placements for this type.
     */
    public function placements()
    {
        return $this->hasManyThrough(
            FlagPlacement::class,
            FlagProduct::class
        );
    }

    // Scopes

    /**
     * Scope to get only active flag types.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get only featured flag types.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope to get flag types by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Static methods

    /**
     * Get US flag types.
     */
    public static function usFlags()
    {
        return self::active()->byCategory('us')->ordered()->get();
    }

    /**
     * Get military flag types.
     */
    public static function militaryFlags()
    {
        return self::active()->byCategory('military')->ordered()->get();
    }

    /**
     * Get all categories.
     */
    public static function getCategories()
    {
        return self::distinct()->pluck('category')->filter()->sort()->values();
    }

    // Helper methods

    /**
     * Check if this is a US flag.
     */
    public function isUsFlag()
    {
        return $this->category === 'us';
    }

    /**
     * Check if this is a military flag.
     */
    public function isMilitaryFlag()
    {
        return $this->category === 'military';
    }

    /**
     * Get the flag image URL with fallback.
     */
    public function getImageAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        if ($this->image_url) {
            return $this->image_url;
        }
        return asset('images/flags/default-flag.jpg');
    }

    /**
     * Get total product count.
     */
    public function getTotalProductsAttribute()
    {
        return $this->flagProducts()->count();
    }

    /**
     * Get active product count.
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->flagProducts()->where('active', true)->count();
    }

    /**
     * Get total inventory across all products.
     */
    public function getTotalInventoryAttribute()
    {
        return $this->flagProducts()->sum('current_inventory');
    }

    /**
     * Check if has low inventory products.
     */
    public function hasLowInventory()
    {
        return $this->flagProducts()
            ->whereRaw('current_inventory <= low_inventory_threshold')
            ->exists();
    }
}