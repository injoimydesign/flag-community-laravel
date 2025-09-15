<?php
// app/Http/Controllers/Admin/FlagProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlagProduct;
use App\Models\FlagType;
use App\Models\FlagSize;
use Illuminate\Support\Facades\Validator;

class FlagProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of flag products.
     */
    public function index(Request $request)
    {
        $query = FlagProduct::with(['flagType', 'flagSize']);

        // Apply filters
        if ($request->filled('category')) {
            $query->whereHas('flagType', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('size')) {
            $query->where('flag_size_id', $request->size);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'low_inventory':
                    $query->lowInventory();
                    break;
                case 'out_of_stock':
                    $query->where('inventory_count', 0);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('flagType', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $flagTypes = FlagType::orderBy('name')->get();
        $flagSizes = FlagSize::orderBy('sort_order')->get();
        $categories = FlagType::distinct()->pluck('category');

        return view('admin.flag-products.index', compact(
            'products',
            'flagTypes',
            'flagSizes',
            'categories'
        ));
    }

    /**
     * Show the form for creating a new flag product.
     */
    public function create()
    {
        $flagTypes = FlagType::active()->orderBy('name')->get();
        $flagSizes = FlagSize::active()->orderBy('sort_order')->get();

        return view('admin.flag-products.create', compact('flagTypes', 'flagSizes'));
    }

    /**
     * Store a newly created flag product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flag_type_id' => 'required|exists:flag_types,id',
            'flag_size_id' => 'required|exists:flag_sizes,id',
            'one_time_price' => 'required|numeric|min:0',
            'annual_subscription_price' => 'required|numeric|min:0',
            'inventory_count' => 'required|integer|min:0',
            'min_inventory_alert' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        // Check for duplicate combination
        $validator->after(function ($validator) use ($request) {
            $exists = FlagProduct::where('flag_type_id', $request->flag_type_id)
                ->where('flag_size_id', $request->flag_size_id)
                ->exists();
            
            if ($exists) {
                $validator->errors()->add('flag_type_id', 'This combination of flag type and size already exists.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = FlagProduct::create([
            'flag_type_id' => $request->flag_type_id,
            'flag_size_id' => $request->flag_size_id,
            'one_time_price' => $request->one_time_price,
            'annual_subscription_price' => $request->annual_subscription_price,
            'inventory_count' => $request->inventory_count,
            'min_inventory_alert' => $request->min_inventory_alert,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.flag-products.index')
            ->with('success', 'Flag product created successfully.');
    }

    /**
     * Display the specified flag product.
     */
    public function show(FlagProduct $flagProduct)
    {
        $flagProduct->load(['flagType', 'flagSize', 'subscriptionItems.subscription.user']);

        // Get related statistics
        $stats = [
            'total_subscriptions' => $flagProduct->subscriptionItems()->count(),
            'active_subscriptions' => $flagProduct->subscriptionItems()
                ->whereHas('subscription', function ($q) {
                    $q->where('status', 'active');
                })
                ->count(),
            'total_revenue' => $flagProduct->subscriptionItems()->sum('total_price'),
            'flags_placed' => $flagProduct->flagPlacements()->where('status', 'placed')->count(),
        ];

        return view('admin.flag-products.show', compact('flagProduct', 'stats'));
    }

    /**
     * Show the form for editing the specified flag product.
     */
    public function edit(FlagProduct $flagProduct)
    {
        $flagTypes = FlagType::active()->orderBy('name')->get();
        $flagSizes = FlagSize::active()->orderBy('sort_order')->get();

        return view('admin.flag-products.edit', compact('flagProduct', 'flagTypes', 'flagSizes'));
    }

    /**
     * Update the specified flag product.
     */
    public function update(Request $request, FlagProduct $flagProduct)
    {
        $validator = Validator::make($request->all(), [
            'flag_type_id' => 'required|exists:flag_types,id',
            'flag_size_id' => 'required|exists:flag_sizes,id',
            'one_time_price' => 'required|numeric|min:0',
            'annual_subscription_price' => 'required|numeric|min:0',
            'inventory_count' => 'required|integer|min:0',
            'min_inventory_alert' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        // Check for duplicate combination (excluding current product)
        $validator->after(function ($validator) use ($request, $flagProduct) {
            $exists = FlagProduct::where('flag_type_id', $request->flag_type_id)
                ->where('flag_size_id', $request->flag_size_id)
                ->where('id', '!=', $flagProduct->id)
                ->exists();
            
            if ($exists) {
                $validator->errors()->add('flag_type_id', 'This combination of flag type and size already exists.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $flagProduct->update([
            'flag_type_id' => $request->flag_type_id,
            'flag_size_id' => $request->flag_size_id,
            'one_time_price' => $request->one_time_price,
            'annual_subscription_price' => $request->annual_subscription_price,
            'inventory_count' => $request->inventory_count,
            'min_inventory_alert' => $request->min_inventory_alert,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.flag-products.index')
            ->with('success', 'Flag product updated successfully.');
    }

    /**
     * Remove the specified flag product.
     */
    public function destroy(FlagProduct $flagProduct)
    {
        // Check if product has active subscriptions
        $activeSubscriptions = $flagProduct->subscriptionItems()
            ->whereHas('subscription', function ($q) {
                $q->where('status', 'active');
            })
            ->count();

        if ($activeSubscriptions > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete product with active subscriptions. Deactivate it instead.');
        }

        $flagProduct->delete();

        return redirect()->route('admin.flag-products.index')
            ->with('success', 'Flag product deleted successfully.');
    }

    /**
     * Bulk update inventory.
     */
    public function bulkUpdateInventory(Request $request)
    