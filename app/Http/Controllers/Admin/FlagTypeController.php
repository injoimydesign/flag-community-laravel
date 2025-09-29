<?php
// app/Http/Controllers/Admin/FlagTypeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlagType;
use App\Models\FlagProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FlagTypeController extends Controller
{
    /**
     * Display a listing of flag types.
     */
    public function index(Request $request)
    {
        $query = FlagType::withCount(['flagProducts']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $flagTypes = $query->orderBy('category')->orderBy('name')->paginate(20);

        // Get categories for filter dropdown
        $categories = FlagType::distinct()->pluck('category')->filter()->sort();

        return view('admin.flag-types.index', compact('flagTypes', 'categories'));
    }

    /**
     * Show the form for creating a new flag type.
     */
    public function create()
    {
        $categories = FlagType::distinct()->pluck('category')->filter()->sort();
        
        return view('admin.flag-types.create', compact('categories'));
    }

    /**
     * Store a newly created flag type in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:flag_types',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'design_file' => 'nullable|file|mimes:pdf,ai,psd,svg|max:10240',
            'active' => 'boolean',
            'featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category' => $request->category,
            'active' => $request->has('active'),
            'featured' => $request->has('featured'),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('flag-types', 'public');
            $data['image_path'] = $imagePath;
        }

        // Handle design file upload
        if ($request->hasFile('design_file')) {
            $designPath = $request->file('design_file')->store('flag-designs', 'public');
            $data['design_file_path'] = $designPath;
        }

        FlagType::create($data);

        return redirect()->route('admin.flag-types.index')
            ->with('success', 'Flag type created successfully.');
    }

    /**
     * Display the specified flag type.
     */
    public function show(FlagType $flagType)
    {
        $flagType->load(['flagProducts.flagSize']);
        
        // Get usage statistics
        $stats = [
            'total_products' => $flagType->flagProducts->count(),
            'active_products' => $flagType->flagProducts->where('active', true)->count(),
            'total_inventory' => $flagType->flagProducts->sum('current_inventory'),
            'low_inventory_products' => $flagType->flagProducts->where('current_inventory', '<=', 
                function($query) {
                    $query->select('low_inventory_threshold');
                })->count(),
        ];

        return view('admin.flag-types.show', compact('flagType', 'stats'));
    }

    /**
     * Show the form for editing the specified flag type.
     */
    public function edit(FlagType $flagType)
    {
        $categories = FlagType::distinct()->pluck('category')->filter()->sort();
        
        return view('admin.flag-types.edit', compact('flagType', 'categories'));
    }

    /**
     * Update the specified flag type in storage.
     */
    public function update(Request $request, FlagType $flagType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:flag_types,name,' . $flagType->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'design_file' => 'nullable|file|mimes:pdf,ai,psd,svg|max:10240',
            'active' => 'boolean',
            'featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category' => $request->category,
            'active' => $request->has('active'),
            'featured' => $request->has('featured'),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($flagType->image_path) {
                Storage::disk('public')->delete($flagType->image_path);
            }
            
            $imagePath = $request->file('image')->store('flag-types', 'public');
            $data['image_path'] = $imagePath;
        }

        // Handle design file upload
        if ($request->hasFile('design_file')) {
            // Delete old design file if exists
            if ($flagType->design_file_path) {
                Storage::disk('public')->delete($flagType->design_file_path);
            }
            
            $designPath = $request->file('design_file')->store('flag-designs', 'public');
            $data['design_file_path'] = $designPath;
        }

        $flagType->update($data);

        return redirect()->route('admin.flag-types.index')
            ->with('success', 'Flag type updated successfully.');
    }

    /**
     * Remove the specified flag type from storage.
     */
    public function destroy(FlagType $flagType)
    {
        // Check if flag type has products
        if ($flagType->flagProducts()->count() > 0) {
            return redirect()->route('admin.flag-types.index')
                ->with('error', 'Cannot delete flag type with existing products.');
        }

        // Delete associated files
        if ($flagType->image_path) {
            Storage::disk('public')->delete($flagType->image_path);
        }

        if ($flagType->design_file_path) {
            Storage::disk('public')->delete($flagType->design_file_path);
        }

        $flagType->delete();

        return redirect()->route('admin.flag-types.index')
            ->with('success', 'Flag type deleted successfully.');
    }

    /**
     * Toggle active status of flag type.
     */
    public function toggleActive(FlagType $flagType)
    {
        $flagType->update(['active' => !$flagType->active]);

        $status = $flagType->active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Flag type {$status} successfully.",
            'active' => $flagType->active
        ]);
    }

    /**
     * Duplicate flag type.
     */
    public function duplicate(FlagType $flagType)
    {
        $newFlagType = $flagType->replicate();
        $newFlagType->name = $flagType->name . ' (Copy)';
        $newFlagType->slug = Str::slug($newFlagType->name);
        $newFlagType->active = false; // New duplicated flag types should be inactive by default
        $newFlagType->save();

        return redirect()->route('admin.flag-types.edit', $newFlagType)
            ->with('success', 'Flag type duplicated successfully. Please review and update the details.');
    }
}