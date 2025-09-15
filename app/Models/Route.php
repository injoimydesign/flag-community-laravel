<?php
// app/Models/Route.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'date',
        'type',
        'holiday_id',
        'assigned_user_id',
        'customer_order',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'customer_order' => 'array',
    ];

    // Relationships

    /**
     * Get the holiday for this route.
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Get the assigned user (driver/technician).
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get flag placements for this route.
     */
    public function flagPlacements()
    {
        $customerIds = $this->customer_order ?: [];

        if ($this->type === 'placement') {
            return FlagPlacement::whereHas('subscription', function ($query) use ($customerIds) {
                $query->whereIn('user_id', $customerIds);
            })
            ->where('holiday_id', $this->holiday_id)
            ->where('placement_date', $this->date);
        } else {
            return FlagPlacement::whereHas('subscription', function ($query) use ($customerIds) {
                $query->whereIn('user_id', $customerIds);
            })
            ->where('holiday_id', $this->holiday_id)
            ->where('removal_date', $this->date);
        }
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

        // Get users in the specified order
        $users = User::whereIn('id', $customerIds)->get();

        // Sort by the order specified in customer_order array
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
     * Scope to get today's routes.
     */
    public function scopeToday($query)
    {
        return $query->where('date', Carbon::today());
    }

    /**
     * Scope to get routes for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope to get routes assigned to a user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    // Static methods

    /**
     * Generate routes for flag placements on a specific date.
     */
    public static function generatePlacementRoutes($date, $holidayId)
    {
        // Get all flag placements for this date and holiday
        $placements = FlagPlacement::with(['subscription.user'])
            ->where('placement_date', $date)
            ->where('holiday_id', $holidayId)
            ->where('status', 'scheduled')
            ->get();

        if ($placements->isEmpty()) {
            return collect();
        }

        // Group customers by geographic proximity
        $customerGroups = self::groupCustomersByProximity($placements);

        $routes = collect();
        $routeNumber = 1;

        foreach ($customerGroups as $group) {
            // Optimize route order using Google Maps API
            $optimizedOrder = self::optimizeRouteOrder($group);

            $route = self::create([
                'name' => "Placement Route #{$routeNumber} - " . Carbon::parse($date)->format('M j'),
                'date' => $date,
                'type' => 'placement',
                'holiday_id' => $holidayId,
                'customer_order' => $optimizedOrder->pluck('id')->toArray(),
                'status' => 'planned',
            ]);

            $routes->push($route);
            $routeNumber++;
        }

        return $routes;
    }

    /**
     * Generate routes for flag removals on a specific date.
     */
    public static function generateRemovalRoutes($date, $holidayId)
    {
        // Get all flag placements for removal on this date and holiday
        $placements = FlagPlacement::with(['subscription.user'])
            ->where('removal_date', $date)
            ->where('holiday_id', $holidayId)
            ->where('status', 'placed')
            ->get();

        if ($placements->isEmpty()) {
            return collect();
        }

        // Group customers by geographic proximity
        $customerGroups = self::groupCustomersByProximity($placements);

        $routes = collect();
        $routeNumber = 1;

        foreach ($customerGroups as $group) {
            // Optimize route order using Google Maps API
            $optimizedOrder = self::optimizeRouteOrder($group);

            $route = self::create([
                'name' => "Removal Route #{$routeNumber} - " . Carbon::parse($date)->format('M j'),
                'date' => $date,
                'type' => 'removal',
                'holiday_id' => $holidayId,
                'customer_order' => $optimizedOrder->pluck('id')->toArray(),
                'status' => 'planned',
            ]);

            $routes->push($route);
            $routeNumber++;
        }

        return $routes;
    }

    // Helper methods

    /**
     * Group customers by geographic proximity.
     */
    private static function groupCustomersByProximity($placements)
    {
        $customers = $placements->map(function ($placement) {
            return $placement->subscription->user;
        })->unique('id');

        // Simple grouping by ZIP code for now
        // In production, you'd want more sophisticated clustering
        return $customers->groupBy('zip_code')->values();
    }

    /**
     * Optimize route order for minimum travel time.
     */
    private static function optimizeRouteOrder($customers)
    {
        // This is a simplified version. In production, you'd use Google Maps
        // Directions API to get actual travel times and optimize the route

        // For now, just return customers sorted by address
        return $customers->sortBy('address');
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
     * Get type display name.
     */
    public function getTypeDisplayAttribute()
    {
        return ucfirst($this->type);
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
            'planned' => 'text-blue-600',
            'in_progress' => 'text-yellow-600',
            'completed' => 'text-green-600',
        ][$this->status] ?? 'text-gray-600';
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

    /**
     * Get total number of stops on this route.
     */
    public function getTotalStopsAttribute()
    {
        return count($this->customer_order ?: []);
    }

    /**
     * Get completed stops count.
     */
    public function getCompletedStopsAttribute()
    {
        if ($this->type === 'placement') {
            return $this->flagPlacements()->where('status', 'placed')->count();
        } else {
            return $this->flagPlacements()->where('status', 'removed')->count();
        }
    }

    /**
     * Get route progress percentage.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_stops === 0) {
            return 0;
        }

        return round(($this->completed_stops / $this->total_stops) * 100);
    }

    /**
     * Get estimated duration in minutes.
     */
    public function getEstimatedDurationAttribute()
    {
        // Rough estimate: 10 minutes per stop + 5 minutes travel between stops
        $stops = $this->total_stops;
        return ($stops * 10) + (($stops - 1) * 5);
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

    /**
     * Assign route to a user.
     */
    public function assignTo($userId)
    {
        $this->assigned_user_id = $userId;
        $this->save();
    }

    /**
     * Unassign route from current user.
     */
    public function unassign()
    {
        $this->assigned_user_id = null;
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
}
