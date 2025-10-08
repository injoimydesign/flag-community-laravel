<?php
// app/Http/Controllers/Admin/RouteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Holiday;
use App\Models\User;
use App\Models\FlagPlacement;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RouteController extends Controller
{
    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        $query = Route::with(['holiday', 'assignedUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by holiday
        if ($request->filled('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $routes = $query->orderBy('date', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total_routes' => Route::count(),
            'planned_routes' => Route::where('status', 'planned')->count(),
            'in_progress_routes' => Route::where('status', 'in_progress')->count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
        ];

        $holidays = Holiday::active()->ordered()->get();

        return view('admin.routes.index', compact('routes', 'stats', 'holidays'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create()
    {
        $holidays = Holiday::active()->ordered()->get();
        $drivers = User::where('role', 'admin')->orWhere('role', 'driver')->get();

        return view('admin.routes.create', compact('holidays', 'drivers'));
    }

    /**
     * Store a newly created route (universal for all holidays).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:placement,removal',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $route = Route::create([
            'name' => $request->name,
            'type' => $request->type,
            'assigned_user_id' => $request->assigned_user_id,
            'customer_order' => [],
            'status' => 'planned',
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Route created successfully. Add placements to get started.');
    }

    /**
     * Display the specified route with optional holiday filter for viewing.
     * Customers are added to routes WITHOUT being tied to a specific holiday.
     * The holiday filter is only for VIEWING the list.
     * UPDATED: Works with pivot table for multiple holidays per placement
     */
     public function show(Route $route, Request $request)
 {
     $selectedHolidayId = $request->query('holiday_id');

     // Get customers on this route
     $customers = $route->customers();

     // Build customer data with placements
     $customersWithHolidays = $customers->map(function ($user) use ($route, $selectedHolidayId) {
         if (!$user) return null;

         $subscription = $user->activeSubscription()->first();
         if (!$subscription) return null;

         // Get placements for this customer on this route
         $placementsQuery = FlagPlacement::where('subscription_id', $subscription->id)
             ->where('route_id', $route->id)
             ->with(['holiday', 'flagProduct.flagType', 'flagProduct.flagSize']);

         // Filter by holiday if specified
         if ($selectedHolidayId) {
             $placementsQuery->where('holiday_id', $selectedHolidayId);
         }

         $placements = $placementsQuery->get();

         // Skip if no placements (especially when filtering by holiday)
         if ($placements->isEmpty()) return null;

         // Get unique holidays for this customer's placements
         $holidays = $placements->map(function ($placement) {
             return [
                 'id' => $placement->holiday->id ?? null,
                 'name' => $placement->holiday->name ?? 'N/A',
                 'date' => $placement->holiday->date ? $placement->holiday->date->format('M d, Y') : null,
             ];
         })->unique('id')->values();

         return [
             'user' => $user,
             'subscription' => $subscription,
             'placements' => $placements,
             'holidays' => $holidays,
         ];
     })->filter()->values();

     // Calculate flag counts
     $flagCounts = [
         'us' => 0,
         'military' => 0,
         'total' => 0,
     ];

     foreach ($customersWithHolidays as $item) {
         foreach ($item['placements'] as $placement) {
             $flagType = $placement->flagProduct->flagType->name ?? '';

             if (stripos($flagType, 'US') !== false || stripos($flagType, 'American') !== false) {
                 $flagCounts['us']++;
             } elseif (stripos($flagType, 'Military') !== false ||
                       stripos($flagType, 'Army') !== false ||
                       stripos($flagType, 'Navy') !== false ||
                       stripos($flagType, 'Marines') !== false ||
                       stripos($flagType, 'Air Force') !== false) {
                 $flagCounts['military']++;
             }
             $flagCounts['total']++;
         }
     }

     // Calculate estimated time (rough estimate: 10 minutes per stop + 5 minutes per flag)
     $totalStops = $customersWithHolidays->count();
     $totalFlags = $flagCounts['total'];
     $estimatedMinutes = ($totalStops * 10) + ($totalFlags * 5);
     $estimatedTime = $estimatedMinutes > 60
         ? round($estimatedMinutes / 60, 1) . ' hrs'
         : $estimatedMinutes . ' min';

     // Get all holidays for filter dropdown
     //$holidays = Holiday::where('is_active', true)
      //   ->orderBy('date')
      //   ->get();

     return view('admin.routes.show', compact(
         'route',
         'customersWithHolidays',
        // 'holidays',
         'selectedHolidayId',
         'flagCounts',
         'estimatedTime'
     ));
 }

    /**
     * Show the form for editing the specified route.
     */
     public function edit(Route $route)
     {
         // Get all users who can be assigned to routes (admin/staff users)
         // Using first_name for ordering since User model uses first_name/last_name
         $users = User::where('role', 'admin')
             ->orWhere('role', 'staff')
             ->orderBy('first_name')
             ->orderBy('last_name')
             ->get();

         // Alternative: If you don't have role-based filtering, get all non-customer users
         // $users = User::where('role', '!=', 'customer')
         //     ->orderBy('first_name')
         //     ->orderBy('last_name')
         //     ->get();

         // Or if you want to get ALL users:
         // $users = User::orderBy('first_name')->orderBy('last_name')->get();

         return view('admin.routes.edit', compact('route', 'users'));
     }

    /**
 * Update the specified route in storage.
 */
public function update(Request $request, Route $route)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'type' => 'required|in:placement,removal,delivery',
        'assigned_user_id' => 'nullable|exists:users,id',
        'status' => 'required|in:planned,in_progress,completed,cancelled',
        'notes' => 'nullable|string',
        'customer_order' => 'nullable|json', // Accept customer order as JSON
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $data = [
        'name' => $request->name,
        'type' => $request->type,
        'assigned_user_id' => $request->assigned_user_id,
        'status' => $request->status,
        'notes' => $request->notes,
    ];

    // Handle customer order if provided
    if ($request->has('customer_order')) {
        $customerOrder = json_decode($request->customer_order, true);

        // Validate that all customer IDs exist
        if (is_array($customerOrder)) {
            $validCustomers = User::whereIn('id', $customerOrder)
                ->where('role', 'customer')
                ->pluck('id')
                ->toArray();

            // Only save valid customer IDs
            $data['customer_order'] = array_values(array_intersect($customerOrder, $validCustomers));
        }
    }

    // Get old customer order for comparison
    $oldCustomerOrder = $route->customer_order ?? [];

    $route->update($data);

    // Sync placements if customer_order changed
    if (isset($data['customer_order'])) {
        $newCustomerOrder = $data['customer_order'];

        // Find removed customers
        $removedCustomers = array_diff($oldCustomerOrder, $newCustomerOrder);
        foreach ($removedCustomers as $userId) {
            $user = User::find($userId);
            if ($user) {
                $subscription = $user->activeSubscription()->first();
                if ($subscription) {
                    // Unlink placements
                    FlagPlacement::where('subscription_id', $subscription->id)
                        ->where('route_id', $route->id)
                        ->update(['route_id' => null]);
                }
            }
        }

        // Find added customers
        $addedCustomers = array_diff($newCustomerOrder, $oldCustomerOrder);
        foreach ($addedCustomers as $userId) {
            $user = User::find($userId);
            if ($user) {
                $subscription = $user->activeSubscription()->first();
                if ($subscription) {
                    // Link placements based on route type
                    $query = FlagPlacement::where('subscription_id', $subscription->id)
                        ->where('status', 'scheduled');

                    if ($route->type === 'placement') {
                        $query->whereNotNull('placement_date');
                    } elseif ($route->type === 'removal') {
                        $query->whereNotNull('removal_date');
                    }

                    $query->update(['route_id' => $route->id]);
                }
            }
        }
    }

    return redirect()->route('admin.routes.show', $route)
        ->with('success', 'Route updated successfully.');
}

    /**
     * Remove the specified route.
     */
    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route deleted successfully.');
    }

    /**
     * Get available placements for a route.
     * Shows one address per customer with all their holidays.
     * NOT filtered by holiday - shows ALL available customers.
     * UPDATED: Works with pivot table for multiple holidays per placement
     */
    public function getAvailablePlacements(Route $route, Request $request)
    {
        try {
            // Get customers already on this route
            $existingCustomerIds = $route->customer_order ?: [];

            // Get all active subscriptions with placements, excluding customers already on route
            $subscriptions = Subscription::with(['user'])
                ->where('status', 'active')
                ->whereHas('user', function ($query) use ($existingCustomerIds) {
                    if (!empty($existingCustomerIds)) {
                        $query->whereNotIn('id', $existingCustomerIds);
                    }
                })
                ->get();

            // Group placements by customer
            $availableCustomers = $subscriptions->map(function ($subscription) use ($route) {
                $user = $subscription->user;

                if (!$user) {
                    return null;
                }

                // Get all placements for this subscription
                $placements = FlagPlacement::where('subscription_id', $subscription->id)
                    ->where('status', 'scheduled')
                    ->with(['holiday', 'holidays']) // Load both single and multiple holidays
                    ->get();

                $allHolidays = collect();

                foreach ($placements as $placement) {
                    // Check if using pivot table (many-to-many)
                    if (method_exists($placement, 'holidays') && $placement->holidays()->exists()) {
                        // Using pivot table
                        $holidaysFromPivot = $placement->holidays->map(function ($holiday) use ($placement) {
                            return [
                                'id' => $holiday->id,
                                'name' => $holiday->name,
                                'date' => $holiday->date->format('M d, Y'),
                                'placement_date' => $placement->placement_date ? $placement->placement_date->format('M d, Y') : null,
                                'removal_date' => $placement->removal_date ? $placement->removal_date->format('M d, Y') : null,
                            ];
                        });
                        $allHolidays = $allHolidays->merge($holidaysFromPivot);
                    }
                    // Fallback to single holiday_id
                    elseif ($placement->holiday) {
                        $allHolidays->push([
                            'id' => $placement->holiday->id,
                            'name' => $placement->holiday->name,
                            'date' => $placement->holiday->date->format('M d, Y'),
                            'placement_date' => $placement->placement_date ? $placement->placement_date->format('M d, Y') : null,
                            'removal_date' => $placement->removal_date ? $placement->removal_date->format('M d, Y') : null,
                        ]);
                    }
                }

                $allHolidays = $allHolidays->unique('id')->values();

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'address' => $user->address . ', ' . $user->city . ', ' . $user->state . ' ' . $user->zip_code,
                    'phone' => $user->phone ?? '',
                    'email' => $user->email ?? '',
                    'holidays' => $allHolidays,
                    'holiday_count' => $allHolidays->count(),
                ];
            })
            ->filter(function ($customer) {
                // Only include customers with holidays and valid user data
                return $customer !== null && $customer['holiday_count'] > 0;
            })
            ->sortBy('name')
            ->values();

            return response()->json($availableCustomers);

        } catch (\Exception $e) {
            \Log::error('Error in getAvailablePlacements: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load available customers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Add placement to route and link placements.
 */
public function addPlacement(Route $route, Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => 'Invalid user'], 400);
    }

    // Add customer to route
    $route->addCustomer($request->user_id);

    // Update all scheduled placements for this customer to link them to this route
    $user = User::find($request->user_id);
    if ($user) {
        $subscription = $user->activeSubscription()->first();
        if ($subscription) {
            // Update placements based on route type
            $query = FlagPlacement::where('subscription_id', $subscription->id)
                ->where('status', 'scheduled');

            // If it's a placement route, only link placement-type placements
            if ($route->type === 'placement') {
                $query->whereNotNull('placement_date');
            } elseif ($route->type === 'removal') {
                $query->whereNotNull('removal_date');
            }

            // Update route_id for matching placements
            $query->update(['route_id' => $route->id]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Customer and their placements added to route successfully.',
    ]);
}

/**
* Remove placement from route and unlink placements.
*/
public function removePlacement(Route $route, Request $request)
{
$validator = Validator::make($request->all(), [
    'user_id' => 'required|exists:users,id',
]);

if ($validator->fails()) {
    return response()->json(['error' => 'Invalid user'], 400);
}

// Remove customer from route
$route->removeCustomer($request->user_id);

// Unlink all placements for this customer from this route
$user = User::find($request->user_id);
if ($user) {
    $subscription = $user->activeSubscription()->first();
    if ($subscription) {
        FlagPlacement::where('subscription_id', $subscription->id)
            ->where('route_id', $route->id)
            ->update(['route_id' => null]);
    }
}

return response()->json([
    'success' => true,
    'message' => 'Customer and their placements removed from route successfully.',
]);
}

    /**
     * Optimize route using Google Maps API.
     */
    public function optimizeRoute(Route $route, Request $request)
    {
        try {
            $optimizedOrder = $route->optimizeWithGoogleMaps();

            return response()->json([
                'success' => true,
                'message' => 'Route optimized successfully.',
                'optimized_order' => $optimizedOrder,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to optimize route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Get turn-by-turn directions with optional starting address.
 */
 /**
  * Get turn-by-turn directions with optional starting address.
  */
 public function getDirections(Route $route, Request $request)
 {
     try {
         $startAddress = $request->query('start', '15531 Gladeridge Dr, Houston, TX');

         \Log::info('Getting directions for route ' . $route->id . ' from: ' . $startAddress);

         $customers = $route->customers();

         if ($customers->isEmpty()) {
             return response()->json([
                 'success' => false,
                 'error' => 'No customers on this route'
             ], 400);
         }

         $apiKey = config('services.google.maps_api_key');

         if (!$apiKey) {
             return response()->json([
                 'success' => false,
                 'error' => 'Google Maps API key not configured'
             ], 500);
         }

         // Get customer addresses
         $addresses = $customers->map(function ($customer) {
             return $customer->full_address;
         })->toArray();

         \Log::info('Customer addresses:', $addresses);

         // Use custom start address as origin
         $origin = $startAddress;
         $destination = end($addresses); // Last customer

         // Remove destination from addresses to use as waypoints
         $waypoints = array_slice($addresses, 0, -1);

         $params = [
             'origin' => $origin,
             'destination' => $destination,
             'key' => $apiKey,
         ];

         // Add waypoints if we have them
         if (!empty($waypoints)) {
             $params['waypoints'] = implode('|', array_map('urlencode', $waypoints));
         }

         \Log::info('Google Maps API request params:', $params);

         $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', $params);

         if (!$response->successful()) {
             throw new \Exception('Google Maps API request failed with status: ' . $response->status());
         }

         $responseData = $response->json();

         if (!isset($responseData['status']) || $responseData['status'] !== 'OK') {
             $errorMessage = $responseData['error_message'] ?? $responseData['status'] ?? 'Unknown error';
             throw new \Exception('Google Maps API error: ' . $errorMessage);
         }

         $routeData = $responseData['routes'][0];
         $legs = $routeData['legs'];

         // Format directions
         $directions = [];
         $totalDistance = 0;
         $totalDuration = 0;

         foreach ($legs as $legIndex => $leg) {
             $totalDistance += $leg['distance']['value'];
             $totalDuration += $leg['duration']['value'];

             // Determine customer for this leg
             if ($legIndex < count($customers)) {
                 $customer = $customers[$legIndex];
                 $customerName = $customer->name;
                 $customerAddress = $customer->full_address;
             } else {
                 $customerName = 'Unknown';
                 $customerAddress = 'Unknown';
             }

             $directions[] = [
                 'stop_number' => $legIndex + 1,
                 'customer_name' => $customerName,
                 'address' => $customerAddress,
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

         return response()->json([
             'success' => true,
             'directions' => [
                 'total_distance' => round($totalDistance / 1609.34, 1) . ' mi',
                 'total_duration' => round($totalDuration / 60) . ' min',
                 'stops' => $directions,
                 'overview_polyline' => $routeData['overview_polyline']['points'] ?? '',
             ],
         ]);

     } catch (\Exception $e) {
         \Log::error('Error getting directions: ' . $e->getMessage());
         \Log::error($e->getTraceAsString());

         return response()->json([
             'success' => false,
             'error' => $e->getMessage()
         ], 500);
     }
 }
    /**
     * Start a route.
     */
    public function start(Route $route)
    {
        if ($route->status !== 'planned') {
            return redirect()->back()
                ->with('error', 'Only planned routes can be started.');
        }

        $route->update(['status' => 'in_progress']);

        return redirect()->back()
            ->with('success', 'Route started successfully.');
    }

    /**
     * Complete a route.
     */
    public function complete(Route $route)
    {
        if ($route->status !== 'in_progress') {
            return redirect()->back()
                ->with('error', 'Only in-progress routes can be completed.');
        }

        $route->update(['status' => 'completed']);

        return redirect()->back()
            ->with('success', 'Route completed successfully.');
    }

    /**
     * Assign a route to a user.
     */
    public function assign(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'assigned_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $route->update(['assigned_user_id' => $request->assigned_user_id]);

        return redirect()->back()
            ->with('success', 'Route assigned successfully.');
    }

    /**
     * Export routes to CSV.
     */
    public function export(Request $request)
    {
        $query = Route::with(['holiday', 'assignedUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $routes = $query->orderBy('date', 'desc')->get();

        $filename = 'routes_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($routes) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Name',
                'Type',
                'Assigned To',
                'Status',
                'Total Stops',
                'Created At',
            ]);

            foreach ($routes as $route) {
                fputcsv($file, [
                    $route->id,
                    $route->name,
                    ucfirst($route->type),
                    $route->assignedUser->name ?? 'Unassigned',
                    ucfirst($route->status),
                    $route->total_stops,
                    $route->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get placements for a specific route (removed - no longer needed).
     */
    // Removed - we now show customers with all their holidays
}
