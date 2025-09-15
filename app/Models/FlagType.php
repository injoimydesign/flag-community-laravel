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
        'category',
        'active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
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
     * Get active products for this flag type.
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
    public function getImageUrlAttribute($value)
    {
        return $value ?: asset('images/flags/default-flag.jpg');
    }
}