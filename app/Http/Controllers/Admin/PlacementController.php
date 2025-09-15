<?php
// app/Http/Controllers/Admin/PlacementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlagPlacement;
use App\Models\Holiday;
use App\Models\User;
use App\Models\Route;
use Carbon\Carbon;

class PlacementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of flag placements.
     */
    public function index(Request $request)
    {
        $query = FlagPlacement::with([
            'subscription.user', 
            'holiday', 
            'flagProduct.flagType', 
            'flagProduct.flagSize',
            'placedByUser',
            'removedByUser'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('placement_date', Carbon::today());
                    break;
                case 'this_week':
                    $query->whereBetween('placement_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('placement_date', Carbon::now()->month);
                    break;
                case 'overdue':
                    $query->scheduled()->where('placement_date', '<', Carbon::now());
                    break;
                case 'upcoming':
                    $query->scheduled()->where('placement_date', '>=', Carbon::now());
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subscription.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Default to upcoming placements if no filter
        if (!$request->filled('date_range') && !$request->filled('status')) {
            $query->whereBetween('placement_date', [Carbon::now(), Carbon::now()->addDays(14)]);
        }

        $placements = $query->orderBy('placement_date')->paginate(20);

        // Get filter options
        $holidays = Holiday::active()->orderBy('sort_order')->get();
        $statuses = [
            'scheduled' => 'Scheduled',
            'placed' => 'Placed', 
            'removed' => 'Removed',
            'skipped' => 'Skipped',
        ];

        // Get summary stats
        $stats = [
            'due_today' => FlagPlacement::dueForPlacementToday()->count(),
            'overdue' => FlagPlacement::overduePlacement()->count(),
            'due_removal' => FlagPlacement::dueForRemovalToday()->count(),
            'overdue_removal' => FlagPlacement::overdueRemoval()->count(),
        ];

        return view('admin.placements.index', compact(
            'placements',
            'holidays',
            'statuses',
            'stats'
        ));
    }

    /**
     * Mark placement as placed.
     */
    public function place(Request $request, FlagPlacement $placement)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$placement->isScheduled()) {
            return response()->json([
                'success' => false,
                'message' => 'Placement is not in scheduled status.'
            ], 400);
        }

        $placement->markAsPlaced(auth()->id(), $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Flag marked as placed successfully.',
            'placement' => $placement->load(['subscription.user', 'holiday'])
        ]);
    }

    /**
     * Mark placement as removed.
     */
    public function remove(Request $request, FlagPlacement $placement)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$placement->isPlaced()) {
            return response()->json([
                'success' => false,
                'message' => 'Placement is not in placed status.'
            ], 400);
        }

        $placement->markAsRemoved(auth()->id(), $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Flag marked as removed successfully.',
            'placement' => $placement->load(['subscription.user', 'holiday'])
        ]);
    }

    /**
     * Skip a placement.
     */
    public function skip(Request $request, FlagPlacement $placement)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$placement->isScheduled()) {
            return response()->json([
                'success' => false,
                'message' => 'Only scheduled placements can be skipped.'
            ], 400);
        }

        $placement->markAsSkipped($request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Placement skipped successfully.',
            'placement' => $placement->load(['subscription.user', 'holiday'])
        ]);
    }

    /**
     * Bulk update placements.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'placement_ids' => 'required|array',
            'placement_ids.*' => 'exists:flag_placements,id',
            'action' => 'required|in:place,remove,skip,reschedule',
            'notes' => 'nullable|string|max:500',
            'reason' => 'required_if:action,skip|string|max:500',
            'new_date' => 'required_if:action,reschedule|date|after:today',
        ]);

        $placements = FlagPlacement::whereIn('id', $request->placement_ids)->get();
        $updated = 0;
        $errors = [];

        foreach ($placements as $placement) {
            try {
                switch ($request->action) {
                    case 'place':
                        if ($placement->isScheduled()) {
                            $placement->markAsPlaced(auth()->id(), $request->notes);
                            $updated++;
                        } else {
                            $errors[] = "Placement #{$placement->id} is not scheduled";
                        }
                        break;
                        
                    case 'remove':
                        if ($placement->isPlaced()) {
                            $placement->markAsRemoved(auth()->id(), $request->notes);
                            $updated++;
                        } else {
                            $errors[] = "Placement #{$placement->id} is not placed";
                        }
                        break;
                        
                    case 'skip':
                        if ($placement->isScheduled()) {
                            $placement->markAsSkipped($request->reason);
                            $updated++;
                        } else {
                            $errors[] = "Placement #{$placement->id} cannot be skipped";
                        }
                        break;
                        
                    case 'reschedule':
                        if ($placement->isScheduled()) {
                            $placement->update([
                                'placement_date' => Carbon::parse($request->new_date),
                                'removal_date' => Carbon::parse($request->new_date)->addDays(3),
                            ]);
                            $updated++;
                        } else {
                            $errors[] = "Placement #{$placement->id} cannot be rescheduled";
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "Error updating placement #{$placement->id}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "{$updated} placements updated successfully."
        ]);
    }

    /**
     * Generate placement calendar view.
     */
    public function calendar(Request $request)
    {
        $start = Carbon::parse($request->get('start', Carbon::now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', Carbon::now()->endOfMonth()));

        $placements = FlagPlacement::with(['subscription.user', 'holiday', 'flagProduct.flagType'])
            ->whereBetween('placement_date', [$start, $end])
            ->orWhereBetween('removal_date', [$start, $end])
            ->get();

        $events = [];

        foreach ($placements as $placement) {
            // Placement event
            $events[] = [
                'id' => 'placement-' . $placement->id,
                'title' => $placement->holiday->name . ' - ' . $placement->subscription->user->full_name,
                'start' => $placement->placement_date->toDateString(),
                'color' => $this->getStatusColor($placement->status),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'placement',
                    'placement_id' => $placement->id,
                    'customer' => $placement->subscription->user->full_name,
                    'flag' => $placement->flagProduct->flagType->name,
                    'status' => $placement->status,
                    'address' => $placement->subscription->user->full_address,
                ]
            ];

            // Removal event (only if placed)
            if ($placement->isPlaced() || $placement->isRemoved()) {
                $events[] = [
                    'id' => 'removal-' . $placement->id,
                    'title' => 'Remove: ' . $placement->subscription->user->full_name,
                    'start' => $placement->removal_date->toDateString(),
                    'color' => $placement->isRemoved() ? '#6B7280' : '#F59E0B',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'removal',
                        'placement_id' => $placement->id,
                        'customer' => $placement->subscription->user->full_name,
                        'flag' => $placement->flagProduct->flagType->name,
                        'status' => $placement->status,
                        'address' => $placement->subscription->user->full_address,
                    ]
                ];
            }
        }

        if ($request->wantsJson()) {
            return response()->json($events);
        }

        return view('admin.placements.calendar', compact('events'));
    }

    /**
     * Show placement details.
     */
    public function show(FlagPlacement $placement)
    {
        $placement->load([
            'subscription.user',
            'subscription.items.flagProduct.flagType',
            'holiday',
            'flagProduct.flagType',
            'flagProduct.flagSize',
            'placedByUser',
            'removedByUser'
        ]);

        return view('admin.placements.show', compact('placement'));
    }

    /**
     * Get route optimization suggestions.
     */
    public function optimizeRoutes(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'holiday_id' => 'required|exists:holidays,id',
            'type' => 'required|in:placement,removal',
        ]);

        $date = Carbon::parse($request->date);
        $holidayId = $request->holiday_id;

        if ($request->type === 'placement') {
            $routes = Route::generatePlacementRoutes($date, $holidayId);
        } else {
            $routes = Route::generateRemovalRoutes($date, $holidayId);
        }

        return response()->json([
            'success' => true,
            'routes' => $routes->load(['assignedUser']),
            'message' => count($routes) . ' routes generated successfully.'
        ]);
    }

    /**
     * Export placements to CSV.
     */
    public function export(Request $request)
    {
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

        $placements = $query->orderBy('placement_date')->get();

        $filename = 'flag_placements_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($placements) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Customer Name',
                'Email',
                'Address',
                'Holiday',
                'Flag Type',
                'Flag Size',
                'Placement Date',
                'Removal Date',
                'Status',
                'Placed At',
                'Removed At',
                'Notes'
            ]);

            // CSV data
            foreach ($placements as $placement) {
                fputcsv($file, [
                    $placement->id,
                    $placement->subscription->user->full_name,
                    $placement->subscription->user->email,
                    $placement->subscription->user->full_address,
                    $placement->holiday->name,
                    $placement->flagProduct->flagType->name,
                    $placement->flagProduct->flagSize->name,
                    $placement->placement_date->format('Y-m-d'),
                    $placement->removal_date->format('Y-m-d'),
                    $placement->status,
                    $placement->placed_at ? $placement->placed_at->format('Y-m-d H:i:s') : '',
                    $placement->removed_at ? $placement->removed_at->format('Y-m-d H:i:s') : '',
                    $placement->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
     * Send placement reminders.
     */
    public function sendReminders(Request $request)
    {
        $request->validate([
            'placement_ids' => 'required|array',
            'placement_ids.*' => 'exists:flag_placements,id',
            'type' => 'required|in:placement,removal',
        ]);

        $placements = FlagPlacement::with(['subscription.user', 'holiday'])
            ->whereIn('id', $request->placement_ids)
            ->get();

        $sent = 0;

        foreach ($placements as $placement) {
            if ($request->type === 'placement' && $placement->isScheduled()) {
                // Send placement reminder
                $placement->sendPlacementNotification();
                $sent++;
            } elseif ($request->type === 'removal' && $placement->isPlaced()) {
                // Send removal reminder
                $placement->sendRemovalNotification();
                $sent++;
            }
        }

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'message' => "{$sent} reminders sent successfully."
        ]);
    }
}