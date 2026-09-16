<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Providers\ProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(ProviderManager $manager): void
    {
        if (empty($this->order->provider_order_id)) {
            return;
        }

        $provider = $this->order->provider ?: ($this->order->service ? $this->order->service->provider : null);

        if (!$provider) {
            return;
        }

        try {
            $adapter = $manager->make($provider);
            $statusData = $adapter->getOrderStatus($this->order->provider_order_id);

            $rawStatus = strtolower($statusData['status'] ?? '');
            $normalizedStatus = $this->normalizeStatus($rawStatus);

            $oldStatus = $this->order->status;

            if ($normalizedStatus !== $oldStatus) {
                $updateData = [
                    'status' => $normalizedStatus,
                    'provider_status' => $statusData['status'] ?? null,
                ];

                if ($normalizedStatus === 'completed' && !$this->order->completed_at) {
                    $updateData['completed_at'] = now();
                }

                $this->order->update($updateData);

                OrderStatusHistory::create([
                    'order_id' => $this->order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $normalizedStatus,
                    'source' => 'provider_sync',
                    'message' => "Order status synchronized from provider: {$statusData['status']}",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync order #{$this->order->order_number} status", ['error' => $e->getMessage()]);
        }
    }

    protected function normalizeStatus(string $rawStatus): string
    {
        if (str_contains($rawStatus, 'completed') || str_contains($rawStatus, 'done') || str_contains($rawStatus, 'success')) {
            return 'completed';
        }

        if (str_contains($rawStatus, 'cancel') || str_contains($rawStatus, 'refund')) {
            return 'cancelled';
        }

        if (str_contains($rawStatus, 'partial')) {
            return 'partial';
        }

        if (str_contains($rawStatus, 'progress') || str_contains($rawStatus, 'processing') || str_contains($rawStatus, 'pending')) {
            return 'processing';
        }

        return 'processing';
    }
}
