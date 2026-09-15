<?php

namespace App\Services\Providers;

use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderSyncService
{
    protected ProviderManager $manager;

    public function __construct(ProviderManager $manager)
    {
        $this->manager = $manager;
    }

    public function sync(Provider $provider): array
    {
        $adapter = $this->manager->make($provider);
        $rawServices = $adapter->getServices();

        $syncedIds = [];
        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($provider, $rawServices, &$syncedIds, &$createdCount, &$updatedCount) {
            foreach ($rawServices as $s) {
                $extId = $s['external_service_id'];
                $syncedIds[] = $extId;

                $pService = ProviderService::updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'external_service_id' => $extId,
                    ],
                    [
                        'name' => $s['name'],
                        'category' => $s['category'],
                        'platform' => $s['platform'],
                        'description' => $s['description'],
                        'min_quantity' => $s['min_quantity'],
                        'max_quantity' => $s['max_quantity'],
                        'provider_price' => $s['provider_price'],
                        'provider_currency' => $s['provider_currency'],
                        'supports_refill' => $s['supports_refill'],
                        'supports_cancel' => $s['supports_cancel'],
                        'supports_drip_feed' => $s['supports_drip_feed'],
                        'status' => 'active',
                        'raw_response' => $s['raw_response'],
                    ]
                );

                if ($pService->wasRecentlyCreated) {
                    $createdCount++;

                    // Map newly discovered provider service into internal Service catalog
                    Service::firstOrCreate(
                        ['provider_service_id' => $pService->id],
                        [
                            'provider_id' => $provider->id,
                            'name' => 'Dowa ' . $pService->name,
                            'slug' => Str::slug('Dowa ' . $pService->name . '-' . $provider->id . '-' . $extId),
                            'platform' => $pService->platform,
                            'category' => $pService->category,
                            'description' => $pService->description,
                            'base_price' => $pService->provider_price,
                            'selling_price' => round($pService->provider_price * 1.5, 4),
                            'markup_type' => 'percentage',
                            'markup_value' => 50.0000,
                            'min_quantity' => $pService->min_quantity,
                            'max_quantity' => $pService->max_quantity,
                            'status' => 'active',
                        ]
                    );
                } else {
                    $updatedCount++;
                }
            }

            // Mark provider services no longer returned in API response as inactive
            ProviderService::where('provider_id', $provider->id)
                ->whereNotIn('external_service_id', $syncedIds)
                ->update(['status' => 'inactive']);
        });

        return [
            'total' => count($syncedIds),
            'created' => $createdCount,
            'updated' => $updatedCount,
        ];
    }
}
