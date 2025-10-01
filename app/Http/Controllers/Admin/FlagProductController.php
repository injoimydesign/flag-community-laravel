<?php
// app/Http/Controllers/Admin/FlagProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlagProduct;
use App\Models\FlagType;
use App\Models\FlagSize;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FlagProductController extends Controller
{

    /**
     * Display a listing of flag products.
     */
    public function index(Request $request)
    {
        $query = FlagProduct::with(['flagType', 'flagSize']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('flagType', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('flagSize', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Category filter (filter by flag type category)
        if ($request->filled('category')) {
            $query->whereHas('flagType', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        // Flag type filter
        if ($request->filled('flag_type_id')) {
            $query->where('flag_type_id', $request->flag_type_id);
        }

        // Flag size filter
        if ($request->filled('flag_size_id')) {
            $query->where('flag_size_id', $request->flag_size_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        // Inventory filter
        if ($request->filled('inventory_status')) {
            switch ($request->inventory_status) {
                case 'low':
                    $query->whereRaw('current_inventory <= low_inventory_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('current_inventory', 0);
                    break;
                case 'in_stock':
                    $query->where('current_inventory', '>', 0);
                    break;
            }
        }

        // Quick filter
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'low_inventory':
                    $query->whereRaw('current_inventory <= low_inventory_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('current_inventory', 0);
                    break;
            }
        }

        $products = $query->orderBy('flag_type_id')->orderBy('flag_size_id')->paginate(20);

        // Get filter options
        $flagTypes = FlagType::active()->orderBy('name')->get();
        $flagSizes = FlagSize::active()->orderBy('name')->get();

        // Get categories from flag types
        $categories = FlagType::distinct()->pluck('category')->filter()->sort()->values();

        // Get statistics
        $stats = [
            'total_products' => FlagProduct::count(),
            'active_products' => FlagProduct::where('active', true)->count(),
            'low_inventory' => FlagProduct::whereRaw('current_inventory <= low_inventory_threshold')->count(),
            'out_of_stock' => FlagProduct::where('current_inventory', 0)->count(),
            'total_inventory_value' => FlagProduct::selectRaw('SUM(current_inventory * cost_per_unit) as total')->value('total') / 100 ?? 0,
        ];

        return view('admin.flag-products.index', compact(
            'products',
            'flagTypes',
            'flagSizes',
            'categories',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new flag product.
     */
    public function create()
    {
        $flagTypes = FlagType::active()->orderBy('name')->get();
        $flagSizes = FlagSize::active()->orderBy('name')->get();

        return view('admin.flag-products.create', compact('flagTypes', 'flagSizes'));
    }

    /**
     * Store a newly created flag product in storage.
     *
     * FIXES:
     * 1. Changed active assignment to convert string to boolean
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flag_type_id' => 'required|exists:flag_types,id',
            'flag_size_id' => 'required|exists:flag_sizes,id',
            'one_time_price' => 'required|numeric|min:0',
            'annual_subscription_price' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'current_inventory' => 'required|integer|min:0',
            'low_inventory_threshold' => 'required|integer|min:0',
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if combination already exists
        $exists = FlagProduct::where('flag_type_id', $request->flag_type_id)
            ->where('flag_size_id', $request->flag_size_id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'A product with this flag type and size combination already exists.')
                ->withInput();
        }

        $flagProduct = FlagProduct::create([
            'flag_type_id' => $request->flag_type_id,
            'flag_size_id' => $request->flag_size_id,
            'one_time_price' => $request->one_time_price * 100,
            'annual_subscription_price' => $request->annual_subscription_price * 100,
            'cost_per_unit' => $request->cost_per_unit * 100,
            'current_inventory' => $request->current_inventory,
            'low_inventory_threshold' => $request->low_inventory_threshold,
            'active' => $request->active == 1,  // Changed from $request->has('active')
        ]);

        // Log initial inventory with correct column names
        InventoryAdjustment::create([
            'flag_product_id' => $flagProduct->id,
            'adjustment_type' => 'initial',
            'quantity' => $request->current_inventory,
            'previous_quantity' => 0,  // Changed from 'previous_inventory'
            'new_quantity' => $request->current_inventory,  // Changed from 'new_inventory'
            'reason' => 'Initial inventory setup',
            'adjusted_by' => auth()->id(),
        ]);

        return redirect()->route('admin.flag-products.show', $flagProduct)
            ->with('success', 'Flag product created successfully.');
    }

    /**
     * Display the specified flag product.
     */
    public function show(FlagProduct $flagProduct)
    {
        $flagProduct->load(['flagType', 'flagSize', 'inventoryAdjustments.adjustedBy']);

        // Get usage statistics
        $stats = [
            'active_subscriptions' => $flagProduct->getActiveSubscriptionCount(),
            'total_placements' => $flagProduct->getTotalPlacementCount(),
            'inventory_value' => ($flagProduct->current_inventory * $flagProduct->cost_per_unit) / 100,
            'monthly_usage' => $flagProduct->getMonthlyUsage(),
        ];

        // Get recent inventory adjustments
        $recentAdjustments = $flagProduct->inventoryAdjustments()
            ->with('adjustedBy')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.flag-products.show', compact(
            'flagProduct',
            'stats',
            'recentAdjustments'
        ));
    }

    /**
     * Show the form for editing the specified flag product.
     */
    public function edit(FlagProduct $flagProduct)
    {
        $flagTypes = FlagType::active()->orderBy('name')->get();
        $flagSizes = FlagSize::active()->orderBy('name')->get();

        return view('admin.flag-products.edit', compact('flagProduct', 'flagTypes', 'flagSizes'));
    }

    /**
     * Update the specified flag product in storage.
     *
     * FIXES:
     * 1. Changed 'active' validation to 'required|boolean'
     * 2. Changed active assignment to convert string to boolean
     */
    public function update(Request $request, FlagProduct $flagProduct)
    {
        $validator = Validator::make($request->all(), [
            'flag_type_id' => 'required|exists:flag_types,id',
            'flag_size_id' => 'required|exists:flag_sizes,id',
            'one_time_price' => 'required|numeric|min:0',
            'annual_subscription_price' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'low_inventory_threshold' => 'required|integer|min:0',
            'active' => 'required|boolean',  // Changed from 'boolean' to 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if combination already exists (excluding current product)
        $exists = FlagProduct::where('flag_type_id', $request->flag_type_id)
            ->where('flag_size_id', $request->flag_size_id)
            ->where('id', '!=', $flagProduct->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'A product with this flag type and size combination already exists.')
                ->withInput();
        }

        $flagProduct->update([
            'flag_type_id' => $request->flag_type_id,
            'flag_size_id' => $request->flag_size_id,
            'one_time_price' => $request->one_time_price * 100,
            'annual_subscription_price' => $request->annual_subscription_price * 100,
            'cost_per_unit' => $request->cost_per_unit * 100,
            'low_inventory_threshold' => $request->low_inventory_threshold,
            'active' => $request->active == 1,  // Changed from $request->has('active')
        ]);

        return redirect()->route('admin.flag-products.show', $flagProduct)
            ->with('success', 'Flag product updated successfully.');
    }


    /**
     * Remove the specified flag product from storage.
     */
    public function destroy(FlagProduct $flagProduct)
    {
        // Check if product has active subscriptions
        if ($flagProduct->getActiveSubscriptionCount() > 0) {
            return redirect()->route('admin.flag-products.index')
                ->with('error', 'Cannot delete flag product with active subscriptions.');
        }

        $flagProduct->delete();

        return redirect()->route('admin.flag-products.index')
            ->with('success', 'Flag product deleted successfully.');
    }

    /**
 * Adjust inventory for a flag product.
 * Supports both form submissions and JSON/AJAX requests.
 *
 * This MUST replace the adjustInventory method in app/Http/Controllers/Admin/FlagProductController.php
 */
public function adjustInventory(Request $request, FlagProduct $flagProduct)
{
    // Add error handling wrapper to catch any exceptions
    try {
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:increase,decrease,set',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            // Handle JSON/AJAX requests differently
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldInventory = $flagProduct->current_inventory;
        $quantity = $request->quantity;

        // Map adjustment types to database enum values
        $dbAdjustmentType = match($request->adjustment_type) {
            'increase' => 'restock',
            'decrease' => 'sale',
            'set' => 'correction',
            default => 'correction'
        };

        switch ($request->adjustment_type) {
            case 'increase':
                $newInventory = $oldInventory + $quantity;
                $adjustmentQuantity = $quantity;
                break;
            case 'decrease':
                $newInventory = max(0, $oldInventory - $quantity);
                $adjustmentQuantity = -$quantity;
                break;
            case 'set':
                $newInventory = $quantity;
                $adjustmentQuantity = $quantity - $oldInventory;
                break;
        }

        // Update inventory
        $flagProduct->update(['current_inventory' => $newInventory]);

        // Log adjustment with correct column names
        InventoryAdjustment::create([
            'flag_product_id' => $flagProduct->id,
            'adjustment_type' => $dbAdjustmentType,
            'quantity' => $adjustmentQuantity,
            'previous_quantity' => $oldInventory,      // FIXED: Changed from previous_inventory
            'new_quantity' => $newInventory,           // FIXED: Changed from new_inventory
            'reason' => $request->reason,
            'adjusted_by' => auth()->id(),
        ]);

        // Handle JSON/AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventory adjusted successfully.',
                'data' => [
                    'previous_inventory' => $oldInventory,
                    'new_inventory' => $newInventory,
                    'adjustment' => $adjustmentQuantity
                ]
            ]);
        }

        return redirect()->back()
            ->with('success', 'Inventory adjusted successfully.');

    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Inventory adjustment error: ' . $e->getMessage(), [
            'product_id' => $flagProduct->id,
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);

        // Handle JSON/AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adjusting inventory: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'An error occurred while adjusting inventory.')
            ->withInput();
    }
}

    /**
     * Toggle active status of flag product.
     */
    public function toggleActive(FlagProduct $flagProduct)
    {
        $flagProduct->update(['active' => !$flagProduct->active]);

        $status = $flagProduct->active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Flag product {$status} successfully.",
            'active' => $flagProduct->active
        ]);
    }

    /**
     * Bulk update inventory for multiple products.
     */
    public function bulkUpdateInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*' => 'exists:flag_products,id',
            'adjustment_type' => 'required|in:increase,decrease,set',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updatedCount = 0;
        $flagProducts = FlagProduct::whereIn('id', $request->products)->get();

        foreach ($flagProducts as $flagProduct) {
            $oldInventory = $flagProduct->current_inventory;
            $quantity = $request->quantity;

            switch ($request->adjustment_type) {
                case 'increase':
                    $newInventory = $oldInventory + $quantity;
                    $adjustmentQuantity = $quantity;
                    break;
                case 'decrease':
                    $newInventory = max(0, $oldInventory - $quantity);
                    $adjustmentQuantity = -$quantity;
                    break;
                case 'set':
                    $newInventory = $quantity;
                    $adjustmentQuantity = $quantity - $oldInventory;
                    break;
            }

            // Update inventory
            $flagProduct->update(['current_inventory' => $newInventory]);

            // Log adjustment
            InventoryAdjustment::create([
                'flag_product_id' => $flagProduct->id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $adjustmentQuantity,
                'previous_inventory' => $oldInventory,
                'new_inventory' => $newInventory,
                'reason' => "Bulk update: " . $request->reason,
                'adjusted_by' => auth()->id(),
            ]);

            $updatedCount++;
        }

        return redirect()->back()
            ->with('success', "Inventory updated for {$updatedCount} products.");
    }

    /**
     * Export flag products to CSV.
     */
    public function export(Request $request)
    {
        $query = FlagProduct::with(['flagType', 'flagSize']);

        // Apply same filters as index
        if ($request->filled('flag_type_id')) {
            $query->where('flag_type_id', $request->flag_type_id);
        }

        if ($request->filled('flag_size_id')) {
            $query->where('flag_size_id', $request->flag_size_id);
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $flagProducts = $query->orderBy('flag_type_id')->orderBy('flag_size_id')->get();

        $filename = 'flag_products_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($flagProducts) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Flag Type',
                'Flag Size',
                'One-Time Price',
                'Annual Subscription Price',
                'Cost Per Unit',
                'Current Inventory',
                'Low Inventory Threshold',
                'Inventory Value',
                'Active',
                'Created Date',
                'Updated Date',
            ]);

            // Add data rows
            foreach ($flagProducts as $product) {
                $inventoryValue = ($product->current_inventory * $product->cost_per_unit) / 100;

                fputcsv($file, [
                    $product->flagType->name,
                    $product->flagSize->name,
                    ' . number_format($product->one_time_price / 100, 2),
                    ' . number_format($product->annual_subscription_price / 100, 2),
                    ' . number_format($product->cost_per_unit / 100, 2),
                    $product->current_inventory,
                    $product->low_inventory_threshold,
                    ' . number_format($inventoryValue, 2),
                    $product->active ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d H:i:s'),
                    $product->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get inventory history for a flag product.
     */
    public function inventoryHistory(FlagProduct $flagProduct)
    {
        $adjustments = $flagProduct->inventoryAdjustments()
            ->with('adjustedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.flag-products.inventory-history', compact('flagProduct', 'adjustments'));
    }

    /**
     * Duplicate a flag product.
     */
    public function duplicate(FlagProduct $flagProduct)
    {
        $newProduct = $flagProduct->replicate();
        $newProduct->active = false; // New duplicated products should be inactive by default
        $newProduct->current_inventory = 0; // Reset inventory
        $newProduct->save();

        // Log initial inventory (0)
        InventoryAdjustment::create([
            'flag_product_id' => $newProduct->id,
            'adjustment_type' => 'initial',
            'quantity' => 0,
            'reason' => 'Duplicated from product ID ' . $flagProduct->id,
            'adjusted_by' => auth()->id(),
        ]);

        return redirect()->route('admin.flag-products.edit', $newProduct)
            ->with('success', 'Flag product duplicated successfully. Please review and update the details.');
    }

    /**
     * Get pricing suggestions based on market data.
     */
    public function getPricingSuggestions(Request $request)
    {
        $flagTypeId = $request->get('flag_type_id');
        $flagSizeId = $request->get('flag_size_id');

        if (!$flagTypeId || !$flagSizeId) {
            return response()->json(['error' => 'Flag type and size are required.'], 400);
        }

        // Get similar products for pricing comparison
        $similarProducts = FlagProduct::where('flag_type_id', $flagTypeId)
            ->orWhere('flag_size_id', $flagSizeId)
            ->where('active', true)
            ->get();

        if ($similarProducts->isEmpty()) {
            return response()->json([
                'suggestions' => [
                    'one_time_price' => null,
                    'annual_subscription_price' => null,
                    'message' => 'No similar products found for pricing comparison.'
                ]
            ]);
        }

        // Calculate average prices
        $avgOneTime = $similarProducts->avg('one_time_price') / 100;
        $avgAnnual = $similarProducts->avg('annual_subscription_price') / 100;

        // Get price ranges
        $oneTimePrices = $similarProducts->pluck('one_time_price')->map(fn($price) => $price / 100);
        $annualPrices = $similarProducts->pluck('annual_subscription_price')->map(fn($price) => $price / 100);

        return response()->json([
            'suggestions' => [
                'one_time_price' => [
                    'suggested' => round($avgOneTime, 2),
                    'min' => $oneTimePrices->min(),
                    'max' => $oneTimePrices->max(),
                    'average' => round($avgOneTime, 2),
                ],
                'annual_subscription_price' => [
                    'suggested' => round($avgAnnual, 2),
                    'min' => $annualPrices->min(),
                    'max' => $annualPrices->max(),
                    'average' => round($avgAnnual, 2),
                ],
                'comparison_count' => $similarProducts->count(),
            ]
        ]);
    }

    /**
     * Get flag product metrics.
     */
    public function getMetrics(Request $request)
    {
        $period = $request->get('period', '30days');

        switch ($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                break;
            case '12months':
            default:
                $startDate = Carbon::now()->subMonths(12);
                break;
        }

        // Inventory adjustments over time
        $adjustmentData = InventoryAdjustment::where('created_at', '>=', $startDate)
            ->with('flagProduct.flagType')
            ->get()
            ->groupBy(function ($adjustment) {
                return $adjustment->created_at->format('Y-m-d');
            })
            ->map(function ($adjustments, $date) {
                return [
                    'date' => $date,
                    'total_adjustments' => $adjustments->count(),
                    'net_change' => $adjustments->sum('quantity'),
                ];
            })->values();

        // Top products by usage
        $topProducts = FlagProduct::with(['flagType', 'flagSize'])
            ->withCount(['subscriptionItems'])
            ->orderBy('subscription_items_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->flagType->name . ' (' . $product->flagSize->name . ')',
                    'usage_count' => $product->subscription_items_count,
                    'current_inventory' => $product->current_inventory,
                ];
            });

        // Inventory status breakdown
        $inventoryStatus = [
            'In Stock' => FlagProduct::where('current_inventory', '>', 0)->count(),
            'Low Stock' => FlagProduct::whereRaw('current_inventory <= low_inventory_threshold AND current_inventory > 0')->count(),
            'Out of Stock' => FlagProduct::where('current_inventory', 0)->count(),
        ];

        return response()->json([
            'adjustments' => $adjustmentData,
            'top_products' => $topProducts,
            'inventory_status' => $inventoryStatus,
        ]);
    }
}
