<?php

namespace Tests\Feature;

use App\Jobs\SubmitOrderToProviderJob;
use App\Jobs\SyncOrderStatusJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Providers\ProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderFulfilmentAndOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $resellerA;
    protected Tenant $tenantA;
    protected Store $storeA;
    protected User $resellerB;
    protected Tenant $tenantB;
    protected Provider $provider;
    protected ProviderService $pService;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->resellerA = User::factory()->create(['username' => 'reseller_owner_a']);
        $this->resellerA->roles()->attach($resellerRole);
        $this->tenantA = Tenant::create([
            'owner_user_id' => $this->resellerA->id,
            'name' => 'Tenant A Store',
            'slug' => 'tenant-a-store',
            'status' => 'active',
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ]);
        $this->storeA = Store::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Reseller A Store',
            'slug' => 'reseller_owner_a',
            'status' => 'active',
        ]);

        $this->resellerB = User::factory()->create(['username' => 'reseller_owner_b']);
        $this->resellerB->roles()->attach($resellerRole);
        $this->tenantB = Tenant::create([
            'owner_user_id' => $this->resellerB->id,
            'name' => 'Tenant B Store',
            'slug' => 'tenant-b-store',
            'status' => 'active',
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ]);

        $this->provider = Provider::create([
            'name' => 'Really Simple Social',
            'slug' => 'really-simple-social',
            'api_url' => 'https://reallysimplesocial.example.com/api/v2',
            'api_key' => 'secret_key_12345',
            'status' => 'active',
        ]);

        $this->pService = ProviderService::create([
            'provider_id' => $this->provider->id,
            'external_service_id' => '101',
            'name' => 'Instagram Likes',
            'provider_price' => 1.0000,
            'status' => 'active',
        ]);

        $this->service = Service::create([
            'provider_id' => $this->provider->id,
            'provider_service_id' => $this->pService->id,
            'name' => 'Dowa Instagram Likes',
            'slug' => 'dowa-instagram-likes',
            'platform' => 'Instagram',
            'category' => 'Likes',
            'base_price' => 1.0000,
            'selling_price' => 2.0000,
            'status' => 'active',
        ]);
    }

    public function test_paid_order_submits_to_provider_and_records_provider_order_id()
    {
        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response([
                'order' => 'RSS-998877',
            ], 200),
        ]);

        $customer = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Client',
            'last_name' => 'One',
            'email' => 'client@example.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customer->id,
            'store_id' => $this->storeA->id,
            'service_id' => $this->service->id,
            'provider_id' => $this->provider->id,
            'order_number' => 'DOWA-ORD-101',
            'target' => 'https://instagram.com/p/test',
            'quantity' => 1000,
            'customer_total_price' => 2.0000,
            'provider_total_cost' => 1.0000,
            'profit_amount' => 1.0000,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $job = new SubmitOrderToProviderJob($order);
        $job->handle(app(ProviderManager::class));

        $this->assertEquals('RSS-998877', $order->fresh()->provider_order_id);
        $this->assertEquals('processing', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'new_status' => 'processing',
            'source' => 'provider_submission',
        ]);
    }

    public function test_unpaid_order_cannot_be_submitted_to_provider()
    {
        Http::fake();

        $customer = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Client',
            'last_name' => 'Unpaid',
            'email' => 'unpaid@example.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customer->id,
            'service_id' => $this->service->id,
            'order_number' => 'DOWA-UNPAID',
            'target' => 'https://instagram.com/p/test',
            'quantity' => 1000,
            'customer_total_price' => 2.0000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $job = new SubmitOrderToProviderJob($order);
        $job->handle(app(ProviderManager::class));

        $this->assertNull($order->fresh()->provider_order_id);
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_order_status_sync_updates_dowa_status_and_history()
    {
        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response([
                'status' => 'Completed',
                'charge' => '1.00',
                'remains' => '0',
            ], 200),
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'provider_id' => $this->provider->id,
            'order_number' => 'DOWA-SYNC-01',
            'provider_order_id' => 'RSS-998877',
            'target' => 'https://instagram.com/p/test',
            'quantity' => 1000,
            'customer_total_price' => 2.0000,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $job = new SyncOrderStatusJob($order);
        $job->handle(app(ProviderManager::class));

        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'new_status' => 'completed',
            'source' => 'provider_sync',
        ]);
    }

    public function test_reseller_a_cannot_view_reseller_b_order_details()
    {
        $orderB = Order::create([
            'tenant_id' => $this->tenantB->id,
            'service_id' => $this->service->id,
            'order_number' => 'DOWA-B-ORDER',
            'target' => 'https://instagram.com/p/b',
            'quantity' => 1000,
            'customer_total_price' => 5.0000,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->resellerA);

        $response = $this->get("/reseller/orders/{$orderB->id}");
        $response->assertStatus(403);
    }

    public function test_admin_can_retry_failed_order_fulfilment()
    {
        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response([
                'order' => 'RSS-RETRY-001',
            ], 200),
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'provider_id' => $this->provider->id,
            'order_number' => 'DOWA-RETRY',
            'target' => 'https://instagram.com/p/retry',
            'quantity' => 500,
            'customer_total_price' => 1.0000,
            'status' => 'failed',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/orders/{$order->id}/retry");
        $response->assertRedirect();

        // Process queued job synchronously for testing assertion
        (new SubmitOrderToProviderJob($order))->handle(app(ProviderManager::class));

        $this->assertEquals('RSS-RETRY-001', $order->fresh()->provider_order_id);
        $this->assertEquals('processing', $order->fresh()->status);
    }
}
