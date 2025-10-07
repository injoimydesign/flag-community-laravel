<?php
// app/Http/Controllers/Admin/PlacementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlagPlacement;
use App\Models\Holiday;
use App\Models\User;
use App\Models\Route;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PlacementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of flag placements.
     */
    public function index(Request $request)
    {
        // Check if flag_placements table exists
        if (!Schema::hasTable('flag_placements')) {
            return view('admin.placements.index', [
                'placements' => collect(),
                'holidays' => collect(),
                'stats' => [
                    'total_placements' => 0,
                    'scheduled_placements' => 0,
                    'completed_placements' => 0,
                    'overdue_placements' => 0,
                ]
            ]);
        }

        $query = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subscription.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Holiday filter
        if ($request->filled('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('placement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('placement_date', '<=', $request->date_to);
        }

        $placements = $query->orderBy('placement_date', 'desc')->paginate(20);

        // Get holidays for filter dropdown
        $holidays = collect();
        try {
            if (Schema::hasTable('holidays')) {
                $holidays = Holiday::where('active', true)->orderBy('name')->get();
            }
        } catch (\Exception $e) {
            // Handle case where holidays table doesn't exist
        }

        // Get statistics
        $stats = [
            'total_placements' => FlagPlacement::count(),
            'scheduled_placements' => FlagPlacement::where('status', 'scheduled')->count(),
            'completed_placements' => FlagPlacement::where('status', 'placed')->count(),
            'overdue_placements' => FlagPlacement::where('status', 'scheduled')
                ->where('placement_date', '<', Carbon::now())
                ->count(),
        ];

        return view('admin.placements.index', compact('placements', 'holidays', 'stats'));
    }

    /**
     * Display the specified flag placement.
     */
    public function show(FlagPlacement $placement)
    {
        $placement->load([
            'subscription.user',
            'subscription.items.flagProduct.flagType',
            'holiday',
            'flagProduct.flagType',
            'flagProduct.flagSize',
        ]);

        return view('admin.placements.show', compact('placement'));
    }

    /**
     * Show calendar view of placements.
     */
    public function calendar(Request $request)
    {
        return view('admin.placements.calendar');
    }

    /**
     * Get calendar data for placements.
     */
    public function getCalendarData(Request $request)
    {
        if (!Schema::hasTable('flag_placements')) {
            return response()->json([]);
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $placements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->whereBetween('placement_date', [$start, $end])
            ->get()
            ->map(function ($placement) {
                $color = match($placement->status) {
                    'scheduled' => '#3B82F6', // Blue
                    'placed' => '#10B981',     // Green
                    'skipped' => '#F59E0B',    // Yellow
                    default => '#6B7280'       // Gray
                };

                return [
                    'id' => $placement->id,
                    'title' => $placement->holiday->name . ' - ' . $placement->subscription->user->full_name,
                    'start' => $placement->placement_date->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'url' => route('admin.placements.show', $placement),
                ];
            });

        return response()->json($placements);
    }

    /**
     * Mark a placement as placed.
     */
    public function place(Request $request, FlagPlacement $placement)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($placement->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Only scheduled placements can be marked as placed.');
        }

        $placement->update([
            'status' => 'placed',
            'placed_at' => Carbon::now(),
            'notes' => $request->notes,
        ]);

        // Send notification to customer
        $this->notificationService->sendEmail(
            $placement->subscription->user->email,
            'Flag Placed Successfully',
            "Your {$placement->flagProduct->flagType->name} flag has been placed for {$placement->holiday->name}.",
            'flag-placement-completed',
            ['placement' => $placement]
        );

        return redirect()->back()
            ->with('success', 'Placement marked as completed successfully.');
    }

    /**
     * Mark a placement as removed.
     */
    public function remove(Request $request, FlagPlacement $placement)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($placement->status !== 'placed') {
            return redirect()->back()
                ->with('error', 'Only placed flags can be removed.');
        }

        $placement->update([
            'status' => 'removed',
            'removed_at' => Carbon::now(),
            'notes' => ($placement->notes ? $placement->notes . "\n" : '') . $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Flag removal recorded successfully.');
    }

    /**
     * Skip a placement.
     */
    public function skip(Request $request, FlagPlacement $placement)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($placement->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Only scheduled placements can be skipped.');
        }

        $placement->update([
            'status' => 'skipped',
            'skipped_at' => Carbon::now(),
            'skip_reason' => $request->reason,
        ]);

        // Send notification to customer
        $this->notificationService->sendEmail(
            $placement->subscription->user->email,
            'Flag Placement Update',
            "We were unable to place your flag for {$placement->holiday->name}. Reason: {$request->reason}",
            'flag-placement-skipped',
            ['placement' => $placement, 'reason' => $request->reason]
        );

        return redirect()->back()
            ->with('success', 'Placement skipped successfully.');
    }

    /**
     * Show the form for creating a new placement.
     */
    public function create()
    {
        // Get active subscriptions with user and product info
        $subscriptions = \App\Models\Subscription::with(['user', 'flagProduct'])
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active holidays
        $holidays = \App\Models\Holiday::where('active', true)
            ->orderBy('date')
            ->get();

        return view('admin.placements.create', compact('subscriptions', 'holidays'));
    }

    /**
 * Store a newly created placement in storage.
 * FIXED: Creates ONE placement with multiple holidays associated
 */
public function store(Request $request)
{
    $validatedData = $request->validate([
        'subscription_id' => 'required|exists:subscriptions,id',
        'status' => 'required|in:scheduled,placed,removed,skipped',
        'placement_address' => 'nullable|string|max:255',
        'placement_city' => 'nullable|string|max:255',
        'placement_state' => 'nullable|string|max:255',
        'placement_zip_code' => 'nullable|string|max:10',
        'notes' => 'nullable|string|max:1000',
        'holiday_ids' => 'nullable|array',
        'holiday_ids.*' => 'exists:holidays,id',
    ]);

    // Get the subscription with its items and flag products
    $subscription = \App\Models\Subscription::with(['items.flagProduct', 'user'])
        ->findOrFail($request->subscription_id);

    // Get the flag product ID from subscription
    $flagProductId = null;

    if (isset($subscription->flag_product_id)) {
        $flagProductId = $subscription->flag_product_id;
    } elseif ($subscription->items && $subscription->items->isNotEmpty()) {
        $flagProductId = $subscription->items->first()->flag_product_id;
    } elseif (isset($subscription->flagProduct)) {
        $flagProductId = $subscription->flagProduct->id;
    }

    if (!$flagProductId) {
        return redirect()->back()
            ->with('error', 'Unable to determine flag product for this subscription.')
            ->withInput();
    }

    // If no address provided, use subscription user's address
    $placementAddress = $request->placement_address ?: $subscription->user->address ?? null;
    $placementCity = $request->placement_city ?: $subscription->user->city ?? null;
    $placementState = $request->placement_state ?: $subscription->user->state ?? null;
    $placementZipCode = $request->placement_zip_code ?: $subscription->user->zip_code ?? null;

    // Get selected holidays (or all active if none selected)
    $holidayIds = $request->holiday_ids;
    if (empty($holidayIds)) {
        $holidayIds = \App\Models\Holiday::where('active', true)->pluck('id')->toArray();
    }

    if (empty($holidayIds)) {
        return redirect()->back()
            ->with('error', 'No holidays available. Please create at least one active holiday.')
            ->withInput();
    }

    // Create ONE placement per subscription per address
    // Check if placement already exists for this subscription
    $existingPlacement = \App\Models\FlagPlacement::where([
        'subscription_id' => $request->subscription_id,
        'placement_address' => $placementAddress,
    ])->first();

    if ($existingPlacement) {
        return redirect()->back()
            ->with('error', 'A placement already exists for this subscription at this address.')
            ->withInput();
    }

    // For the placement date, use the earliest holiday date
    $earliestHoliday = \App\Models\Holiday::whereIn('id', $holidayIds)
        ->orderBy('date')
        ->first();

    $currentYear = now()->year;
    $holidayDate = \Carbon\Carbon::parse($earliestHoliday->date)->year($currentYear);
    $placementDate = $holidayDate->copy()->subDays($earliestHoliday->placement_days_before ?? 1);
    $removalDate = $holidayDate->copy()->addDays($earliestHoliday->removal_days_after ?? 1);

    // Create ONE placement
    $placement = \App\Models\FlagPlacement::create([
        'subscription_id' => $request->subscription_id,
        'holiday_id' => $earliestHoliday->id, // Use first holiday as primary
        'flag_product_id' => $flagProductId,
        'placement_date' => $placementDate,
        'removal_date' => $removalDate,
        'status' => $request->status,
        'placement_address' => $placementAddress,
        'placement_city' => $placementCity,
        'placement_state' => $placementState,
        'placement_zip_code' => $placementZipCode,
        'notes' => $request->notes,
    ]);

    // Associate all selected holidays with this placement
    // Store in a JSON field or separate pivot table
    // Option 1: If you have a holiday_ids JSON field in flag_placements
    //if (\Schema::hasColumn('flag_placements', 'holiday_ids')) {
    //      $placement->update([
    //        'holiday_ids' => $holidayIds
    //  ]);
    //}
    // Option 2: If you have a pivot table (recommended)
     $placement->holidays()->sync($holidayIds);

    // Set timestamps based on status
    if ($request->status === 'placed') {
        $placement->update([
            'placed_at' => now(),
            'placed_by' => auth()->id(),
        ]);
    } elseif ($request->status === 'removed') {
        $placement->update([
            'removed_at' => now(),
            'removed_by' => auth()->id(),
        ]);
    } elseif ($request->status === 'skipped') {
        $placement->update([
            'skipped_at' => now(),
        ]);
    }

    $holidayCount = count($holidayIds);
    $holidayNames = \App\Models\Holiday::whereIn('id', $holidayIds)->pluck('name')->implode(', ');

    return redirect()->route('admin.placements.index')
        ->with('success', "Placement created successfully and associated with {$holidayCount} holiday(s): {$holidayNames}");
}

    /**
     * Bulk update placements.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placement_ids' => 'required|array|min:1',
            'placement_ids.*' => 'exists:flag_placements,id',
            'action' => 'required|in:place,skip,reschedule',
            'notes' => 'nullable|string|max:500',
            'new_date' => 'required_if:action,reschedule|nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $placements = FlagPlacement::whereIn('id', $request->placement_ids)->get();
        $updatedCount = 0;

        foreach ($placements as $placement) {
            switch ($request->action) {
                case 'place':
                    if ($placement->status === 'scheduled') {
                        $placement->update([
                            'status' => 'placed',
                            'placed_at' => Carbon::now(),
                            'notes' => $request->notes,
                        ]);
                        $updatedCount++;
                    }
                    break;

                case 'skip':
                    if ($placement->status === 'scheduled') {
                        $placement->update([
                            'status' => 'skipped',
                            'skipped_at' => Carbon::now(),
                            'skip_reason' => $request->notes,
                        ]);
                        $updatedCount++;
                    }
                    break;

                case 'reschedule':
                    if ($placement->status === 'scheduled') {
                        $placement->update([
                            'placement_date' => $request->new_date,
                            'notes' => ($placement->notes ? $placement->notes . "\n" : '') .
                                      "Rescheduled: " . $request->notes,
                        ]);
                        $updatedCount++;
                    }
                    break;
            }
        }

        return redirect()->back()
            ->with('success', "Bulk update completed. {$updatedCount} placements updated.");
    }

    /**
     * Send placement reminders.
     */
    public function sendReminders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days_ahead' => 'required|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $reminderDate = Carbon::now()->addDays($request->days_ahead);

        $placements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->where('status', 'scheduled')
            ->whereDate('placement_date', $reminderDate)
            ->get();

        $sentCount = 0;

        foreach ($placements as $placement) {
            $success = $this->notificationService->sendEmail(
                $placement->subscription->user->email,
                'Upcoming Flag Placement',
                "Your {$placement->holiday->name} flag is scheduled to be placed on {$placement->placement_date->format('F j, Y')}.",
                'flag-placement-reminder',
                ['placement' => $placement]
            );

            if ($success) {
                $sentCount++;
            }
        }

        return redirect()->back()
            ->with('success', "Reminders sent to {$sentCount} customers.");
    }

    /**
 * Remove the specified placement from storage.
 */
