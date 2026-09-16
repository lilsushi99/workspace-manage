<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrderStatusJob;
use App\Models\Order;
use Illuminate\Console\Command;

class SyncActiveOrdersCommand extends Command
{
    protected $signature = 'orders:sync-active';

    protected $description = 'Synchronize order status from external SMM providers for active processing orders';

    public function handle(): int
    {
        $orders = Order::whereNotNull('provider_order_id')
            ->whereIn('status', ['paid', 'processing', 'pending'])
            ->get();

        $count = $orders->count();
        $this->info("Found {$count} active orders eligible for status sync.");

        foreach ($orders as $order) {
            SyncOrderStatusJob::dispatch($order);
        }

        $this->info("Dispatched {$count} status sync jobs.");
        return Command::SUCCESS;
    }
}
