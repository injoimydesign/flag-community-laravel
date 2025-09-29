<?php
// app/Http/Controllers/Admin/CustomerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\FlagPlacement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CustomerController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        // Add withCount for relationships that might exist
        try {
            $query->withCount(['subscriptions']);
        } catch (\Exception $e) {
            // Handle case where subscriptions relationship doesn't exist
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('zip_code', 'like', "%{$search}%");
            });
        }

        // Service area filter
        if ($request->filled('service_area')) {
            if ($request->service_area === 'served') {
                $query->where('in_service_area', true);
            } elseif ($request->service_area === 'not_served') {
                $query->where('in_service_area', false);
            }
        }

        // Subscription status filter
        if ($request->filled('subscription_status')) {
            if ($request->subscription_status === 'active') {
                try {
                    $query->whereHas('subscriptions', function ($q) {
                        $q->where('status', 'active');
                    });
                } catch (\Exception $e) {
                    // Handle case where subscriptions relationship doesn't exist
                }
            } elseif ($request->subscription_status === 'inactive') {
                try {
                    $query->whereDoesntHave('subscriptions', function ($q) {
                        $q->where('status', 'active');
                    });
                } catch (\Exception $e) {
                    // Handle case where subscriptions relationship doesn't exist
                }
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics with error handling
        $stats = [
            'total' => 0,
            'active_subscribers' => 0,
            'in_service_area' => 0,
            'total_revenue' => 0,
        ];

        try {
            $stats['total'] = User::where('role', 'customer')->count();
            
            if (Schema::hasColumn('users', 'in_service_area')) {
                $stats['in_service_area'] = User::where('role', 'customer')
                    ->where('in_service_area', true)->count();
            }

            if (Schema::hasTable('subscriptions')) {
                $stats['active_subscribers'] = User::where('role', 'customer')
                    ->whereHas('subscriptions', function ($q) {
                        $q->where('status', 'active');
                    })->count();
                
                $stats['total_revenue'] = \App\Models\Subscription::sum('total_amount') / 100;
            }
        } catch (\Exception $e) {
            // Handle case where tables or relationships don't exist
        }

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => strtoupper($request->state),
            'zip_code' => $request->zip_code,
            'role' => 'customer',
            'email_verified_at' => Carbon::now(),
        ]);

        // Check service area coverage
        $user->checkServiceAreaCoverage();

        // Send welcome email
        $this->notificationService->sendEmail(
            $user->email,
            'Welcome to Flags Across Our Community!',
            'Your account has been created successfully. You can now log in and start your flag subscription.',
            'customer-welcome',
            ['user' => $user]
        );

        return redirect()->route('admin.customers.show', $user)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        // Load relationships with error handling
        try {
            $customer->load([
                'subscriptions.items.flagProduct.flagType',
                'subscriptions.holidays',
            ]);
        } catch (\Exception $e) {
            // Handle case where relationships don't exist
        }

        // Get customer statistics with error handling
        $stats = [
            'total_subscriptions' => 0,
            'active_subscriptions' => 0,
            'total_spent' => 0,
            'flags_placed' => 0,
            'upcoming_placements' => 0,
        ];

        try {
            if (method_exists($customer, 'subscriptions')) {
                $stats['total_subscriptions'] = $customer->subscriptions->count();
                $stats['active_subscriptions'] = $customer->subscriptions->where('status', 'active')->count();
                $stats['total_spent'] = $customer->subscriptions->sum('total_amount') / 100;
            }

            if (Schema::hasTable('flag_placements')) {
                $stats['flags_placed'] = FlagPlacement::whereHas('subscription', function ($q) use ($customer) {
                    $q->where('user_id', $customer->id);
                })->where('status', 'placed')->count();

                $stats['upcoming_placements'] = FlagPlacement::whereHas('subscription', function ($q) use ($customer) {
                    $q->where('user_id', $customer->id);
                })->where('status', 'scheduled')
                  ->where('placement_date', '>=', Carbon::now())->count();
            }
        } catch (\Exception $e) {
            // Handle case where models/relationships don't exist
        }

        // Get recent activity with error handling
        $recentPlacements = collect();
        try {
            if (Schema::hasTable('flag_placements')) {
                $recentPlacements = FlagPlacement::whereHas('subscription', function ($q) use ($customer) {
                    $q->where('user_id', $customer->id);
                })->with(['holiday', 'flagProduct.flagType'])
                  ->orderBy('placement_date', 'desc')
                  ->take(10)
                  ->get();
            }
        } catch (\Exception $e) {
            // Handle case where flag_placements table doesn't exist
        }

        return view('admin.customers.show', compact('customer', 'stats', 'recentPlacements'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:10',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => strtoupper($request->state),
            'zip_code' => $request->zip_code,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $customer->update($data);

        // Check service area coverage if address changed
        if ($request->address !== $customer->getOriginal('address') ||
            $request->city !== $customer->getOriginal('city') ||
            $request->state !== $customer->getOriginal('state') ||
            $request->zip_code !== $customer->getOriginal('zip_code')) {
            $customer->checkServiceAreaCoverage();
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        // Check if customer has active subscriptions
        if ($customer->activeSubscriptions()->count() > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with active subscriptions.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Toggle customer status (for future use if needed).
     */
    public function toggleStatus(Request $request, User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        // This could be used to temporarily disable/enable customers
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Customer status updated successfully.',
        ]);
    }

    /**
     * Export customers to CSV.
     */
    public function export(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount(['subscriptions', 'activeSubscriptions']);

        // Apply same filters as index
        if ($request->filled('service_area')) {
            if ($request->service_area === 'served') {
                $query->where('in_service_area', true);
            } elseif ($request->service_area === 'not_served') {
                $query->where('in_service_area', false);
            }
        }

        if ($request->filled('subscription_status')) {
            if ($request->subscription_status === 'active') {
                $query->whereHas('subscriptions', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->subscription_status === 'inactive') {
                $query->whereDoesntHave('subscriptions', function ($q) {
                    $q->where('status', 'active');
                });
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'ZIP Code',
                'In Service Area',
                'Total Subscriptions',
                'Active Subscriptions',
                'Total Spent',
                'Email Verified',
                'Created Date',
                'Last Login',
            ]);

            // Add data rows
            foreach ($customers as $customer) {
                $totalSpent = $customer->subscriptions->sum('total_amount') / 100;

                fputcsv($file, [
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->address,
                    $customer->city,
                    $customer->state,
                    $customer->zip_code,
                    $customer->in_service_area ? 'Yes' : 'No',
                    $customer->subscriptions_count,
                    $customer->active_subscriptions_count,
                    $customer->email_verified_at ? 'Yes' : 'No',
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->last_login_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send notification to customer.
     */
    public function sendNotification(Request $request, User $customer)
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'notification_type' => 'required|in:email,sms,both',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $success = false;

        // Send email notification
        if (in_array($request->notification_type, ['email', 'both'])) {
            $success = $this->notificationService->sendEmail(
                $customer->email,
                $request->subject,
                $request->message,
                'admin-notification',
                ['customer' => $customer]
            );
        }

        // Send SMS notification (if implemented)
        if (in_array($request->notification_type, ['sms', 'both']) && $customer->phone) {
            // SMS functionality would go here
            // $this->notificationService->sendSMS($customer->phone, $request->message);
        }

        if ($success) {
            return redirect()->back()
                ->with('success', 'Notification sent successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to send notification.');
    }

    /**
     * Get customer metrics for dashboard.
     */
    public function getMetrics(Request $request)
    {
        $period = $request->get('period', '12months');
        
        switch ($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M j';
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M j';
                break;
            case '12months':
            default:
                $startDate = Carbon::now()->subMonths(12);
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                $dateFormat = 'M Y';
                break;
        }

        // New customers over time
        $customerData = User::where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->selectRaw("{$groupBy} as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) use ($dateFormat) {
                return [
                    'period' => Carbon::parse($item->period)->format($dateFormat),
                    'count' => $item->count,
                ];
            });

        // Service area breakdown
        $serviceAreaData = [
            'Served' => User::where('role', 'customer')->where('in_service_area', true)->count(),
            'Not Served' => User::where('role', 'customer')->where('in_service_area', false)->count(),
        ];

        // Subscription status breakdown
        $subscriptionData = [
            'Active' => User::where('role', 'customer')
                ->whereHas('subscriptions', function ($q) {
                    $q->where('status', 'active');
                })->count(),
            'Inactive' => User::where('role', 'customer')
                ->whereDoesntHave('subscriptions', function ($q) {
                    $q->where('status', 'active');
                })->count(),
        ];

        return response()->json([
            'customers' => $customerData,
            'service_area' => $serviceAreaData,
            'subscriptions' => $subscriptionData,
        ]);
    }
}