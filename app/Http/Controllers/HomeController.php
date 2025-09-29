<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\FlagType;
use App\Models\FlagProduct;
use App\Models\Holiday;
use App\Models\ServiceArea;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index()
    {
        // Get featured flag types (with error handling)
        $usFlags = collect();
        $militaryFlags = collect();
        try {
            if (class_exists(FlagType::class) && Schema::hasTable('flag_types')) {
                $usFlags = FlagType::where('active', true)
                    ->where('category', 'US Flags')
                    ->take(6)
                    ->get();
                $militaryFlags = FlagType::where('active', true)
                    ->where('category', 'Military')
                    ->take(6)
                    ->get();
            }
        } catch (\Exception $e) {
            // Handle case where FlagType doesn't exist yet
        }

        // Get upcoming holidays (with error handling)
        $upcomingHolidays = collect();
        try {
            if (Schema::hasTable('holidays') && Schema::hasColumn('holidays', 'date')) {
                $upcomingHolidays = Holiday::where('active', true)
                    ->where('date', '>=', now())
                    ->orderBy('date')
                    ->take(3)
                    ->get();
            }
        } catch (\Exception $e) {
            // Handle case where holidays table doesn't exist yet
        }

        // Get service area coverage for map display (with error handling)
        $serviceAreas = collect();
        try {
            if (Schema::hasTable('service_areas')) {
                $serviceAreas = ServiceArea::where('active', true)->get();
            }
        } catch (\Exception $e) {
            // Handle case where service_areas table doesn't exist yet
        }

        return view('home', compact('usFlags', 'militaryFlags', 'upcomingHolidays', 'serviceAreas'));
    }

    /**
     * Show pricing information.
     */
    public function pricing()
    {
        $flagProducts = collect();
        $holidays = collect();

        try {
            if (Schema::hasTable('flag_products')) {
                $flagProducts = FlagProduct::with(['flagType', 'flagSize'])
                    ->where('active', true)
                    ->get()
                    ->groupBy('flagType.category');
            }
        } catch (\Exception $e) {
            // Handle case where tables don't exist yet
        }

        try {
            if (Schema::hasTable('holidays')) {
                $holidays = Holiday::where('active', true)->orderBy('date')->get();
            }
        } catch (\Exception $e) {
            // Handle case where holidays table doesn't exist yet
        }

        return view('pricing', compact('flagProducts', 'holidays'));
    }

    /**
     * Show how it works page.
     */
    public function howItWorks()
    {
        $holidays = collect();

        try {
            if (Schema::hasTable('holidays')) {
                $holidays = Holiday::where('active', true)->orderBy('date')->get();
            }
        } catch (\Exception $e) {
            // Handle case where holidays table doesn't exist yet
        }

        return view('how-it-works', compact('holidays'));
    }

    /**
     * Show service areas page.
     */
    public function serviceAreas()
    {
        $serviceAreas = collect();

        try {
            if (Schema::hasTable('service_areas')) {
                $serviceAreas = ServiceArea::where('active', true)->get();
            }
        } catch (\Exception $e) {
            // Handle case where service_areas table doesn't exist yet
        }

        return view('service-areas', compact('serviceAreas'));
    }

    /**
     * Check if address is in service area (AJAX endpoint).
     */
    public function checkServiceArea(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
        ]);

        try {
            // In a real application, you'd use Google Maps Geocoding API
            // For now, we'll use a simple zip code check
            $isServed = false;

            if (Schema::hasTable('service_areas')) {
                $isServed = ServiceArea::where('active', true)
                    ->where(function ($query) use ($request) {
                        $query->whereJsonContains('zip_codes', $request->zip_code);
                    })
                    ->exists();
            }

            if ($isServed) {
                return response()->json([
                    'served' => true,
                    'message' => 'Great! We serve your area. You can proceed with your order.',
                ]);
            }

            return response()->json([
                'served' => false,
                'message' => 'We don\'t currently serve your area, but we\'re expanding! We\'ll save your information and notify you when service becomes available.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'served' => false,
                'message' => 'Unable to check service area at this time. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get flag products for a specific category (AJAX endpoint).
     */
    public function getFlagProducts(Request $request)
    {
        try {
            $category = $request->get('category');

            if (!Schema::hasTable('flag_products')) {
                return response()->json([]);
            }

            $products = FlagProduct::with(['flagType', 'flagSize'])
                ->where('active', true)
                ->when($category, function ($query) use ($category) {
                    $query->whereHas('flagType', function ($q) use ($category) {
                        $q->where('category', $category);
                    });
                })
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Calculate pricing for selected products (AJAX endpoint).
     */
    public function calculatePricing(Request $request)
    {
        try {
            $request->validate([
                'products' => 'required|array',
                'products.*' => 'integer',
                'subscription_type' => 'required|in:onetime,annual',
            ]);

            if (!Schema::hasTable('flag_products')) {
                return response()->json(['error' => 'Service temporarily unavailable'], 500);
            }

            $products = FlagProduct::whereIn('id', $request->products)
                ->where('active', true)
                ->get();

            $total = 0;
            $items = [];

            foreach ($products as $product) {
                $price = $request->subscription_type === 'annual'
                    ? $product->annual_subscription_price
                    : $product->one_time_price;

                $total += $price;

                $items[] = [
                    'id' => $product->id,
                    'name' => $product->flagType->name ?? 'Flag Product',
                    'price' => $price / 100, // Convert from cents
                ];
            }

            return response()->json([
                'total' => $total / 100, // Convert from cents
                'items' => $items,
                'subscription_type' => $request->subscription_type,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to calculate pricing'], 500);
        }
    }

    /**
     * Handle contact form submission.
     */
    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // In a real application, you would send an email or save to database
        // For now, just return success
        return redirect()->back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}
