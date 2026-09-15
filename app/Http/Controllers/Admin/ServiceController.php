<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $query = Service::with(['provider', 'providerService']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $services = $query->orderBy('sort_order')->orderBy('id', 'desc')->paginate(15);
        $platforms = Service::distinct()->whereNotNull('platform')->pluck('platform');

        return view('admin.services.index', compact('services', 'platforms'));
    }

    public function create()
    {
        Gate::authorize('access-admin');

        $providerServices = ProviderService::where('status', 'active')->with('provider')->get();
        return view('admin.services.create', compact('providerServices'));
    }

    public function store(Request $request)
    {
        Gate::authorize('access-admin');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'provider_service_id' => ['nullable', 'exists:provider_services,id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'markup_value' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'max_quantity' => ['required', 'integer', 'gt:min_quantity'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $pService = $request->provider_service_id ? ProviderService::find($request->provider_service_id) : null;
        $providerId = $pService ? $pService->provider_id : null;
        $sellingPrice = round($request->base_price * (1 + ($request->markup_value / 100)), 4);

        Service::create([
            'provider_id' => $providerId,
            'provider_service_id' => $request->provider_service_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'platform' => $request->platform,
            'category' => $request->category,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'selling_price' => $sellingPrice,
            'markup_type' => 'percentage',
            'markup_value' => $request->markup_value,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Platform service created successfully.');
    }

    public function edit(Service $service)
    {
        Gate::authorize('access-admin');

        $providerServices = ProviderService::where('status', 'active')->with('provider')->get();
        return view('admin.services.edit', compact('service', 'providerServices'));
    }

    public function update(Request $request, Service $service)
    {
        Gate::authorize('access-admin');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'provider_service_id' => ['nullable', 'exists:provider_services,id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'markup_value' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'max_quantity' => ['required', 'integer', 'gt:min_quantity'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $pService = $request->provider_service_id ? ProviderService::find($request->provider_service_id) : null;
        $providerId = $pService ? $pService->provider_id : null;
        $sellingPrice = round($request->base_price * (1 + ($request->markup_value / 100)), 4);

        $service->update([
            'provider_id' => $providerId,
            'provider_service_id' => $request->provider_service_id,
            'name' => $request->name,
            'platform' => $request->platform,
            'category' => $request->category,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'selling_price' => $sellingPrice,
            'markup_value' => $request->markup_value,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Platform service updated successfully.');
    }
}
