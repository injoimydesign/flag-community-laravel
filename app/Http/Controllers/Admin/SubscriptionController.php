<?php
// app/Http/Controllers/Admin/SubscriptionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\StripeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    protected StripeService $stripeService;
    protected NotificationService $notificationService;

    public function __construct(StripeService $stripeService, NotificationService $notificationService)
    {
        $this->stripeService = $stripeService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'flagProduct']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->has('flag_product_id') && $request->flag_product_id !== '') {
            $query->where('flag_product_id', $request->flag_product_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'status', 'next_billing_date', 'total_amount'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $subscriptions = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'total_revenue' => Subscription::whereIn('status', ['active', 'cancelled'])->sum('total_amount') / 100,
            'monthly_revenue' => Subscription::where('status', 'active')->sum('total_amount') / 100,
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'flagProduct', 'placements']);

        // Get Stripe subscription details if available
        $stripeSubscription = null;
        if ($subscription->stripe_subscription_id) {
            try {
                $stripeSubscription = $this->stripeService->getSubscription($subscription->stripe_subscription_id);
            } catch (\Exception $e) {
                \Log::error('Failed to fetch Stripe subscription: ' . $e->getMessage());
            }
        }

        return view('admin.subscriptions.show', compact('subscription', 'stripeSubscription'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(Subscription $subscription)
    {
        return view('admin.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified subscription in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:active,pending,cancelled',
            'notes' => 'nullable|string',
            'placement_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subscription->update($request->only(['status', 'notes', 'placement_instructions']));

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        try {
            // Cancel in Stripe if applicable
            if ($subscription->stripe_subscription_id) {
                $this->stripeService->cancelSubscription($subscription->stripe_subscription_id);
            }

            // Update subscription status
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
            ]);

            // Send notification to customer
            $this->notificationService->send(
                $subscription->user,
                'Subscription Cancelled',
                'Your subscription has been cancelled. You will not be billed for the next billing cycle.'
            );

            return redirect()->back()->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to cancel subscription: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate a cancelled subscription.
     */
    public function reactivate(Request $request, Subscription $subscription)
    {
        if ($subscription->status !== 'cancelled') {
            return redirect()->back()->with('error', 'Only cancelled subscriptions can be reactivated.');
        }

        try {
            // Reactivate in Stripe if applicable
            if ($subscription->stripe_subscription_id) {
                $this->stripeService->reactivateSubscription($subscription->stripe_subscription_id);
            }

            // Update subscription status
            $subscription->update([
                'status' => 'active',
                'cancelled_at' => null,
                'next_billing_date' => Carbon::now()->addMonth(),
            ]);

            // Send notification to customer
            $this->notificationService->send(
                $subscription->user,
                'Subscription Reactivated',
                'Your subscription has been reactivated. Your next billing date is ' . $subscription->next_billing_date->format('F j, Y') . '.'
            );

            return redirect()->back()->with('success', 'Subscription reactivated successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to reactivate subscription: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reactivate subscription: ' . $e->getMessage());
        }
    }

    /**
     * Export subscriptions to CSV.
     */
    public function export(Request $request)
    {
        $query = Subscription::with(['user', 'flagProduct']);

        // Apply same filters as index
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->get();

        $filename = 'subscriptions_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($subscriptions) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Customer Name',
                'Customer Email',
                'Address',
                'Flag Product',
                'Status',
                'Total Amount',
                'Billing Frequency',
                'Next Billing Date',
                'Created At',
                'Stripe Subscription ID',
            ]);

            // Data rows
            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->id,
                    $subscription->user->name ?? 'N/A',
                    $subscription->user->email ?? 'N/A',
                    $subscription->user->address ?? 'N/A',
                    $subscription->flagProduct->name ?? 'N/A',
                    $subscription->status,
                    '$' . number_format($subscription->total_amount / 100, 2),
                    $subscription->billing_frequency,
                    $subscription->next_billing_date ? $subscription->next_billing_date->format('Y-m-d') : 'N/A',
                    $subscription->created_at->format('Y-m-d H:i:s'),
                    $subscription->stripe_subscription_id ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get subscription metrics for dashboard/API.
     */
    public function getMetrics(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(12));
        $endDate = $request->get('end_date', Carbon::now());

        $metrics = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'pending_subscriptions' => Subscription::where('status', 'pending')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'total_revenue' => Subscription::whereIn('status', ['active', 'cancelled'])->sum('total_amount') / 100,
            'monthly_recurring_revenue' => Subscription::where('status', 'active')->sum('total_amount') / 100,
            'average_subscription_value' => Subscription::where('status', 'active')->avg('total_amount') / 100,
            'churn_rate' => $this->calculateChurnRate($startDate, $endDate),
            'revenue_by_month' => $this->getRevenueByMonth($startDate, $endDate),
            'subscriptions_by_status' => Subscription::select('status', \DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return response()->json($metrics);
    }

    /**
     * Calculate churn rate.
     */
    private function calculateChurnRate($startDate, $endDate)
    {
        $startCount = Subscription::where('created_at', '<=', $startDate)->count();
        $cancelledCount = Subscription::where('status', 'cancelled')
            ->whereBetween('cancelled_at', [$startDate, $endDate])
            ->count();

        if ($startCount === 0) {
            return 0;
        }

        return round(($cancelledCount / $startCount) * 100, 2);
    }

    /**
     * Get revenue grouped by month.
     */
    private function getRevenueByMonth($startDate, $endDate)
    {
        return Subscription::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) / 100 as revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['active', 'cancelled'])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month');
    }

    /**
     * Remove the specified subscription from storage.
     */
    public function destroy(Subscription $subscription)
    {
        try {
            // Cancel in Stripe first if applicable
            if ($subscription->stripe_subscription_id && $subscription->status === 'active') {
                $this->stripeService->cancelSubscription($subscription->stripe_subscription_id);
            }

            $subscription->delete();

            return redirect()->route('admin.subscriptions.index')
                ->with('success', 'Subscription deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete subscription: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete subscription: ' . $e->getMessage());
        }
    }
}