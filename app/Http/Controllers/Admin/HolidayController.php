<?php
// app/Http/Controllers/Admin/HolidayController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        // Filter by date range - check if date column exists
        if ($request->filled('date_from')) {
            if (\Schema::hasColumn('holidays', 'date')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
        }

        if ($request->filled('date_to')) {
            if (\Schema::hasColumn('holidays', 'date')) {
                $query->whereDate('date', '<=', $request->date_to);
            }
        }

        // Order by date if column exists, otherwise by sort_order
        if (\Schema::hasColumn('holidays', 'date')) {
            $holidays = $query->orderBy('date')->paginate(20);
        } else {
            $holidays = $query->orderBy('sort_order')->orderBy('name')->paginate(20);
        }

        // Get statistics
        $stats = [
            'total' => Holiday::count(),
            'active' => Holiday::where('active', true)->count(),
            'upcoming' => 0,
            'past' => 0,
        ];

        // Calculate upcoming/past only if date column exists
        if (\Schema::hasColumn('holidays', 'date')) {
            $stats['upcoming'] = Holiday::where('date', '>=', Carbon::now())
                ->where('active', true)
                ->count();
            $stats['past'] = Holiday::where('date', '<', Carbon::now())->count();
        }

        return view('admin.holidays.index', compact('holidays', 'stats'));
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
            'description' => 'nullable|string',
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
            'slug' => Str::slug($request->name),
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
        $holiday->load('placements.subscription.user');

        // Get statistics for this holiday
        $stats = [
            'total_placements' => $holiday->placements()->count(),
            'scheduled_placements' => $holiday->placements()->where('status', 'scheduled')->count(),
            'completed_placements' => $holiday->placements()->where('status', 'placed')->count(),
            'skipped_placements' => $holiday->placements()->where('status', 'skipped')->count(),
        ];

        return view('admin.holidays.show', compact('holiday', 'stats'));
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
            'name' => 'required|string|max:255|unique:holidays,name,' . $holiday->id,
            'description' => 'nullable|string',
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
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'date' => $request->date,
            'recurring' => $request->has('recurring'),
            'placement_days_before' => $request->placement_days_before,
            'removal_days_after' => $request->removal_days_after,
            'active' => $request->has('active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.holidays.show', $holiday)
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        // Check if holiday has placements
        if ($holiday->placements()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete holiday with existing placements. Please remove or reassign placements first.');
        }

        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Generate placements for a holiday.
     */
     /**
  * Generate placements for a holiday.
  *
  * This method should be added to your HolidayController.php
  */
 public function generatePlacements(Holiday $holiday)
 {
     // CRITICAL FIX: Check if holiday has a date before proceeding
     if (!$holiday->date) {
         return redirect()->back()
             ->with('error', 'Cannot generate placements: Holiday has no date set. Please update the holiday first.');
     }

     // Logic to generate placements for all active subscriptions
     // This would create FlagPlacement records for this holiday

     $subscriptionsCount = \App\Models\Subscription::where('status', 'active')->count();

     if ($subscriptionsCount === 0) {
         return redirect()->back()
             ->with('error', 'No active subscriptions found to generate placements.');
     }

     // Create placements for each active subscription
     $created = 0;
     $subscriptions = \App\Models\Subscription::where('status', 'active')->get();

     foreach ($subscriptions as $subscription) {
         // Check if placement already exists
         $exists = \App\Models\FlagPlacement::where('holiday_id', $holiday->id)
             ->where('subscription_id', $subscription->id)
             ->exists();

         if (!$exists) {
             try {
                 \App\Models\FlagPlacement::create([
                     'subscription_id' => $subscription->id,
                     'holiday_id' => $holiday->id,
                     'flag_product_id' => $subscription->flag_product_id,
                     'placement_date' => $holiday->date->copy()->subDays($holiday->placement_days_before ?? 1),
                     'removal_date' => $holiday->date->copy()->addDays($holiday->removal_days_after ?? 1),
                     'status' => 'scheduled',
                 ]);
                 $created++;
             } catch (\Exception $e) {
                 \Log::error("Failed to create placement for subscription {$subscription->id}: " . $e->getMessage());
             }
         }
     }

     return redirect()->back()
         ->with('success', "Generated {$created} placements for {$holiday->name}.");
 }

    /**
     * Export holidays to CSV.
     */
    public function export(Request $request)
    {
        $query = Holiday::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $holidays = $query->orderBy('date')->get();

        $filename = 'holidays_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($holidays) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Date',
                'Recurring',
                'Placement Days Before',
                'Removal Days After',
                'Active',
                'Sort Order',
                'Created At',
            ]);

            // Data rows
            foreach ($holidays as $holiday) {
                fputcsv($file, [
                    $holiday->id,
                    $holiday->name,
                    $holiday->description,
                    $holiday->date->format('Y-m-d'),
                    $holiday->recurring ? 'Yes' : 'No',
                    $holiday->placement_days_before,
                    $holiday->removal_days_after,
                    $holiday->active ? 'Active' : 'Inactive',
                    $holiday->sort_order,
                    $holiday->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
