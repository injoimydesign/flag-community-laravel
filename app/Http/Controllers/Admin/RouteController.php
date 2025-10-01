<?php
// app/Http/Controllers/Admin/RouteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Holiday;
use App\Models\User;
use App\Models\FlagPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RouteController extends Controller
{
    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        // Check if routes table exists
        if (!Schema::hasTable('routes')) {
            return view('admin.routes.index', [
                'routes' => collect(),
                'holidays' => collect(),
                'drivers' => collect(),
                'stats' => [
                    'total_routes' => 0,
                    'planned_routes' => 0,
                    'in_progress_routes' => 0,
                    'completed_routes' => 0,
                ]
            ]);
        }

        $query = Route::with(['holiday', 'assignedUser']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Holiday filter
        if ($request->filled('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $routes = $query->orderBy('date', 'desc')->paginate(20);

        // Get holidays for filter
        $holidays = collect();
        try {
            if (Schema::hasTable('holidays')) {
                $holidays = Holiday::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            // Handle case where table doesn't exist
        }

        // Get drivers for assignment
        $drivers = User::where('role', 'admin')->orWhere('role', 'driver')->get();

        // Get statistics
        $stats = [
            'total_routes' => Route::count(),
            'planned_routes' => Route::where('status', 'planned')->count(),
            'in_progress_routes' => Route::where('status', 'in_progress')->count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
        ];

        return view('admin.routes.index', compact('routes', 'holidays', 'drivers', 'stats'));
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
     * Store a newly created route in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:placement,removal',
            'holiday_id' => 'required|exists:holidays,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Route::create([
            'name' => $request->name,
            'date' => $request->date,
            'type' => $request->type,
            'holiday_id' => $request->holiday_id,
            'assigned_user_id' => $request->assigned_user_id,
            'customer_order' => [],
            'status' => 'planned',
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route created successfully.');
    }

    /**
     * Display the specified route.
     */
    public function show(Route $route)
    {
        $route->load(['holiday', 'assignedUser']);

        // Get customers on this route
        $customers = $route->customers();

        return view('admin.routes.show', compact('route', 'customers'));
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
     * Update the specified route in storage.
     */
    public function update(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:placement,removal',
            'holiday_id' => 'required|exists:holidays,id',
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
            'date' => $request->date,
            'type' => $request->type,
            'holiday_id' => $request->holiday_id,
            'assigned_user_id' => $request->assigned_user_id,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.routes.show', $route)
            ->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified route from storage.
     */
    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route deleted successfully.');
    }

    /**
     * Generate routes for a holiday.
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'holiday_id' => 'required|exists:holidays,id',
            'placement_date' => 'required|date',
            'removal_date' => 'required|date|after:placement_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $holiday = Holiday::findOrFail($request->holiday_id);

        // Create placement route
        Route::create([
            'name' => $holiday->name . ' - Placement',
            'date' => $request->placement_date,
            'type' => 'placement',
            'holiday_id' => $holiday->id,
            'status' => 'planned',
        ]);

        // Create removal route
        Route::create([
            'name' => $holiday->name . ' - Removal',
            'date' => $request->removal_date,
            'type' => 'removal',
            'holiday_id' => $holiday->id,
            'status' => 'planned',
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Routes generated successfully for ' . $holiday->name);
    }

    /**
     * Start a route (mark as in progress).
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

        // Apply same filters as index
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

            // Header row
            fputcsv($file, [
                'ID',
                'Name',
                'Date',
                'Type',
                'Holiday',
                'Assigned To',
                'Status',
                'Created At',
            ]);

            // Data rows
            foreach ($routes as $route) {
                fputcsv($file, [
                    $route->id,
                    $route->name,
                    $route->date->format('Y-m-d'),
                    ucfirst($route->type),
                    $route->holiday->name ?? 'N/A',
                    $route->assignedUser->name ?? 'Unassigned',
                    ucfirst($route->status),
                    $route->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get route metrics for dashboard.
     */
    public function getMetrics(Request $request)
    {
        $metrics = [
            'total_routes' => Route::count(),
            'planned_routes' => Route::where('status', 'planned')->count(),
            'in_progress_routes' => Route::where('status', 'in_progress')->count(),
            'completed_routes' => Route::where('status', 'completed')->count(),
            'routes_today' => Route::whereDate('date', Carbon::today())->count(),
            'routes_this_week' => Route::whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
        ];

        return response()->json($metrics);
    }
}
