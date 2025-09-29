<?php
// app/Http/Controllers/Admin/ServiceAreaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceAreaController extends Controller
{
    /**
     * Display a listing of service areas.
     */
    public function index(Request $request)
    {
        $query = ServiceArea::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $serviceAreas = $query->orderBy('state')->orderBy('name')->paginate(15);

        return view('admin.service-areas.index', compact('serviceAreas'));
    }

    /**
     * Show the form for creating a new service area.
     */
    public function create()
    {
        return view('admin.service-areas.create');
    }

    /**
     * Store a newly created service area in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'description' => 'nullable|string|max:1000',
            'zip_codes' => 'required|array|min:1',
            'zip_codes.*' => 'required|string|regex:/^\d{5}(-\d{4})?$/',
            'coverage_radius' => 'nullable|numeric|min:0|max:100',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ServiceArea::create([
            'name' => $request->name,
            'state' => strtoupper($request->state),
            'description' => $request->description,
            'zip_codes' => array_unique($request->zip_codes),
            'coverage_radius' => $request->coverage_radius,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('admin.service-areas.index')
            ->with('success', 'Service area created successfully.');
    }

    /**
     * Display the specified service area.
     */
    public function show(ServiceArea $serviceArea)
    {
        $customerCount = $serviceArea->getCustomerCount();
        $subscriptionCount = $serviceArea->getSubscriptionCount();

        return view('admin.service-areas.show', compact(
            'serviceArea',
            'customerCount',
            'subscriptionCount'
        ));
    }

    /**
     * Show the form for editing the specified service area.
     */
    public function edit(ServiceArea $serviceArea)
    {
        return view('admin.service-areas.edit', compact('serviceArea'));
    }

    /**
     * Update the specified service area in storage.
     */
    public function update(Request $request, ServiceArea $serviceArea)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'description' => 'nullable|string|max:1000',
            'zip_codes' => 'required|array|min:1',
            'zip_codes.*' => 'required|string|regex:/^\d{5}(-\d{4})?$/',
            'coverage_radius' => 'nullable|numeric|min:0|max:100',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $serviceArea->update([
            'name' => $request->name,
            'state' => strtoupper($request->state),
            'description' => $request->description,
            'zip_codes' => array_unique($request->zip_codes),
            'coverage_radius' => $request->coverage_radius,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('admin.service-areas.index')
            ->with('success', 'Service area updated successfully.');
    }

    /**
     * Remove the specified service area from storage.
     */
    public function destroy(ServiceArea $serviceArea)
    {
        // Check if service area has customers
        if ($serviceArea->getCustomerCount() > 0) {
            return redirect()->route('admin.service-areas.index')
                ->with('error', 'Cannot delete service area with existing customers.');
        }

        $serviceArea->delete();

        return redirect()->route('admin.service-areas.index')
            ->with('success', 'Service area deleted successfully.');
    }
}
