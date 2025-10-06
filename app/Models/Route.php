<?php
// app/Models/Route.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'assigned_user_id',
        'customer_order',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'customer_order' => 'array',
    ];

    // Relationships

    /**
     * Get the assigned user (driver/technician).
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get customers on this route in order.
     */
    public function customers()
    {
        $customerIds = $this->customer_order ?: [];

        if (empty($customerIds)) {
            return collect();
        }

        $users = User::whereIn('id', $customerIds)->get();

        return collect($customerIds)->map(function ($id) use ($users) {
            return $users->firstWhere('id', $id);
        })->filter();
    }

    // Scopes

    /**
     * Scope to get routes by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get routes by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get planned routes.
     */
    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    /**
     * Scope to get in-progress routes.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get completed routes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get routes assigned to a user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    // Route Management Methods

    /**
     * Add customer to route.
     */
    public function addCustomer($customerId)
    {
        $order = $this->customer_order ?: [];

        if (!in_array($customerId, $order)) {
            $order[] = $customerId;
            $this->customer_order = $order;
            $this->save();
        }
    }

    /**
     * Remove customer from route.
     */
    public function removeCustomer($customerId)
    {
        $order = $this->customer_order ?: [];
        $order = array_filter($order, function ($id) use ($customerId) {
            return $id != $customerId;
        });

        $this->customer_order = array_values($order);
        $this->save();
    }

    /**
     * Reorder customers in the route.
     */
    public function reorderCustomers(array $customerIds)
    {
        $this->customer_order = $customerIds;
        $this->save();
    }

    // Google Maps Integration

    /**
     * Optimize route using Google Maps Directions API with waypoint optimization.
     */
    public function optimizeWithGoogleMaps()
    {
        $customers = $this->customers();

        if ($customers->count() < 2) {
            return $this->customer_order;
        }

        $apiKey = config('services.google.maps_api_key');

        if (!$apiKey) {
            throw new \Exception('Google Maps API key not configured');
        }

        // Get addresses
        $addresses = $customers->map(function ($customer) {
            return $customer->full_address;
        })->toArray();

        // Use first address as origin and last as destination
        $origin = $addresses[0];
        $destination = end($addresses);

        // Middle addresses are waypoints
        $waypoints = array_slice($addresses, 1, -1);

        // Build waypoints string with optimization
        $waypointsStr = 'optimize:true';
        if (!empty($waypoints)) {
            $waypointsStr .= '|' . implode('|', array_map('urlencode', $waypoints));
        }

        // Call Google Maps Directions API
        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $origin,
            'destination' => $destination,
            'waypoints' => $waypointsStr,
            'key' => $apiKey,
        ]);

        if (!$response->successful() || $response['status'] !== 'OK') {
            throw new \Exception('Failed to optimize route with Google Maps API');
        }

        // Extract optimized order
        $waypointOrder = $response['routes'][0]['waypoint_order'] ?? [];

        // Build new customer order
        $optimizedOrder = [$this->customer_order[0]]; // Start with origin

        // Add waypoints in optimized order
        foreach ($waypointOrder as $index) {
            $optimizedOrder[] = $this->customer_order[$index + 1];
        }

        // Add destination
        if (count($this->customer_order) > 1) {
            $optimizedOrder[] = end($this->customer_order);
        }

        // Save optimized order
        $this->customer_order = $optimizedOrder;
        $this->save();

        return $optimizedOrder;
    }

    /**
     * Get turn-by-turn directions from Google Maps.
     */
    public function getGoogleMapsDirections()
    {
        $customers = $this->customers();

        if ($customers->isEmpty()) {
            return null;
        }

        $apiKey = config('services.google.maps_api_key');

        if (!$apiKey) {
            throw new \Exception('Google Maps API key not configured');
        }

        $addresses = $customers->map(function ($customer) {
            return $customer->full_address;
        })->toArray();

        if (count($addresses) < 2) {
            return null;
        }

        $origin = $addresses[0];
        $destination = end($addresses);
        $waypoints = array_slice($addresses, 1, -1);

        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'key' => $apiKey,
            'alternatives' => false,
        ];

        if (!empty($waypoints)) {
            $params['waypoints'] = implode('|', array_map('urlencode', $waypoints));
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', $params);

        if (!$response->successful() || $response['status'] !== 'OK') {
            throw new \Exception('Failed to get directions from Google Maps API');
        }

        $route = $response['routes'][0];
        $legs = $route['legs'];

        // Format directions
        $directions = [];
        $totalDistance = 0;
        $totalDuration = 0;

        foreach ($legs as $legIndex => $leg) {
            $totalDistance += $leg['distance']['value'];
            $totalDuration += $leg['duration']['value'];

            $customer = $customers[$legIndex];

            $directions[] = [
                'stop_number' => $legIndex + 1,
                'customer_name' => $customer->name,
                'address' => $customer->full_address,
                'distance' => $leg['distance']['text'],
                'duration' => $leg['duration']['text'],
                'steps' => collect($leg['steps'])->map(function ($step) {
                    return [
                        'instruction' => strip_tags($step['html_instructions']),
                        'distance' => $step['distance']['text'],
                        'duration' => $step['duration']['text'],
                    ];
                })->toArray(),
            ];
        }

        return [
            'total_distance' => round($totalDistance / 1609.34, 2) . ' miles',
            'total_duration' => round($totalDuration / 60) . ' minutes',
            'stops' => $directions,
            'overview_polyline' => $route['overview_polyline']['points'],
        ];
    }

    /**
     * Get Google Maps URL for the route.
     */
    public function getGoogleMapsUrlAttribute()
    {
        $customers = $this->customers();

        if ($customers->isEmpty()) {
            return null;
        }

        $waypoints = $customers->map(function ($customer) {
            return urlencode($customer->full_address);
        });

        $origin = $waypoints->first();
        $destination = $waypoints->last();
        $waypointsStr = $waypoints->slice(1, -1)->implode('|');

        $url = "https://www.google.com/maps/dir/{$origin}";

        if ($waypointsStr) {
            $url .= "/{$waypointsStr}";
        }

        $url .= "/{$destination}";

        return $url;
    }

    // Helper Methods

    /**
     * Get total number of stops on this route.
     */
    public function getTotalStopsAttribute()
    {
        return count($this->customer_order ?: []);
    }

    /**
     * Check if route is planned.
     */
    public function isPlanned()
    {
        return $this->status === 'planned';
    }

    /**
     * Check if route is in progress.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if route is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Start the route.
     */
    public function start($userId = null)
    {
        $this->status = 'in_progress';

        if ($userId) {
            $this->assigned_user_id = $userId;
        }

        $this->save();
    }

    /**
     * Complete the route.
     */
    public function complete($notes = null)
    {
        $this->status = 'completed';

        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . $notes;
        }

        $this->save();
    }
}
