<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Providers\Exceptions\ProviderValidationException;
use App\Services\Providers\ProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitOrderToProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public int $tries = 3;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(ProviderManager $manager): void
    {
        // 1. Check eligibility and lock order record
        $eligibleOrder = DB::transaction(function () {
            $order = Order::where('id', $this->order->id)->lockForUpdate()->first();

            if (!$order || $order->payment_status !== 'paid' || !empty($order->provider_order_id)) {
                return null;
            }

            return $order;
        });

        if (!$eligibleOrder) {
            return;
        }

        $provider = $eligibleOrder->provider ?: ($eligibleOrder->service ? $eligibleOrder->service->provider : null);

        if (!$provider) {
            $eligibleOrder->update(['status' => 'failed']);
            OrderStatusHistory::create([
                'order_id' => $eligibleOrder->id,
                'old_status' => $eligibleOrder->status,
                'new_status' => 'failed',
                'source' => 'system',
                'message' => 'No provider associated with order service.',
            ]);
            return;
        }

        $providerService = $eligibleOrder->service ? $eligibleOrder->service->providerService : null;
        $extServiceId = $providerService ? $providerService->external_service_id : null;

        if (!$extServiceId) {
            $eligibleOrder->update(['status' => 'failed']);
            OrderStatusHistory::create([
                'order_id' => $eligibleOrder->id,
                'old_status' => $eligibleOrder->status,
                'new_status' => 'failed',
                'source' => 'system',
                'message' => 'No external provider service ID mapped.',
            ]);
            return;
        }

        // 2. Perform HTTP Network Request OUTSIDE of DB Transaction
        try {
            $adapter = $manager->make($provider);
            $result = $adapter->createOrder([
                'service_id' => $extServiceId,
                'target' => $eligibleOrder->target,
                'quantity' => $eligibleOrder->quantity,
            ]);

            // 3. Atomically persist provider order reference
            DB::transaction(function () use ($eligibleOrder, $result, $provider) {
                $order = Order::where('id', $eligibleOrder->id)->lockForUpdate()->first();

                if ($order && empty($order->provider_order_id)) {
                    $oldStatus = $order->status;
                    $order->update([
                        'provider_order_id' => $result['provider_order_id'],
                        'status' => 'processing',
                        'started_at' => now(),
                    ]);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'processing',
                        'source' => 'provider_submission',
                        'message' => "Successfully submitted to provider {$provider->name} (Ref: {$result['provider_order_id']}).",
                    ]);
                }
            });
        } catch (ProviderValidationException $e) {
            $eligibleOrder->update(['status' => 'failed']);
            OrderStatusHistory::create([
                'order_id' => $eligibleOrder->id,
                'old_status' => $eligibleOrder->status,
                'new_status' => 'failed',
                'source' => 'provider_error',
                'message' => "Provider validation failed: " . $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to submit order #{$eligibleOrder->order_number} to provider", ['error' => $e->getMessage()]);
            OrderStatusHistory::create([
                'order_id' => $eligibleOrder->id,
                'old_status' => $eligibleOrder->status,
                'new_status' => $eligibleOrder->status,
                'source' => 'provider_error',
                'message' => "Provider submission attempt failed: " . $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
