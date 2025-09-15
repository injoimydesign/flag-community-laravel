<?php
// app/Models/PotentialCustomer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PotentialCustomer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'interested_flags',
        'notes',
        'notified_when_available',
        'notified_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'interested_flags' => 'array',
        'notified_when_available' => 'boolean',
        'notified_at' => 'datetime',
    ];

    // Scopes

    /**
     * Scope to get potential customers by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending potential customers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get contacted potential customers.
     */
    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    /**
     * Scope to get converted potential customers.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope to get potential customers in a specific area.
     */
    public function scopeInArea($query, $latitude, $longitude, $radiusMiles)
    {
        return $query->whereRaw('
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )) <= ?
        ', [
            $latitude, 
            $longitude, 
            $latitude, 
            $radiusMiles * 1.60934 // Convert miles to km
        ]);
    }

    /**
     * Scope to get potential customers in specific zip codes.
     */
    public function scopeInZipCodes($query, array $zipCodes)
    {
        return $query->whereIn('zip_code', $zipCodes);
    }

    /**
     * Scope to get potential customers not yet notified.
     */
    public function scopeNotNotified($query)
    {
        return $query->where('notified_when_available', false);
    }

    // Static methods

    /**
     * Find potential customers in a newly expanded service area.
     */
    public static function findInServiceArea(ServiceArea $serviceArea)
    {
        return self::pending()
            ->where(function ($query) use ($serviceArea) {
                // Check by coordinates
                $query->where(function ($q) use ($serviceArea) {
                    $q->whereNotNull('latitude')
                      ->whereNotNull('longitude')
                      ->whereRaw('
                          (6371 * acos(
                              cos(radians(?)) * cos(radians(latitude)) * 
                              cos(radians(longitude) - radians(?)) + 
                              sin(radians(?)) * sin(radians(latitude))
                          )) <= ?
                      ', [
                          $serviceArea->center_latitude, 
                          $serviceArea->center_longitude, 
                          $serviceArea->center_latitude, 
                          $serviceArea->radius_miles * 1.60934
                      ]);
                })
                // Or check by zip code
                ->orWhereIn('zip_code', $serviceArea->zip_codes ?: []);
            })
            ->get();
    }

    /**
     * Create from checkout attempt outside service area.
     */
    public static function createFromCheckoutAttempt(array $customerData, array $flagInterests = [])
    {
        return self::create([
            'first_name' => $customerData['first_name'],
            'last_name' => $customerData['last_name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'] ?? null,
            'address' => $customerData['address'],
            'city' => $customerData['city'],
            'state' => $customerData['state'],
            'zip_code' => $customerData['zip_code'],
            'latitude' => $customerData['latitude'] ?? null,
            'longitude' => $customerData['longitude'] ?? null,
            'interested_flags' => $flagInterests,
            'notes' => 'Created from checkout attempt outside service area',
            'status' => 'pending',
        ]);
    }

    // Helper methods

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the customer's full address.
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state} {$this->zip_code}";
    }

    /**
     * Get coordinates as array for mapping.
     */
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude
        ];
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute()
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'text-yellow-600',
            'contacted' => 'text-blue-600',
            'converted' => 'text-green-600',
            'not_interested' => 'text-gray-600',
        ][$this->status] ?? 'text-gray-600';
    }

    /**
     * Get interested flag types as comma-separated string.
     */
    public function getInterestedFlagsDisplayAttribute()
    {
        if (empty($this->interested_flags)) {
            return 'Any flags';
        }

        $flagTypes = FlagType::whereIn('id', $this->interested_flags)->pluck('name');
        return $flagTypes->implode(', ');
    }

    /**
     * Mark as contacted.
     */
    public function markAsContacted($notes = null)
    {
        $this->status = 'contacted';
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . $notes;
        }
        
        $this->save();
    }

    /**
     * Mark as converted to customer.
     */
    public function markAsConverted($userId = null)
    {
        $this->status = 'converted';
        
        if ($userId) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . "Converted to customer ID: {$userId}";
        }
        
        $this->save();
    }

    /**
     * Mark as not interested.
     */
    public function markAsNotInterested($reason = null)
    {
        $this->status = 'not_interested';
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . "Not interested: {$reason}";
        }
        
        $this->save();
    }

    /**
     * Send service area expansion notification.
     */
    public function sendServiceAreaNotification()
    {
        if ($this->notified_when_available) {
            return false; // Already notified
        }

        // Create notification record
        Notification::create([
            'user_id' => null, // Not a user yet
            'type' => 'email',
            'subject' => "Great news! Flag service is now available in your area",
            'message' => "Hi {$this->first_name},\n\nWe're excited to let you know that Flags Across Our Community is now serving {$this->city}, {$this->state}! You can now sign up for our flag subscription service.\n\nBest regards,\nFlags Across Our Community Team",
            'metadata' => [
                'potential_customer_id' => $this->id,
                'notification_type' => 'service_area_expansion',
                'email' => $this->email,
            ]
        ]);

        // Mark as notified
        $this->notified_when_available = true;
        $this->notified_at = Carbon::now();
        $this->save();

        return true;
    }

    /**
     * Check if coordinates are within a service area.
     */
    public function isInServiceArea()
    {
        if (!$this->latitude || !$this->longitude) {
            // Check by zip code if coordinates not available
            return ServiceArea::where('active', true)
                ->where(function ($query) {
                    $query->whereJsonContains('zip_codes', $this->zip_code);
                })
                ->exists();
        }

        return ServiceArea::isAddressServed($this->latitude, $this->longitude, $this->zip_code);
    }

    /**
     * Convert to actual customer account.
     */
    public function convertToCustomer($password)
    {
        // Create user account
        $user = User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => bcrypt($password),
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'in_service_area' => true,
            'role' => 'customer',
        ]);

        // Mark this potential customer as converted
        $this->markAsConverted($user->id);

        // Send welcome notification
        Notification::createWelcomeNotification($user->id);

        return $user;
    }
}