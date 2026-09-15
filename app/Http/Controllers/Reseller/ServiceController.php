<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $allServices = Service::where('status', 'active')->orderBy('platform')->orderBy('category')->get();
        $tenantServices = $tenant->services()->with('service')->get()->keyBy('service_id');

        return view('reseller.services.index', compact('allServices', 'tenantServices'));
    }

    public function toggle(Request $request, Service $service)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $existing = $tenant->services()->where('service_id', $service->id)->first();

        if ($existing) {
            $newStatus = $existing->status === 'active' ? 'inactive' : 'active';
            $existing->update(['status' => $newStatus]);
        } else {
            $tenant->services()->create([
                'service_id' => $service->id,
                'selling_price' => $service->selling_price,
                'markup_type' => 'percentage',
                'markup_value' => $service->markup_value,
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Service status updated.');
    }

    public function edit(Request $request, TenantService $tenantService)
    {
        Gate::authorize('update', $tenantService);

        return view('reseller.services.edit', compact('tenantService'));
    }

    public function update(Request $request, TenantService $tenantService)
    {
        Gate::authorize('update', $tenantService);

        $request->validate([
            'markup_type' => ['required', 'in:percentage,fixed_markup,fixed_price'],
            'markup_value' => ['required', 'numeric', 'min:0'],
        ]);

        $basePrice = $tenantService->service->base_price;
        $markupType = $request->markup_type;
        $markupValue = floatval($request->markup_value);

        if ($markupType === 'percentage') {
            $sellingPrice = round($basePrice * (1 + ($markupValue / 100)), 4);
        } elseif ($markupType === 'fixed_markup') {
            $sellingPrice = round($basePrice + $markupValue, 4);
        } else { // fixed_price
            $sellingPrice = round($markupValue, 4);
            if ($sellingPrice < $basePrice) {
                return back()->withErrors(['markup_value' => "Custom price cannot be lower than base cost of \${$basePrice}."]);
            }
        }

        $tenantService->update([
            'markup_type' => $markupType,
            'markup_value' => $markupValue,
            'selling_price' => $sellingPrice,
        ]);

        return redirect()->route('reseller.services.index')->with('success', 'Service pricing updated successfully.');
    }
}