public function destroy(FlagPlacement $placement)
{
    try {
        // Store placement details for the success message
        $customerName = $placement->subscription->user->name ?? 'Customer';
        $holidayName = $placement->holiday->name ?? 'Holiday';

        // Delete the placement
        $placement->delete();

        return redirect()->back()
            ->with('success', "Placement for {$customerName} ({$holidayName}) deleted successfully.");

    } catch (\Exception $e) {
        \Log::error('Failed to delete placement: ' . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Failed to delete placement. Please try again.');
    }
}

/**
 * Bulk delete placements.
 */
public function bulkDelete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'placement_ids' => 'required|array|min:1',
        'placement_ids.*' => 'exists:flag_placements,id',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->with('error', 'Invalid placement selection.');
    }

    try {
        $count = FlagPlacement::whereIn('id', $request->placement_ids)->delete();

        return redirect()->back()
            ->with('success', "{$count} placement(s) deleted successfully.");

    } catch (\Exception $e) {
        \Log::error('Failed to bulk delete placements: ' . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Failed to delete placements. Please try again.');
    }
}




    /**
     * Export placements to CSV.
     */
    public function export(Request $request)
    {
        if (!Schema::hasTable('flag_placements')) {
            return redirect()->back()->with('error', 'Placements data not available.');
        }

        $query = FlagPlacement::with([
            'subscription.user',
            'holiday',
            'flagProduct.flagType',
            'flagProduct.flagSize'
        ]);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        if ($request->filled('date_from')) {
            $query->where('placement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('placement_date', '<=', $request->date_to);
        }

        $placements = $query->orderBy('placement_date')->get();

        $filename = 'flag_placements_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($placements) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Customer Name',
                'Customer Email',
                'Customer Address',
                'Holiday',
                'Flag Type',
                'Flag Size',
                'Placement Date',
                'Removal Date',
                'Status',
                'Placed At',
                'Removed At',
                'Notes',
                'Created Date',
            ]);

            // Add data rows
            foreach ($placements as $placement) {
                fputcsv($file, [
                    $placement->subscription->user->full_name,
                    $placement->subscription->user->email,
                    $placement->subscription->user->full_address,
                    $placement->holiday->name,
                    $placement->flagProduct->flagType->name,
                    $placement->flagProduct->flagSize->name ?? 'Standard',
                    $placement->placement_date->format('Y-m-d'),
                    $placement->removal_date?->format('Y-m-d'),
                    ucfirst($placement->status),
                    $placement->placed_at?->format('Y-m-d H:i:s'),
                    $placement->removed_at?->format('Y-m-d H:i:s'),
                    $placement->notes,
                    $placement->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
