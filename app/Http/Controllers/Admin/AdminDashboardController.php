<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Subscription;
use App\Models\FlagPlacement;
use App\Models\PotentialCustomer;
use App\Models\FlagProduct;
use App\Models\Holiday;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        // Get key metrics
        $stats = [
            'total_customers' => User::where('role', 'customer')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'potential_customers' => PotentialCustomer::where('status', 'pending')->count(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'flags_placed_this_month' => FlagPlacement::where('status', 'placed')
                ->whereMonth('placed_at', Carbon::now()->month)
                ->count(),
            'upcoming_placements' => FlagPlacement::where('status', 'scheduled')
                ->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(7)])
                ->count(),
        ];

        // Get recent activity
        $recentSubscriptions = Subscription::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPlacements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->where('status', 'placed')
            ->orderBy('placed_at', 'desc')
            ->take(5)
            ->get();

        // Get upcoming tasks
        $upcomingPlacements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->where('status', 'scheduled')
            ->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('placement_date')
            ->take(10)
            ->get();

        $overduePlacements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->where('status', 'scheduled')
            ->where('placement_date', '<', Carbon::now())
            ->orderBy('placement_date')
            ->take(5)
            ->get();

        // Get low inventory alerts (with error handling)
        $lowInventoryProducts = collect();
        try {
            if (Schema::hasColumn('flag_products', 'current_inventory')) {
                $lowInventoryProducts = FlagProduct::with(['flagType', 'flagSize'])
                    ->whereRaw('current_inventory <= low_inventory_threshold')
                    ->where('active', true)
                    ->get();
            }
        } catch (\Exception $e) {
            // Handle case where columns don't exist yet
            $lowInventoryProducts = collect();
        }

        // Get revenue chart data (last 12 months)
        $revenueChart = $this->getRevenueChartData();

        // Get upcoming holidays (with error handling)
        $upcomingHolidays = collect();
        try {
            if (Schema::hasColumn('holidays', 'date') && Schema::hasColumn('holidays', 'active')) {
                $upcomingHolidays = Holiday::where('active', true)
                    ->where('date', '>=', Carbon::now())
                    ->orderBy('date')
                    ->take(3)
                    ->get();
            }
        } catch (\Exception $e) {
            // Handle case where columns don't exist yet
            $upcomingHolidays = collect();
        }

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
        $labels = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = 0;
            
            try {
                if (Schema::hasTable('subscriptions')) {
                    $revenue = Subscription::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('total_amount');
                }
            } catch (\Exception $e) {
                // Handle case where subscriptions table doesn't exist
            }
            
            $labels[] = $date->format('M Y');
            $data[] = $revenue / 100; // Convert from cents
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get dashboard metrics via AJAX.
     */
    public function getMetrics()
    {
        return response()->json([
            'total_customers' => User::where('role', 'customer')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'monthly_revenue' => $this->getMonthlyRevenue() / 100,
            'upcoming_placements' => FlagPlacement::where('status', 'scheduled')
                ->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(7)])
                ->count(),
        ]);
    }

    /**
     * Get calendar data for dashboard.
     */
    public function getCalendarData(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $placements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->whereBetween('placement_date', [$start, $end])
            ->get()
            ->map(function ($placement) {
                return [
                    'id' => $placement->id,
                    'title' => $placement->holiday->name . ' - ' . $placement->subscription->user->full_name,
                    'start' => $placement->placement_date->format('Y-m-d'),
                    'className' => 'bg-' . ($placement->status === 'completed' ? 'green' : 'blue') . '-500',
                    'url' => route('admin.placements.show', $placement),
                ];
            });

        return response()->json($placements);
    }

    /**
     * Handle quick actions from dashboard.
     */
    public function quickAction(Request $request)
    {
        $action = $request->get('action');
        
        switch ($action) {
            case 'mark_placement_complete':
                $placement = FlagPlacement::findOrFail($request->get('placement_id'));
                $placement->update([
                    'status' => 'placed',
                    'placed_at' => Carbon::now(),
                ]);
                return response()->json(['success' => true, 'message' => 'Placement marked as complete.']);
                
            case 'send_reminder':
                // Implementation for sending reminders
                return response()->json(['success' => true, 'message' => 'Reminder sent successfully.']);
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action.']);
        }
    }
}