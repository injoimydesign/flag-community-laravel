<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\FlagPlacement;
use App\Models\PotentialCustomer;
use App\Models\FlagProduct;
use App\Models\Holiday;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        // Get key metrics
        $stats = [
            'total_customers' => User::where('role', 'customer')->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'potential_customers' => PotentialCustomer::pending()->count(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'flags_placed_this_month' => FlagPlacement::where('status', 'placed')
                ->whereMonth('placed_at', Carbon::now()->month)
                ->count(),
            'upcoming_placements' => FlagPlacement::scheduled()
                ->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(7)])
                ->count(),
        ];

        // Get recent activity
        $recentSubscriptions = Subscription::with(['user', 'items.flagProduct.flagType'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPlacements = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType'])
            ->where('status', 'placed')
            ->orderBy('placed_at', 'desc')
            ->take(5)
            ->get();

        // Get upcoming tasks
        $upcomingPlacements = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType'])
            ->scheduled()
            ->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('placement_date')
            ->take(10)
            ->get();

        $overduePlacements = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType'])
            ->scheduled()
            ->where('placement_date', '<', Carbon::now())
            ->orderBy('placement_date')
            ->take(5)
            ->get();

        // Get low inventory alerts
        $lowInventoryProducts = FlagProduct::with(['flagType', 'flagSize'])
            ->lowInventory()
            ->active()
            ->get();

        // Get revenue chart data (last 12 months)
        $revenueChart = $this->getRevenueChartData();

        // Get upcoming holidays
        $upcomingHolidays = Holiday::upcoming()->take(3)->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentSubscriptions',
            'recentPlacements',
            'upcomingPlacements',
            'overduePlacements',
            'lowInventoryProducts',
            'revenueChart',
            'upcomingHolidays'
        ));
    }

    /**
     * Get monthly revenue for current month.
     */
    private function getMonthlyRevenue()
    {
        return Subscription::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');
    }

    /**
     * Get revenue chart data for the last 12 months.
     */
    private function getRevenueChartData()
    {
        $months = [];
        $revenues = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $revenue = Subscription::where('status', 'active')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
                
            $revenues[] = $revenue;
        }

        return [
            'labels' => $months,
            'data' => $revenues
        ];
    }

    /**
     * Get dashboard metrics for AJAX updates.
     */
    public function getMetrics(Request $request)
    {
        return response()->json([
            'total_customers' => User::where('role', 'customer')->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'potential_customers' => PotentialCustomer::pending()->count(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'flags_placed_today' => FlagPlacement::where('status', 'placed')
                ->whereDate('placed_at', Carbon::today())
                ->count(),
            'pending_placements' => FlagPlacement::scheduled()
                ->where('placement_date', '<=', Carbon::now()->addDays(3))
                ->count(),
        ]);
    }

    /**
     * Get placement calendar data.
     */
    public function getCalendarData(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $placements = FlagPlacement::with(['holiday', 'subscription.user'])
            ->whereBetween('placement_date', [$start, $end])
            ->orWhereBetween('removal_date', [$start, $end])
            ->get();

        $events = [];

        foreach ($placements as $placement) {
            // Placement event
            $events[] = [
                'id' => 'placement-' . $placement->id,
                'title' => "Place: {$placement->holiday->name}",
                'start' => $placement->placement_date->toDateString(),
                'color' => $this->getPlacementColor($placement->status),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'placement',
                    'customer' => $placement->subscription->user->full_name,
                    'status' => $placement->status,
                    'placement_id' => $placement->id,
                ]
            ];

            // Removal event
            $events[] = [
                'id' => 'removal-' . $placement->id,
                'title' => "Remove: {$placement->holiday->name}",
                'start' => $placement->removal_date->toDateString(),
                'color' => $placement->status === 'placed' ? '#6B7280' : '#D1D5DB',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'removal',
                    'customer' => $placement->subscription->user->full_name,
                    'status' => $placement->status,
                    'placement_id' => $placement->id,
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Get placement color based on status.
     */
    private function getPlacementColor($status)
    {
        return [
            'scheduled' => '#3B82F6', // Blue
            'placed' => '#10B981',     // Green
            'removed' => '#6B7280',    // Gray
            'skipped' => '#F59E0B',    // Yellow
        ][$status] ?? '#6B7280';
    }

    /**
     * Quick actions for dashboard.
     */
    public function quickAction(Request $request)
    {
        $action = $request->get('action');
        
        switch ($action) {
            case 'mark_placement_complete':
                $placement = FlagPlacement::findOrFail($request->get('placement_id'));
                $placement->markAsPlaced(auth()->id(), 'Marked as placed from dashboard');
                return response()->json(['success' => true, 'message' => 'Placement marked as complete']);
                
            case 'skip_placement':
                $placement = FlagPlacement::findOrFail($request->get('placement_id'));
                $placement->markAsSkipped($request->get('reason', 'Skipped from dashboard'));
                return response()->json(['success' => true, 'message' => 'Placement skipped']);
                
            case 'contact_customer':
                // In a real application, this would create a notification or log
                return response()->json(['success' => true, 'message' => 'Customer contact logged']);
                
            default:
                return response()->json(['success' => false, 'message' => 'Unknown action']);
        }
    }
}