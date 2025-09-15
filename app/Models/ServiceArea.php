<?php
// app/Models/ServiceArea.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'center_latitude',
        'center_longitude', 
        'radius_miles',
        'zip_codes',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'zip_codes' => 'array',
        'active' => 'boolean',
    ];

    // Static methods

    /**
     * Check if an address is within any active service area.
     */
    public static function isAddressServed($latitude, $longitude, $zipCode = null)
    {
        $activeAreas = self::where('active', true)->get();
        
        foreach ($activeAreas as $area) {
            if ($area->containsCoordinates($latitude, $longitude)) {
                return true;
            }
            
            if ($zipCode && $area->containsZipCode($zipCode)) {
                return true;
            }
        }
        
        return false;
    }

    // Instance methods

    /**
     * Check if coordinates are within this service area.
     */
    public function containsCoordinates($latitude, $longitude)
    {
        $distance = $this->calculateDistance(
            $this->center_latitude, 
            $this->center_longitude,
            $latitude, 
            $longitude
        );
        
        return $distance <= $this->radius_miles;
    }

    /**
     * Check if zip code is served by this area.
     */
    public function containsZipCode($zipCode)
    {
        return in_array($zipCode, $this->zip_codes ?? []);
    }

    /**
     * Get center coordinates as array for mapping.
     */
    public function getCenterCoordinatesAttribute()
    {
        return [
            'lat' => (float) $this->center_latitude,
            'lng' => (float) $this->center_longitude
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 3959; // miles

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Get all users within this service area.
     */
    public function users()
    {
        return User::where('in_service_area', true)
            ->where(function ($query) {
                $query->whereRaw('
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?
                ', [
                    $this->center_latitude, 
                    $this->center_longitude, 
                    $this->center_latitude, 
                    $this->radius_miles * 1.60934 // Convert miles to km
                ]);
            })
            ->orWhereIn('zip_code', $this->zip_codes ?? []);
    }
}