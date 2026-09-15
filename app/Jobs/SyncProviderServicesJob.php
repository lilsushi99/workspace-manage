<?php

namespace App\Jobs;

use App\Models\Provider;
use App\Services\Providers\ProviderSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProviderServicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Provider $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(ProviderSyncService $syncService): void
    {
        $syncService->sync($this->provider);
    }
}
