<?php
// app/Http/Controllers/Customer/DashboardController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Models\FlagPlacement;
use App\Models\Holiday;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show customer dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get active subscription
        $activeSubscription = $user->activeSubscription()->with(['items.flagProduct.flagType', 'items.flagProduct.flagSize'])->first();
        
        // Get upcoming flag placements
        $upcomingPlacements = collect();
        $pastPlacements = collect();
        
        if ($activeSubscription) {
            $upcomingPlacements = $activeSubscription->getUpcomingPlacements();
            $pastPlacements = $activeSubscription->getPastPlacements()->take(5);
        }
        
        // Get subscription statistics
        $stats = [
            'total_subscriptions' => $user->subscriptions()->count(),
            'flags_placed_this_year' => FlagPlacement::whereHas('subscription', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'placed')->whereYear('placed_at', date('Y'))->count(),
            'next_placement' => $upcomingPlacements->first(),
            'subscription_status' => $activeSubscription ? $activeSubscription->status : 'none',
        ];

        return view('customer.dashboard', compact('activeSubscription', 'upcomingPlacements', 'pastPlacements', 'stats'));
    }

    /**
     * Show subscription details.
     */
    public function subscription()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription()->with(['items.flagProduct.flagType', 'items.flagProduct.flagSize', 'holidays'])->first();
        
        if (!$subscription) {
            return redirect()->route('customer.dashboard')->with('error', 'No active subscription found.');
        }

        $allPlacements = $subscription->flagPlacements()
            ->with(['holiday', 'flagProduct.flagType', 'flagProduct.flagSize'])
            ->orderBy('placement_date', 'desc')
            ->paginate(10);

        return view('customer.subscription', compact('subscription', 'allPlacements'));
    }

    /**
     * Show flag placement history.
     */
    public function placements()
    {
        $user = Auth::user();
        
        $placements = FlagPlacement::whereHas('subscription', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['holiday', 'flagProduct.flagType', 'flagProduct.flagSize', 'subscription'])
        ->orderBy('placement_date', 'desc')
        ->paginate(15);

        return view('customer.placements', compact('placements'));
    }

    /**
     * Update special instructions.
     */
    public function updateInstructions(Request $request)
    {
        $request->validate([
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $subscription = $user->activeSubscription()->first();

        if (!$subscription) {
            return response()->json(['error' => 'No active subscription found.'], 404);
        }

        $subscription->special_instructions = $request->special_instructions;
        $subscription->save();

        return response()->json(['success' => 'Instructions updated successfully.']);
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $subscription = $user->activeSubscription()->first();

        if (!$subscription) {
            return redirect()->route('customer.dashboard')->with('error', 'No active subscription found.');
        }

        if ($subscription->type === 'onetime') {
            return redirect()->route('customer.dashboard')->with('error', 'One-time purchases cannot be cancelled.');
        }

        // Cancel the subscription
        $subscription->cancel($request->reason);

        return redirect()->route('customer.dashboard')->with('success', 'Your subscription has been cancelled. Flags will still be placed for scheduled holidays.');
    }

    /**
     * Renew subscription.
     */
    public function renewSubscription()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription()->first();

        if (!$subscription) {
            return redirect()->route('customer.dashboard')->with('error', 'No active subscription found.');
        }

        if ($subscription->type !== 'annual') {
            return redirect()->route('customer.dashboard')->with('error', 'Only annual subscriptions can be renewed.');
        }

        // Create renewal subscription
        $renewalSubscription = $subscription->renew();

        if ($renewalSubscription) {
            // In a real application, you'd redirect to payment processing
            return redirect()->route('checkout.index', [
                'products' => $renewalSubscription->items->pluck('flag_product_id')->toArray(),
                'subscription_type' => 'annual',
            ])->with('info', 'Please complete payment to activate your renewal.');
        }

        return redirect()->route('customer.dashboard')->with('error', 'Unable to create renewal. Please contact support.');
    }

    /**
     * Update account information.
     */
    public function updateAccount(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        
        // Check if new address is still in service area
        $coordinates = $this->geocodeAddress($request);
        $inServiceArea = \App\Models\ServiceArea::isAddressServed(
            $coordinates['lat'] ?? null,
            $coordinates['lng'] ?? null,
            $request->zip_code
        );

        if (!$inServiceArea) {
            return back()->with('error', 'Your new address is outside our service area. Please contact support for assistance.');
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'latitude' => $coordinates['lat'] ?? $user->latitude,
            'longitude' => $coordinates['lng'] ?? $user->longitude,
        ]);

        return back()->with('success', 'Account information updated successfully.');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => \Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Show account settings page.
     */
    public function account()
    {
        $user = Auth::user();
        return view('customer.account', compact('user'));
    }

    /**
     * Get placement calendar data (AJAX).
     */
    public function getCalendarData(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year', date('Y'));

        $placements = FlagPlacement::whereHas('subscription', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['holiday', 'flagProduct.flagType'])
        ->whereYear('placement_date', $year)
        ->get();

        $events = [];

        foreach ($placements as $placement) {
            $events[] = [
                'title' => $placement->holiday->name,
                'start' => $placement->placement_date->toDateString(),
                'end' => $placement->removal_date->toDateString(),
                'color' => $this->getStatusColor($placement->status),
                'description' => $placement->flagProduct->flagType->name,
                'status' => $placement->status,
            ];
        }

        return response()->json($events);
    }

    /**
     * Get notification preferences.
     */
    public function notifications()
    {
        $user = Auth::user();
        
        // Get recent notifications
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.notifications', compact('notifications'));
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_placement' => 'boolean',
            'email_removal' => 'boolean',
            'email_renewal' => 'boolean',
            'sms_placement' => 'boolean',
            'sms_removal' => 'boolean',
        ]);

        $user = Auth::user();
        
        // In a real application, you'd store these preferences
        // For now, we'll just return success
        
        return back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Download invoice/receipt.
     */
    public function downloadInvoice(Request $request, $subscriptionId)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->findOrFail($subscriptionId);

        // In a real application, you'd generate a PDF invoice
        // For now, we'll just return a placeholder
        
        return response()->json([
            'message' => 'Invoice download would be implemented here',
            'subscription' => $subscription->id,
        ]);
    }

    /**
     * Get status color for calendar events.
     */
    private function getStatusColor($status)
    {
        return [
            'scheduled' => '#3B82F6', // Blue
            'placed' => '#10B981',     // Green
            'removed' => '#6B7280',    // Gray
            'skipped' => '#F59E0B',    // Yellow
        ][$status] ?? '#6B7280';
    }

    /**
     * Geocode address (placeholder).
     */
    private function geocodeAddress(Request $request)
    {
        // In a real application, you'd use Google Maps Geocoding API
        $zipCoordinates = [
            '77801' => ['lat' => 30.6744, 'lng' => -96.3698],
            '77802' => ['lat' => 30.6280, 'lng' => -96.3344],
        ];

        return $zipCoordinates[$request->zip_code] ?? ['lat' => null, 'lng' => null];
    }
}