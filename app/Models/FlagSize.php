<?php
// app/Models/FlagSize.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlagSize extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'dimensions',
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
     * Get all products for this flag size.
     */
    public function products()
    {
        return $this->hasMany(FlagProduct::class);
    }

    /**
     * Get active products for this flag size.
     */
    public function availableProducts()
    {
        return $this->hasMany(FlagProduct::class)->where('active', true);
    }

    // Scopes

    /**
     * Scope to get only active flag sizes.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods

    /**
     * Get display name for the flag size.
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->dimensions . ')';
    }

    /**
     * Parse dimensions to get width and height.
     */
    public function getDimensionsArrayAttribute()
    {
        // Parse dimensions like "3'x5'" into array
        $dimensions = str_replace("'", '', $this->dimensions);
        $parts = explode('x', $dimensions);
        
        return [
            'width' => isset($parts[0]) ? (int) $parts[0] : 3,
            'height' => isset($parts[1]) ? (int) $parts[1] : 5
        ];
    }
}