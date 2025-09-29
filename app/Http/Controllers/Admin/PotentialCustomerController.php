<?php
// app/Http/Controllers/Admin/PotentialCustomerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PotentialCustomer;
use App\Models\User;
use App\Models\ServiceArea;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PotentialCustomerController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of potential customers.
     */
    public function index(Request $request)
    {
        // Check if potential_customers table exists
        if (!Schema::hasTable('potential_customers')) {
            return view('admin.potential-customers.index', [
                'potentialCustomers' => collect(),
                'stats' => [
                    'total' => 0,
                    'pending' => 0,
                    'contacted' => 0,
                    'converted' => 0,
                    'served_areas' => 0,
                    'unserved_areas' => 0,
                ]
            ]);
        }

        $query = PotentialCustomer::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('zip_code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Service area filter
        if ($request->filled('service_area')) {
            if ($request->service_area === 'served') {
                $query->where('in_service_area', true);
            } elseif ($request->service_area === 'not_served') {
                $query->where('in_service_area', false);
            }
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $potentialCustomers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total' => PotentialCustomer::count(),
            'pending' => PotentialCustomer::where('status', 'pending')->count(),
            'contacted' => PotentialCustomer::where('status', 'contacted')->count(),
            'converted' => PotentialCustomer::where('status', 'converted')->count(),
            'served_areas' => PotentialCustomer::where('in_service_area', true)->count(),
            'unserved_areas' => PotentialCustomer::where('in_service_area', false)->count(),
        ];

        return view('admin.potential-customers.index', compact('potentialCustomers', 'stats'));
    }

    /**
     * Display the specified potential customer.
     */
    public function show(PotentialCustomer $potentialCustomer)
    {
        $potentialCustomer->load('convertedUser');
        
        // Check current service area status
        $serviceAreas = collect();
        try {
            if (Schema::hasTable('service_areas')) {
                $serviceAreas = ServiceArea::where('active', true)->get();
            }
        } catch (\Exception $e) {
            // Handle case where ServiceArea doesn't exist
        }
        
        $currentlyServed = $this->checkServiceArea($potentialCustomer, $serviceAreas);

        return view('admin.potential-customers.show', compact(
            'potentialCustomer', 
            'currentlyServed',
            'serviceAreas'
        ));
    }

    /**
     * Contact a potential customer.
     */
    public function contact(Request $request, PotentialCustomer $potentialCustomer)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'contact_method' => 'required|in:email,phone,both',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Send notification based on contact method
        $success = false;

        if (in_array($request->contact_method, ['email', 'both'])) {
            $success = $this->notificationService->sendEmail(
                $potentialCustomer->email,
                'Service Now Available in Your Area!',
                $request->message,
                'potential-customer-contact'
            );
        }

        if (in_array($request->contact_method, ['phone', 'both']) && $potentialCustomer->phone) {
            // SMS notification would go here
            // $this->notificationService->sendSMS($potentialCustomer->phone, $request->message);
        }

        if ($success) {
            $potentialCustomer->update([
                'status' => 'contacted',
                'contacted_at' => Carbon::now(),
                'contact_notes' => $request->message,
            ]);

            return redirect()->back()
                ->with('success', 'Potential customer contacted successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to contact potential customer.');
    }

    /**
     * Convert potential customer to actual customer.
     */
    public function convert(Request $request, PotentialCustomer $potentialCustomer)
    {
        if ($potentialCustomer->status === 'converted') {
            return redirect()->back()
                ->with('error', 'Customer has already been converted.');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if email already exists
        if (User::where('email', $potentialCustomer->email)->exists()) {
            return redirect()->back()
                ->with('error', 'A user with this email already exists.');
        }

        // Create user account
        $user = User::create([
            'first_name' => $potentialCustomer->first_name,
            'last_name' => $potentialCustomer->last_name,
            'email' => $potentialCustomer->email,
            'password' => Hash::make($request->password),
            'phone' => $potentialCustomer->phone,
            'address' => $potentialCustomer->address,
            'city' => $potentialCustomer->city,
            'state' => $potentialCustomer->state,
            'zip_code' => $potentialCustomer->zip_code,
            'latitude' => $potentialCustomer->latitude,
            'longitude' => $potentialCustomer->longitude,
            'in_service_area' => $potentialCustomer->in_service_area,
            'role' => 'customer',
            'email_verified_at' => Carbon::now(), // Auto-verify converted customers
        ]);

        // Update potential customer status
        $potentialCustomer->update([
            'status' => 'converted',
            'converted_at' => Carbon::now(),
            'converted_user_id' => $user->id,
        ]);

        // Send welcome email
        $this->notificationService->sendEmail(
            $user->email,
            'Welcome to Flags Across Our Community!',
            "Your account has been created successfully. You can now log in and start your flag subscription.",
            'customer-welcome',
            ['user' => $user, 'password' => $request->password]
        );

        return redirect()->route('admin.customers.show', $user)
            ->with('success', 'Potential customer converted successfully.');
    }

    /**
     * Bulk notify potential customers.
     */
    public function bulkNotify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:potential_customers,id',
            'message' => 'required|string|max:1000',
            'service_area_expansion' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customers = PotentialCustomer::whereIn('id', $request->customer_ids)->get();
        $successCount = 0;
        $failCount = 0;

        foreach ($customers as $customer) {
            $subject = $request->service_area_expansion 
                ? 'Service Now Available in Your Area!'
                : 'Important Update from Flags Across Our Community';

            $success = $this->notificationService->sendEmail(
                $customer->email,
                $subject,
                $request->message,
                'bulk-notification'
            );

            if ($success) {
                $customer->update([
                    'status' => 'contacted',
                    'contacted_at' => Carbon::now(),
                    'contact_notes' => "Bulk notification: " . $request->message,
                ]);
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "Bulk notification completed. {$successCount} sent successfully";
        if ($failCount > 0) {
            $message .= ", {$failCount} failed";
        }

        return redirect()->back()->with('success', $message . '.');
    }

    /**
     * Export potential customers to CSV.
     */
    public function export(Request $request)
    {
        $query = PotentialCustomer::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_area')) {
            if ($request->service_area === 'served') {
                $query->where('in_service_area', true);
            } elseif ($request->service_area === 'not_served') {
                $query->where('in_service_area', false);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'potential_customers_' . date('Y-m-d_H-i-s') . '.csv';

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
                'Status',
                'In Service Area',
                'Created Date',
                'Contacted Date',
                'Converted Date',
            ]);

            // Add data rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->address,
                    $customer->city,
                    $customer->state,
                    $customer->zip_code,
                    ucfirst($customer->status),
                    $customer->in_service_area ? 'Yes' : 'No',
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->contacted_at?->format('Y-m-d H:i:s'),
                    $customer->converted_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Check if potential customer is in current service area.
     */
    private function checkServiceArea(PotentialCustomer $customer, $serviceAreas)
    {
        foreach ($serviceAreas as $serviceArea) {
            if (in_array($customer->zip_code, $serviceArea->zip_codes ?? [])) {
                return true;
            }
        }

        return false;
    }
}
    