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
        $route->load(['assignedUser']);

        // Get all holidays
        $holidays = Holiday::active()->ordered()->get();

        // Get selected holiday if provided (for filtering VIEW only)
        $selectedHolidayId = $request->get('holiday_id');

        // Get all customers on this route with their holidays
        $customers = $route->customers();

        // Get customer details with their holidays
        $customersWithHolidays = $customers->map(function ($customer) use ($selectedHolidayId) {
            // Get active subscription for this customer
            $subscription = Subscription::where('user_id', $customer->id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return null;
            }

            // Get all holidays for this customer
            // Check if using pivot table or direct relationship
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
                'user' => $customer,
                'holidays' => $allHolidays,
                'has_selected_holiday' => $selectedHolidayId ?
                    $allHolidays->contains('id', (int)$selectedHolidayId) : true,
            ];
        })
        ->filter(); // Remove nulls

        // If a holiday is selected, filter VIEW to only show customers with that holiday
        if ($selectedHolidayId) {
            $customersWithHolidays = $customersWithHolidays->filter(function ($item) {
                return $item['has_selected_holiday'];
            });
        }

        return view('admin.routes.show', compact('route', 'customersWithHolidays', 'holidays', 'selectedHolidayId'));
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit(Route $route)
    {
        $holidays = Holiday::active()->ordered()->get();
        $drivers = User::where('role', 'admin')->orWhere('role', 'driver')->get();

        return view('admin.routes.edit', compact('route', 'holidays', 'drivers'));
    }

    /**
     * Update the specified route.
     */
    public function update(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:placement,removal',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $route->update([
            'name' => $request->name,
            'type' => $request->type,
            'assigned_user_id' => $request->assigned_user_id,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

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
     * Add placement to route.
     */
    public function addPlacement(Route $route, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid user'], 400);
        }

        $route->addCustomer($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Placement added to route successfully.',
        ]);
    }

    /**
     * Remove placement from route.
     */
    public function removePlacement(Route $route, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid user'], 400);
        }

        $route->removeCustomer($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Placement removed from route successfully.',
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
     * Get turn-by-turn directions.
     */
    public function getDirections(Route $route, Request $request)
    {
        try {
            $directions = $route->getGoogleMapsDirections();

            return response()->json([
                'success' => true,
                'directions' => $directions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get directions: ' . $e->getMessage()
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
