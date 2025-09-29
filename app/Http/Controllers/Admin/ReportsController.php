<?php
// app/Http/Controllers/Admin/ReportsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\FlagPlacement;
use App\Models\FlagProduct;
use App\Models\PotentialCustomer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        // Get overview statistics
        $stats = [
            'total_revenue' => Subscription::sum('total_amount') / 100,
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'total_customers' => User::where('role', 'customer')->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'flags_placed_ytd' => FlagPlacement::where('status', 'placed')
                ->whereYear('placed_at', Carbon::now()->year)
                ->count(),
            'conversion_rate' => $this->getConversionRate(),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Generate revenue report.
     */
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'month');

        $query = Subscription::whereBetween('created_at', [$startDate, $endDate]);

        // Revenue over time
        switch ($groupBy) {
            case 'day':
                $revenueData = $query->selectRaw('DATE(created_at) as period, SUM(total_amount) as revenue, COUNT(*) as subscriptions')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'week':
                $revenueData = $query->selectRaw('YEARWEEK(created_at) as period, SUM(total_amount) as revenue, COUNT(*) as subscriptions')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'month':
            default:
                $revenueData = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, SUM(total_amount) as revenue, COUNT(*) as subscriptions')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
        }

        // Revenue by subscription type
        $revenueByType = $query->selectRaw('type, SUM(total_amount) as revenue, COUNT(*) as subscriptions')
            ->groupBy('type')
            ->get();

        // Top customers by revenue
        $topCustomers = User::whereHas('subscriptions', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->withSum(['subscriptions as total_spent' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_amount')
            ->orderBy('total_spent', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.revenue', compact(
            'revenueData',
            'revenueByType',
            'topCustomers',
            'startDate',
            'endDate',
            'groupBy'
        ));
    }

    /**
     * Generate customer report.
     */
    public function customers(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Customer acquisition over time
        $customerData = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Customer demographics
        $customersByState = User::where('role', 'customer')
            ->selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->get();

        // Service area coverage
        $serviceAreaStats = [
            'served' => User::where('role', 'customer')->where('in_service_area', true)->count(),
            'not_served' => User::where('role', 'customer')->where('in_service_area', false)->count(),
        ];

        // Customer lifetime value
        $lifetimeValue = User::where('role', 'customer')
            ->withSum('subscriptions', 'total_amount')
            ->get()
            ->avg('subscriptions_sum_total_amount') / 100;

        return view('admin.reports.customers', compact(
            'customerData',
            'customersByState',
            'serviceAreaStats',
            'lifetimeValue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate operations report.
     */
    public function operations(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Flag placements over time
        $placementData = FlagPlacement::whereBetween('placement_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(placement_date, "%Y-%m") as period, COUNT(*) as placements')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Placement efficiency
        $placementStats = [
            'total_scheduled' => FlagPlacement::whereBetween('placement_date', [$startDate, $endDate])->count(),
            'completed_on_time' => FlagPlacement::whereBetween('placement_date', [$startDate, $endDate])
                ->where('status', 'placed')
                ->whereRaw('DATE(placed_at) <= placement_date')
                ->count(),
            'average_completion_time' => $this->getAverageCompletionTime($startDate, $endDate),
        ];

        // Most popular flag products
        $popularProducts = FlagProduct::withCount(['subscriptionItems as usage_count'])
            ->orderBy('usage_count', 'desc')
            ->take(10)
            ->get();

        // Inventory status
        $inventoryStats = [
            'total_products' => FlagProduct::count(),
            'low_inventory' => FlagProduct::whereRaw('current_inventory <= low_inventory_threshold')->count(),
            'out_of_stock' => FlagProduct::where('current_inventory', 0)->count(),
            'total_inventory_value' => FlagProduct::selectRaw('SUM(current_inventory * cost_per_unit)')->value('SUM(current_inventory * cost_per_unit)') / 100,
        ];

        return view('admin.reports.operations', compact(
            'placementData',
            'placementStats',
            'popularProducts',
            'inventoryStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate marketing report.
     */
    public function marketing(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Lead generation over time
        $leadData = PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as leads')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Conversion funnel
        $conversionStats = [
            'total_leads' => PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'contacted_leads' => PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'contacted')->count(),
            'converted_leads' => PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'converted')->count(),
            'conversion_rate' => $this->getConversionRate($startDate, $endDate),
        ];

        // Geographic distribution of leads
        $leadsByState = PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->get();

        // Service area expansion opportunities
        $expansionOpportunities = PotentialCustomer::where('in_service_area', false)
            ->selectRaw('state, city, COUNT(*) as potential_customers')
            ->groupBy('state', 'city')
            ->orderBy('potential_customers', 'desc')
            ->take(20)
            ->get();

        return view('admin.reports.marketing', compact(
            'leadData',
            'conversionStats',
            'leadsByState',
            'expansionOpportunities',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export report data.
     */
    public function export(Request $request)
    {
        $reportType = $request->get('type', 'revenue');
        $startDate = $request->get('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        switch ($reportType) {
            case 'revenue':
                return $this->exportRevenueReport($startDate, $endDate);
            case 'customers':
                return $this->exportCustomersReport($startDate, $endDate);
            case 'operations':
                return $this->exportOperationsReport($startDate, $endDate);
            case 'marketing':
                return $this->exportMarketingReport($startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }

    /**
     * Get monthly revenue for current month.
     */
    protected function getMonthlyRevenue(): float
    {
        return Subscription::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount') / 100;
    }

    /**
     * Get conversion rate.
     */
    protected function getConversionRate(string $startDate = null, string $endDate = null): float
    {
        $query = PotentialCustomer::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = $query->count();
        $converted = $query->where('status', 'converted')->count();

        return $total > 0 ? round(($converted / $total) * 100, 2) : 0;
    }

    /**
     * Get average completion time for flag placements.
     */
    protected function getAverageCompletionTime(string $startDate, string $endDate): float
    {
        $placements = FlagPlacement::whereBetween('placement_date', [$startDate, $endDate])
            ->where('status', 'placed')
            ->whereNotNull('placed_at')
            ->get();

        if ($placements->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($placements as $placement) {
            $scheduledTime = Carbon::parse($placement->placement_date . ' 08:00:00'); // Assume 8 AM start
            $completedTime = $placement->placed_at;

            $hoursDelay = $scheduledTime->diffInHours($completedTime, false);
            $totalHours += max(0, $hoursDelay); // Only count delays, not early completions
            $count++;
        }

        return $count > 0 ? round($totalHours / $count, 2) : 0;
    }

    /**
     * Export revenue report to CSV.
     */
    protected function exportRevenueReport(string $startDate, string $endDate)
    {
        $subscriptions = Subscription::with(['user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'revenue_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($subscriptions) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Customer Name',
                'Customer Email',
                'Subscription Type',
                'Amount',
                'Status',
                'Payment Method',
                'State',
                'City',
            ]);

            // Add data rows
            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->created_at->format('Y-m-d'),
                    $subscription->user->full_name,
                    $subscription->user->email,
                    ucfirst($subscription->type),
                    ' . number_format($subscription->total_amount / 100, 2),
                    ucfirst($subscription->status),
                    'Stripe', // Assuming Stripe payments
                    $subscription->user->state,
                    $subscription->user->city,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customers report to CSV.
     */
    protected function exportCustomersReport(string $startDate, string $endDate)
    {
        $customers = User::where('role', 'customer')
            ->with(['subscriptions'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'customers_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Registration Date',
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'ZIP Code',
                'In Service Area',
                'Total Subscriptions',
                'Total Spent',
                'Last Subscription Date',
            ]);

            // Add data rows
            foreach ($customers as $customer) {
                $totalSpent = $customer->subscriptions->sum('total_amount') / 100;
                $lastSubscription = $customer->subscriptions->sortByDesc('created_at')->first();

                fputcsv($file, [
                    $customer->created_at->format('Y-m-d'),
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->address,
                    $customer->city,
                    $customer->state,
                    $customer->zip_code,
                    $customer->in_service_area ? 'Yes' : 'No',
                    $customer->subscriptions->count(),
                    ' . number_format($totalSpent, 2),
                    $lastSubscription?->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export operations report to CSV.
     */
    protected function exportOperationsReport(string $startDate, string $endDate)
    {
        $placements = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType'])
            ->whereBetween('placement_date', [$startDate, $endDate])
            ->orderBy('placement_date', 'desc')
            ->get();

        $filename = 'operations_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($placements) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Placement Date',
                'Customer Name',
                'Customer Address',
                'Holiday',
                'Flag Type',
                'Status',
                'Placed At',
                'Removal Date',
                'Days Until Placement',
                'Completion Status',
            ]);

            // Add data rows
            foreach ($placements as $placement) {
                $daysUntilPlacement = Carbon::now()->diffInDays($placement->placement_date, false);
                $completionStatus = 'Pending';

                if ($placement->status === 'placed') {
                    $completionStatus = $placement->placed_at <= $placement->placement_date ? 'On Time' : 'Late';
                } elseif ($placement->status === 'skipped') {
                    $completionStatus = 'Skipped';
                }

                fputcsv($file, [
                    $placement->placement_date->format('Y-m-d'),
                    $placement->subscription->user->full_name,
                    $placement->subscription->user->full_address,
                    $placement->holiday->name,
                    $placement->flagProduct->flagType->name,
                    ucfirst($placement->status),
                    $placement->placed_at?->format('Y-m-d H:i:s'),
                    $placement->removal_date?->format('Y-m-d'),
                    $daysUntilPlacement,
                    $completionStatus,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export marketing report to CSV.
     */
    protected function exportMarketingReport(string $startDate, string $endDate)
    {
        $potentialCustomers = PotentialCustomer::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'marketing_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($potentialCustomers) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Lead Date',
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'ZIP Code',
                'In Service Area',
                'Status',
                'Contacted Date',
                'Converted Date',
                'Days to Conversion',
                'Contact Notes',
            ]);

            // Add data rows
            foreach ($potentialCustomers as $customer) {
                $daysToConversion = '';
                if ($customer->converted_at) {
                    $daysToConversion = $customer->created_at->diffInDays($customer->converted_at);
                }

                fputcsv($file, [
                    $customer->created_at->format('Y-m-d'),
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->address,
                    $customer->city,
                    $customer->state,
                    $customer->zip_code,
                    $customer->in_service_area ? 'Yes' : 'No',
                    ucfirst($customer->status),
                    $customer->contacted_at?->format('Y-m-d'),
                    $customer->converted_at?->format('Y-m-d'),
                    $daysToConversion,
                    $customer->contact_notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get financial metrics for dashboard widgets.
     */
    public function getFinancialMetrics(Request $request)
    {
        $period = $request->get('period', '12months');

        switch ($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                $dateFormat = 'M j';
                $groupBy = 'DATE(created_at)';
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                $dateFormat = 'M j';
                $groupBy = 'DATE(created_at)';
                break;
            case '12months':
            default:
                $startDate = Carbon::now()->subMonths(12);
                $dateFormat = 'M Y';
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                break;
        }

        // Revenue trend
        $revenueTrend = Subscription::where('created_at', '>=', $startDate)
            ->selectRaw("{$groupBy} as period, SUM(total_amount) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) use ($dateFormat) {
                return [
                    'period' => Carbon::parse($item->period)->format($dateFormat),
                    'revenue' => $item->revenue / 100,
                ];
            });

        // Key metrics
        $currentPeriodRevenue = Subscription::where('created_at', '>=', $startDate)->sum('total_amount') / 100;
        $previousPeriodStart = match($period) {
            '7days' => Carbon::now()->subDays(14),
            '30days' => Carbon::now()->subDays(60),
            '12months' => Carbon::now()->subMonths(24),
            default => Carbon::now()->subMonths(24),
        };
        $previousPeriodEnd = match($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '12months' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subMonths(12),
        };

        $previousPeriodRevenue = Subscription::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('total_amount') / 100;

        $revenueGrowth = $previousPeriodRevenue > 0
            ? round((($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1)
            : 100;

        return response()->json([
            'revenue_trend' => $revenueTrend,
            'current_revenue' => $currentPeriodRevenue,
            'revenue_growth' => $revenueGrowth,
            'average_order_value' => $this->getAverageOrderValue($startDate),
            'customer_lifetime_value' => $this->getCustomerLifetimeValue(),
        ]);
    }

    /**
     * Get average order value.
     */
    protected function getAverageOrderValue(Carbon $startDate): float
    {
        $subscriptions = Subscription::where('created_at', '>=', $startDate)->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        return round($subscriptions->avg('total_amount') / 100, 2);
    }

    /**
     * Get customer lifetime value.
     */
    protected function getCustomerLifetimeValue(): float
    {
        $customers = User::where('role', 'customer')
            ->withSum('subscriptions', 'total_amount')
            ->get();

        if ($customers->isEmpty()) {
            return 0;
        }

        return round($customers->avg('subscriptions_sum_total_amount') / 100, 2);
    }
}
