<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Services\Providers\ProviderManager;
use App\Services\Providers\ProviderSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderController extends Controller
{
    protected ProviderManager $manager;
    protected ProviderSyncService $syncService;

    public function __construct(ProviderManager $manager, ProviderSyncService $syncService)
    {
        $this->manager = $manager;
        $this->syncService = $syncService;
    }

    public function index()
    {
        Gate::authorize('access-admin');

        $providers = Provider::withCount('providerServices')->get();
        return view('admin.providers.index', compact('providers'));
    }

    public function edit(Provider $provider)
    {
        Gate::authorize('access-admin');

        return view('admin.providers.edit', compact('provider'));
    }

    public function update(Request $request, Provider $provider)
    {
        Gate::authorize('access-admin');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'api_url' => ['required', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive,disabled'],
        ]);

        $data = [
            'name' => $request->name,
            'api_url' => $request->api_url,
            'status' => $request->status,
        ];

        if ($request->filled('api_key')) {
            $data['api_key'] = $request->api_key;
        }

        $provider->update($data);

        return redirect()->route('admin.providers.index')->with('success', 'Provider updated successfully.');
    }

    public function testConnection(Provider $provider)
    {
        Gate::authorize('access-admin');

        try {
            $adapter = $this->manager->make($provider);
            $success = $adapter->testConnection();

            if ($success) {
                $balance = $adapter->getBalance();
                return back()->with('success', "Connection successful! Provider balance: {$balance['currency']} {$balance['balance']}");
            }

            return back()->withErrors(['provider' => 'Provider connection test failed.']);
        } catch (\Exception $e) {
            return back()->withErrors(['provider' => 'Provider connection failed: ' . $e->getMessage()]);
        }
    }

    public function sync(Provider $provider)
    {
        Gate::authorize('access-admin');

        try {
            $result = $this->syncService->sync($provider);
            return back()->with('success', "Synchronization completed! {$result['total']} services processed ({$result['created']} new, {$result['updated']} updated).");
        } catch (\Exception $e) {
            return back()->withErrors(['provider' => 'Synchronization failed: ' . $e->getMessage()]);
        }
    }
}
