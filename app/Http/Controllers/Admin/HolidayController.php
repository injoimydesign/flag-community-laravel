<?php
// app/Http/Controllers/Admin/HolidayController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\FlagPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $query = Holiday::withCount(['flagPlacements']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        // Year filter
        if ($request->filled('year')) {
            $year = $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('date', $year)
                  ->orWhere('recurring', true);
            });
        }

        // Upcoming filter
        if ($request->filled('upcoming') && $request->upcoming === '1') {
            $query->where(function ($q) {
                $q->where('date', '>=', Carbon::now())
                  ->orWhere('recurring', true);
            });
        }

        $holidays = $query->orderBy('date')->paginate(20);

        // Get years for filter dropdown
        $years = range(date('Y') - 2, date('Y') + 2);

        return view('admin.holidays.index', compact('holidays', 'years'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        return view('admin.holidays.create');
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'recurring' => 'boolean',
            'placement_days_before' => 'required|integer|min:0|max:30',
            'removal_days_after' => 'required|integer|min:0|max:30',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Holiday::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'recurring' => $request->has('recurring'),
            'placement_days_before' => $request->placement_days_before,
            'removal_days_after' => $request->removal_days_after,
            'active' => $request->has('active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    /**
     * Display the specified holiday.
     */
    public function show(Holiday $holiday)
    {
        $holiday->load(['flagPlacements.subscription.user', 'flagPlacements.flagProduct.flagType']);

        // Get placement statistics
        $stats = [
            'total_placements' => $holiday->flagPlacements->count(),
            'scheduled_placements' => $holiday->flagPlacements->where('status', 'scheduled')->count(),
            'completed_placements' => $holiday->flagPlacements->where('status', 'placed')->count(),
            'upcoming_placements' => $holiday->flagPlacements
                ->where('placement_date', '>=', Carbon::now())
                ->where('status', 'scheduled')
                ->count(),
        ];

        // Get next occurrence dates
        $nextOccurrence = $holiday->getNextOccurrence();
        $placementDate = $holiday->getPlacementDate($nextOccurrence);
        $removalDate = $holiday->getRemovalDate($nextOccurrence);

        return view('admin.holidays.show', compact(
            'holiday',
            'stats',
            'nextOccurrence',
            'placementDate',
            'removalDate'
        ));
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'recurring' => 'boolean',
            'placement_days_before' => 'required|integer|min:0|max:30',
            'removal_days_after' => 'required|integer|min:0|max:30',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $holiday->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'recurring' => $request->has('recurring'),
            'placement_days_before' => $request->placement_days_before,
            'removal_days_after' => $request->removal_days_after,
            'active' => $request->has('active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        // Check if holiday has scheduled placements
        $scheduledPlacements = $holiday->flagPlacements()
            ->where('status', 'scheduled')
            ->where('placement_date', '>=', Carbon::now())
            ->count();

        if ($scheduledPlacements > 0) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Cannot delete holiday with scheduled placements.');
        }

        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Toggle active status of holiday.
     */
    public function toggleActive(Holiday $holiday)
    {
        $holiday->update(['active' => !$holiday->active]);

        $status = $holiday->active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Holiday {$status} successfully.",
            'active' => $holiday->active
        ]);
    }

    /**
     * Generate placements for holiday.
     */
    public function generatePlacements(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:' . (date('Y') - 1) . '|max:' . (date('Y') + 2),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $year = $request->year;
        $generatedCount = 0;

        // Get all active subscriptions
        $subscriptions = \App\Models\Subscription::active()
            ->whereHas('holidays', function ($query) use ($holiday) {
                $query->where('holiday_id', $holiday->id);
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            $placementDates = $holiday->getPlacementDatesForYear($year);

            // Skip if placement date is outside subscription period
            if ($placementDates['placement_date'] < $subscription->start_date ||
                $placementDates['placement_date'] > $subscription->end_date) {
                continue;
            }

            foreach ($subscription->items as $item) {
                // Check if placement already exists
                $existingPlacement = FlagPlacement::where([
                    'subscription_id' => $subscription->id,
                    'holiday_id' => $holiday->id,
                    'flag_product_id' => $item->flag_product_id,
                    'placement_date' => $placementDates['placement_date'],
                ])->first();

                if (!$existingPlacement) {
                    FlagPlacement::create([
                        'subscription_id' => $subscription->id,
                        'holiday_id' => $holiday->id,
                        'flag_product_id' => $item->flag_product_id,
                        'placement_date' => $placementDates['placement_date'],
                        'removal_date' => $placementDates['removal_date'],
                        'status' => 'scheduled',
                    ]);

                    $generatedCount++;
                }
            }
        }

        return redirect()->back()
            ->with('success', "Generated {$generatedCount} flag placements for {$holiday->name} in {$year}.");
    }

    /**
     * Export holiday data to CSV.
     */
    public function export(Request $request)
    {
        $query = Holiday::withCount(['flagPlacements']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $holidays = $query->orderBy('date')->get();

        $filename = 'holidays_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($holidays) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Name',
                'Description',
                'Date',
                'Recurring',
                'Placement Days Before',
                'Removal Days After',
                'Active',
                'Total Placements',
                'Sort Order',
                'Created Date',
            ]);

            // Add data rows
            foreach ($holidays as $holiday) {
                fputcsv($file, [
                    $holiday->name,
                    $holiday->description,
                    $holiday->date->format('Y-m-d'),
                    $holiday->recurring ? 'Yes' : 'No',
                    $holiday->placement_days_before,
                    $holiday->removal_days_after,
                    $holiday->active ? 'Yes' : 'No',
                    $holiday->flag_placements_count,
                    $holiday->sort_order,
                    $holiday->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
  }
