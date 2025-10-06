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
        $query = Subscription::with(['user', 'items.flagProduct']);

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
       // FIXED: Load items with nested relationships
       $subscription->load([
           'user',
           'items.flagProduct.flagType',
           'items.flagProduct.flagSize',
           'flagPlacements.holiday',
           'flagPlacements.flagProduct.flagType',
           'flagPlacements.flagProduct.flagSize'
       ]);

       // Get Stripe subscription details if available
       $stripeSubscription = null;
       if (!empty($subscription->stripe_subscription_id)) {
           try {
               if (isset($this->stripeService)) {
                   $stripeSubscription = $this->stripeService->getSubscription($subscription->stripe_subscription_id);
               }
           } catch (\Exception $e) {
               \Log::error('Failed to fetch Stripe subscription: ' . $e->getMessage());
           }
       }

       // Get statistics
       $stats = [
           'total_placements' => $subscription->flagPlacements->count(),
           'completed_placements' => $subscription->flagPlacements->where('status', 'placed')->count(),
           'scheduled_placements' => $subscription->flagPlacements->where('status', 'scheduled')->count(),
           'cancelled_placements' => $subscription->flagPlacements->where('status', 'cancelled')->count(),
       ];

       return view('admin.subscriptions.show', compact('subscription', 'stripeSubscription', 'stats'));
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

    // app/Http/Controllers/Admin/SubscriptionController.php
    // ADD these methods to the existing SubscriptionController

    /**
     * Show the form for creating a new subscription (order).
     */
    public function create()
    {
        // Get all customers
        $customers = \App\Models\User::where('role', 'customer')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get active flag products
        $flagProducts = \App\Models\FlagProduct::with(['flagType', 'flagSize'])
            ->where('active', true)
            ->orderBy('flag_type_id')
            ->orderBy('flag_size_id')
            ->get();

        // Get active holidays
        $holidays = \App\Models\Holiday::where('active', true)
            ->orderBy('date')
            ->get();

        // Get service areas for address validation
        $serviceAreas = \App\Models\ServiceArea::where('active', true)->get();

        return view('admin.subscriptions.create', compact(
            'customers',
            'flagProducts',
            'holidays',
            'serviceAreas'
        ));
    }

    /**
   * Store a newly created subscription in storage with placement address.
   */
  public function store(Request $request)
  {
      // Validation rules
      $rules = [
          'flag_product_id' => 'required|exists:flag_products,id',
          'status' => 'required|in:pending,active,cancelled',
          'start_date' => 'required|date',
          'billing_frequency' => 'required|in:monthly,annual',
          'placement_instructions' => 'nullable|string|max:1000',
          'notes' => 'nullable|string|max:1000',
      ];

      // Add validation based on customer type
      if ($request->customer_type === 'existing') {
          $rules['user_id'] = 'required|exists:users,id';
      } else {
          $rules['first_name'] = 'required|string|max:255';
          $rules['last_name'] = 'required|string|max:255';
          $rules['email'] = 'required|email|unique:users,email';
          $rules['phone'] = 'nullable|string|max:20';
          $rules['address'] = 'required|string|max:255';
          $rules['city'] = 'required|string|max:255';
          $rules['state'] = 'required|string|max:255';
          $rules['zip_code'] = 'required|string|max:10';
      }

      $validatedData = $request->validate($rules);

      // Handle customer creation or selection
      if ($request->customer_type === 'new') {
          // Create new customer
          $user = \App\Models\User::create([
              'first_name' => $request->first_name,
              'last_name' => $request->last_name,
              'name' => $request->first_name . ' ' . $request->last_name, // Add full name
              'email' => $request->email,
              'phone' => $request->phone,
              'address' => $request->address,
              'city' => $request->city,
              'state' => $request->state,
              'zip_code' => $request->zip_code,
              'role' => 'customer',
              'password' => \Hash::make(\Str::random(16)), // Random password, user will reset
          ]);

          // Geocode address
          try {
              $coordinates = $this->geocodeAddress($request->address, $request->city, $request->state, $request->zip_code);
              $user->update([
                  'latitude' => $coordinates['lat'] ?? null,
                  'longitude' => $coordinates['lng'] ?? null,
              ]);
          } catch (\Exception $e) {
              \Log::error('Geocoding failed: ' . $e->getMessage());
          }

          $userId = $user->id;
          $placementAddress = $request->address;
          $placementCity = $request->city;
          $placementState = $request->state;
          $placementZipCode = $request->zip_code;
          $placementLatitude = $coordinates['lat'] ?? null;
          $placementLongitude = $coordinates['lng'] ?? null;
      } else {
          $userId = $request->user_id;
          $user = \App\Models\User::findOrFail($userId);
          $placementAddress = $user->address;
          $placementCity = $user->city;
          $placementState = $user->state;
          $placementZipCode = $user->zip_code;
          $placementLatitude = $user->latitude;
          $placementLongitude = $user->longitude;
      }

      // Get flag product
      $flagProduct = \App\Models\FlagProduct::findOrFail($request->flag_product_id);

      // Calculate dates
      $startDate = \Carbon\Carbon::parse($request->start_date);
      $endDate = $request->billing_frequency === 'annual'
          ? $startDate->copy()->addYear()
          : $startDate->copy()->addMonth();

      $nextBillingDate = $request->billing_frequency === 'annual'
          ? $startDate->copy()->addYear()
          : $startDate->copy()->addMonth();

      // Calculate total amount
      $totalAmount = $request->billing_frequency === 'annual'
          ? $flagProduct->annual_subscription_price
          : round($flagProduct->annual_subscription_price / 12);

      // Create subscription
      $subscription = \App\Models\Subscription::create([
          'user_id' => $userId,
          'flag_product_id' => $request->flag_product_id,
          'status' => $request->status,
          'type' => $request->billing_frequency,
          'billing_frequency' => $request->billing_frequency,
          'start_date' => $startDate,
          'end_date' => $endDate,
          'next_billing_date' => $nextBillingDate,
          'total_amount' => $totalAmount,
          'placement_instructions' => $request->placement_instructions,
          'notes' => $request->notes,
      ]);

      // Create subscription item
      \App\Models\SubscriptionItem::create([
          'subscription_id' => $subscription->id,
          'flag_product_id' => $request->flag_product_id,
          'quantity' => 1,
          'unit_price' => $totalAmount / 100, // Convert from cents to dollars
          'total_price' => $totalAmount / 100,
      ]);

      // Create placement using the address for ALL active holidays
      if ($request->has('use_address_as_placement') && $request->use_address_as_placement) {
          // Get ALL active holidays (not just selected ones)
          $holidays = \App\Models\Holiday::where('active', true)->get();

          $currentYear = $startDate->year;
          $endYear = $endDate->year;
          $years = ($currentYear === $endYear) ? [$currentYear] : [$currentYear, $endYear];

          foreach ($years as $year) {
              foreach ($holidays as $holiday) {
                  // Skip if holiday is not active in this year
                  if (method_exists($holiday, 'isActiveInYear') && !$holiday->isActiveInYear($year)) {
                      continue;
                  }

                  // Get placement dates for this holiday
                  if (method_exists($holiday, 'getPlacementDatesForYear')) {
                      $placementDates = $holiday->getPlacementDatesForYear($year);
                  } else {
                      // Fallback if method doesn't exist
                      $holidayDate = \Carbon\Carbon::parse($holiday->date)->year($year);
                      $placementDates = [
                          'placement_date' => $holidayDate->copy()->subDays($holiday->placement_days_before ?? 1),
                          'removal_date' => $holidayDate->copy()->addDays($holiday->removal_days_after ?? 1),
                      ];
                  }

                  // Only create placements for dates within subscription period
                  if ($placementDates['placement_date'] >= $startDate &&
                      $placementDates['placement_date'] <= $endDate) {

                      \App\Models\FlagPlacement::create([
                          'subscription_id' => $subscription->id,
                          'holiday_id' => $holiday->id,
                          'flag_product_id' => $request->flag_product_id,
                          'placement_date' => $placementDates['placement_date'],
                          'removal_date' => $placementDates['removal_date'],
                          'status' => 'scheduled',
                          // Add placement address details - same address for ALL holidays
                          'placement_address' => $placementAddress,
                          'placement_city' => $placementCity,
                          'placement_state' => $placementState,
                          'placement_zip_code' => $placementZipCode,
                          'placement_latitude' => $placementLatitude,
                          'placement_longitude' => $placementLongitude,
                      ]);
                  }
              }
          }
      }

      // Send welcome email if new customer
      if ($request->customer_type === 'new') {
          try {
              // Generate password reset token
              $token = app('auth.password.broker')->createToken($user);

              // Send welcome email with password setup link
              // \Mail::to($user->email)->send(new \App\Mail\CustomerWelcome($user, $token));

              \Log::info('Welcome email queued for new customer: ' . $user->email);
          } catch (\Exception $e) {
              \Log::error('Failed to send welcome email: ' . $e->getMessage());
          }
      }

      return redirect()->route('admin.subscriptions.show', $subscription)
          ->with('success', 'Subscription created successfully. ' .
                 ($request->customer_type === 'new' ? 'Welcome email sent to customer.' : ''));
  }

    /**
     * Geocode address helper method
     */
    private function geocodeAddress($address, $city, $state, $zipCode)
    {
        $fullAddress = trim("$address, $city, $state $zipCode");

        // Here you would implement actual geocoding using Google Maps API or similar
        // For now, returning null values as placeholder
        //
        // Example with Google Maps:
        // $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
        //     'address' => $fullAddress,
        //     'key' => config('services.google.maps_api_key')
        // ]);
        //
        // if ($response->successful() && $response['status'] === 'OK') {
        //     $location = $response['results'][0]['geometry']['location'];
        //     return [
        //         'lat' => $location['lat'],
        //         'lng' => $location['lng']
        //     ];
        // }

        return [
            'lat' => null,
            'lng' => null,
        ];
    }
    /**
     * Generate flag placements for a subscription based on selected holidays.
     */
    private function generatePlacementsForSubscription(\App\Models\Subscription $subscription)
    {
        $holidays = \App\Models\Holiday::whereIn('id', $subscription->selected_holidays ?? [])
            ->where('active', true)
            ->get();

        foreach ($holidays as $holiday) {
            foreach ($subscription->items as $item) {
                // Calculate placement and removal dates
                $placementDate = \Carbon\Carbon::parse($holiday->date)
                    ->subDays($holiday->placement_days_before);

                $removalDate = \Carbon\Carbon::parse($holiday->date)
                    ->addDays($holiday->removal_days_after);

                // Only create placements for future dates
                if ($placementDate->isFuture() || $placementDate->isToday()) {
                    \App\Models\FlagPlacement::create([
                        'subscription_id' => $subscription->id,
                        'flag_product_id' => $item->flag_product_id,
                        'holiday_id' => $holiday->id,
                        'placement_date' => $placementDate,
                        'removal_date' => $removalDate,
                        'status' => 'scheduled',
                    ]);
                }
            }
        }
    }

    /**
     * Export subscriptions to CSV.
     */
    public function export(Request $request)
    {
        $query = Subscription::with(['user', 'items.flagProduct']);

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
