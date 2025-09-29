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
