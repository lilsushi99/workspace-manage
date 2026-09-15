<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Service;
use App\Models\Store;
use App\Models\TenantService;
use App\Rules\ReservedSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    protected array $stepOrder = ['account', 'services', 'pricing', 'store', 'bank', 'review', 'complete'];

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('dashboard');
        }

        if ($tenant->isOnboardingComplete()) {
            return redirect()->route('onboarding.complete');
        }

        $step = $tenant->onboarding_step ?: 'account';
        return redirect()->route('onboarding.' . $step);
    }

    public function showAccount(Request $request)
    {
        $user = $request->user();
        return view('onboarding.account', compact('user'));
    }

    public function postAccount(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
        ]);

        $tenant = $user->tenant;
        $tenant->update(['onboarding_step' => 'services']);

        return redirect()->route('onboarding.services');
    }

    public function showServices(Request $request)
    {
        $tenant = $request->user()->tenant;
        $services = Service::where('status', 'active')->orderBy('platform')->orderBy('category')->get();
        $groupedServices = $services->groupBy(['platform', 'category']);
        $selectedServiceIds = $tenant->services()->pluck('service_id')->toArray();

        return view('onboarding.services', compact('groupedServices', 'selectedServiceIds'));
    }

    public function postServices(Request $request)
    {
        $tenant = $request->user()->tenant;
        $request->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['exists:services,id'],
        ]);

        DB::transaction(function () use ($tenant, $request) {
            $selectedIds = $request->services;

            // Remove unselected tenant services
            $tenant->services()->whereNotIn('service_id', $selectedIds)->delete();

            // Attach new selected services
            foreach ($selectedIds as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $tenant->services()->firstOrCreate(
                        ['service_id' => $serviceId],
                        [
                            'selling_price' => $service->selling_price,
                            'markup_type' => 'percentage',
                            'markup_value' => $service->markup_value,
                            'status' => 'active',
                        ]
                    );
                }
            }

            $tenant->update(['onboarding_step' => 'pricing']);
        });

        return redirect()->route('onboarding.pricing');
    }

    public function showPricing(Request $request)
    {
        $tenant = $request->user()->tenant;
        $tenantServices = $tenant->services()->with('service')->get();

        if ($tenantServices->isEmpty()) {
            return redirect()->route('onboarding.services')->withErrors(['services' => 'Please select at least one service before setting pricing.']);
        }

        return view('onboarding.pricing', compact('tenantServices'));
    }

    public function postPricing(Request $request)
    {
        $tenant = $request->user()->tenant;
        $request->validate([
            'global_markup' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'markup' => ['nullable', 'array'],
            'markup.*' => ['numeric', 'min:0', 'max:1000'],
        ]);

        DB::transaction(function () use ($tenant, $request) {
            $tenantServices = $tenant->services()->with('service')->get();
            $globalMarkup = $request->input('global_markup');

            foreach ($tenantServices as $tService) {
                $service = $tService->service;
                $markupValue = isset($request->markup[$tService->id])
                    ? floatval($request->markup[$tService->id])
                    : ($globalMarkup !== null ? floatval($globalMarkup) : 20.0);

                // Server-side calculation: base_price + (base_price * markupValue / 100)
                $sellingPrice = round($service->base_price * (1 + ($markupValue / 100)), 4);

                $tService->update([
                    'markup_type' => 'percentage',
                    'markup_value' => $markupValue,
                    'selling_price' => $sellingPrice,
                ]);
            }

            $tenant->update(['onboarding_step' => 'store']);
        });

        return redirect()->route('onboarding.store');
    }

    public function showStore(Request $request)
    {
        $tenant = $request->user()->tenant;
        $store = $tenant->stores()->first();

        return view('onboarding.store', compact('tenant', 'store'));
    }

    public function postStore(Request $request)
    {
        $tenant = $request->user()->tenant;
        $existingStore = $tenant->stores()->first();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'slug' => [
                'required',
                'string',
                'max:255',
                new ReservedSlug(),
                'unique:stores,slug,' . ($existingStore ? $existingStore->id : 'NULL'),
            ],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $logoPath = $existingStore ? $existingStore->logo : null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('stores', 'public');
        }

        Store::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => $request->name,
                'slug' => Str::slug($request->slug),
                'description' => $request->description,
                'logo' => $logoPath,
                'status' => 'active',
            ]
        );

        $tenant->update(['onboarding_step' => 'bank']);

        return redirect()->route('onboarding.bank');
    }

    public function showBank(Request $request)
    {
        $tenant = $request->user()->tenant;
        $bank = $tenant->bankAccounts()->first();

        return view('onboarding.bank', compact('bank'));
    }

    public function postBank(Request $request)
    {
        $tenant = $request->user()->tenant;
        $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:255'],
        ]);

        BankAccount::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'bank_code' => 'DUMMY',
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'verification_status' => 'pending',
                'is_default' => true,
            ]
        );

        $tenant->update(['onboarding_step' => 'review']);

        return redirect()->route('onboarding.review');
    }

    public function showReview(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $store = $tenant->stores()->first();
        $bank = $tenant->bankAccounts()->first();
        $tenantServices = $tenant->services()->with('service')->get();

        return view('onboarding.review', compact('user', 'tenant', 'store', 'bank', 'tenantServices'));
    }

    public function postReview(Request $request)
    {
        $tenant = $request->user()->tenant;

        if ($tenant->services()->count() === 0) {
            return redirect()->route('onboarding.services')->withErrors(['services' => 'Please select at least one service before completing onboarding.']);
        }

        if (!$tenant->stores()->exists()) {
            return redirect()->route('onboarding.store')->withErrors(['store' => 'Please configure your store before completing onboarding.']);
        }

        DB::transaction(function () use ($tenant) {
            $tenant->update([
                'onboarding_step' => 'complete',
                'onboarding_completed_at' => now(),
                'status' => 'active',
            ]);
        });

        return redirect()->route('onboarding.complete');
    }

    public function showComplete(Request $request)
    {
        $tenant = $request->user()->tenant;
        $store = $tenant->stores()->first();

        return view('onboarding.complete', compact('tenant', 'store'));
    }
}
