<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        // Get featured flag types
        $usFlags = FlagType::usFlags();
        $militaryFlags = FlagType::militaryFlags();
        
        // Get upcoming holidays
        $upcomingHolidays = Holiday::upcoming()->take(3)->get();
        
        // Get service area coverage for map display
        $serviceAreas = ServiceArea::where('active', true)->get();

        return view('home', compact('usFlags', 'militaryFlags', 'upcomingHolidays', 'serviceAreas'));
    }

    /**
     * Show pricing information.
     */
    public function pricing()
    {
        $flagProducts = FlagProduct::with(['flagType', 'flagSize'])
            ->active()
            ->get()
            ->groupBy('flagType.category');

        $holidays = Holiday::active()->ordered()->get();

        return view('pricing', compact('flagProducts', 'holidays'));
    }

    /**
     * Show how it works page.
     */
    public function howItWorks()
    {
        $holidays = Holiday::active()->ordered()->get();
        
        return view('how-it-works', compact('holidays'));
    }

    /**
     * Show service areas page.
     */
    public function serviceAreas()
    {
        $serviceAreas = ServiceArea::where('active', true)->get();
        
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

        // In a real application, you'd use Google Maps Geocoding API
        // For now, we'll use a simple zip code check
        $isServed = ServiceArea::where('active', true)
            ->where(function ($query) use ($request) {
                $query->whereJsonContains('zip_codes', $request->zip_code);
            })
            ->exists();

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
    }

    /**
     * Get flag products for a specific category (AJAX endpoint).
     */
    public function getFlagProducts(Request $request)
    {
        $category = $request->get('category', 'all');
        
        $query = FlagProduct::with(['flagType', 'flagSize'])->active();
        
        if ($category !== 'all') {
            $query->whereHas('flagType', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }
        
        $products = $query->get()->groupBy('flagType.name');
        
        return response()->json($products);
    }

    /**
     * Calculate subscription pricing (AJAX endpoint).
     */
    public function calculatePricing(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'exists:flag_products,id',
            'subscription_type' => 'required|in:onetime,annual',
        ]);

        $products = FlagProduct::with(['flagType', 'flagSize'])
            ->whereIn('id', $request->products)
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
                'name' => $product->display_name,
                'price' => $price,
                'formatted_price' => '$' . number_format($price, 2),
            ];
        }

        $savings = 0;
        if ($request->subscription_type === 'annual') {
            // Calculate savings compared to buying for each holiday separately
            $holidayCount = Holiday::active()->count();
            $onetimeTotal = $products->sum('one_time_price') * $holidayCount;
            $savings = max(0, $onetimeTotal - $total);
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
            'formatted_total' => '$' . number_format($total, 2),
            'savings' => $savings,
            'formatted_savings' => '$' . number_format($savings, 2),
            'subscription_type' => $request->subscription_type,
        ]);
    }

    /**
     * Contact form submission.
     */
    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        // In a real application, you'd send an email or save to database
        // For now, we'll just flash a success message
        
        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}