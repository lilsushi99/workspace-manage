<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\TenantService;
use App\Models\User;
use App\Services\Payments\PaymentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerOrderingAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $resellerA;
    protected Tenant $tenantA;
    protected Store $storeA;
    protected Service $service;
    protected TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();

        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);

        $this->resellerA = User::factory()->create(['username' => 'store_owner_a']);
        $this->resellerA->roles()->attach($resellerRole);

        $this->tenantA = Tenant::create([
            'owner_user_id' => $this->resellerA->id,
            'name' => 'Store A Empire',
            'slug' => 'store-a-empire',
            'status' => 'active',
            'currency' => 'USD',
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ]);

        $this->storeA = Store::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Store A Storefront',
            'slug' => 'store_owner_a',
            'status' => 'active',
        ]);

        $this->service = Service::create([
            'name' => 'Instagram Real Likes',
            'slug' => 'instagram-real-likes',
            'platform' => 'Instagram',
            'category' => 'Likes',
            'base_price' => 1.0000,
            'selling_price' => 2.0000,
            'min_quantity' => 100,
            'max_quantity' => 100000,
            'status' => 'active',
        ]);

        $this->tenantService = TenantService::create([
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'selling_price' => 4.0000, // $4.00 per 1k
            'status' => 'active',
        ]);
    }

    public function test_checkout_creates_tenant_scoped_customer_order_and_payment_session()
    {
        Http::fake([
            'api.flutterwave.com/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'link' => 'https://checkout.flutterwave.com/v3/hosted/pay/test_session_123',
                ],
            ], 200),
        ]);

        $response = $this->post('/store/store_owner_a/checkout', [
            'tenant_service_id' => $this->tenantService->id,
            'quantity' => 2000, // 2k * $4/1k = $8.00 customer total
            'target' => 'https://instagram.com/p/test12345',
            'email' => 'client@customer.com',
            'first_name' => 'John',
            'last_name' => 'Client',
            'phone' => '+18005550199',
            'fake_client_price' => 0.01, // Attempted price manipulation!
        ]);

        $response->assertRedirect('https://checkout.flutterwave.com/v3/hosted/pay/test_session_123');

        // Verify Customer created under Tenant A
        $this->assertDatabaseHas('customers', [
            'tenant_id' => $this->tenantA->id,
            'email' => 'client@customer.com',
        ]);

        // Verify Order stored in MySQL with server-calculated price
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenantA->id,
            'quantity' => 2000,
            'customer_unit_price' => 4.0000,
            'customer_total_price' => 8.0000, // $8.00 recalculated server-side
            'provider_unit_cost' => 1.0000,
            'provider_total_cost' => 2.0000, // $2.00
            'profit_amount' => 6.0000, // $6.00 profit
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        // Verify Payment stored in MySQL
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenantA->id,
            'amount' => 8.0000,
            'currency' => 'USD',
            'gateway' => 'flutterwave',
            'status' => 'pending',
        ]);
    }

    public function test_flutterwave_webhook_verifies_and_confirms_payment_idempotently()
    {
        Queue::fake();

        $customer = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'John',
            'last_name' => 'Client',
            'email' => 'client@customer.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customer->id,
            'store_id' => $this->storeA->id,
            'service_id' => $this->service->id,
            'order_number' => 'DOWA-TEST01',
            'target' => 'https://instagram.com/p/test12345',
            'quantity' => 1000,
            'customer_total_price' => 4.0000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'reference' => 'PAY-TEST001',
            'gateway' => 'flutterwave',
            'amount' => 4.0000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        Http::fake([
            'api.flutterwave.com/v3/transactions/998877/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'tx_ref' => 'PAY-TEST001',
                    'id' => 998877,
                    'amount' => 4.0000,
                    'currency' => 'USD',
                    'payment_type' => 'card',
                    'created_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        // Trigger Webhook with valid signature header
        $response = $this->withHeaders([
            'verif-hash' => 'dummy_flw_secret_hash',
        ])->postJson('/webhooks/flutterwave', [
            'event' => 'charge.completed',
            'data' => [
                'id' => 998877,
                'status' => 'successful',
                'tx_ref' => 'PAY-TEST001',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Verify database state updated atomically
        $this->assertEquals('successful', $payment->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('paid', $order->fresh()->status);

        // Duplicate Webhook call should be idempotent and succeed without duplicating records
        $duplicateResponse = $this->withHeaders([
            'verif-hash' => 'dummy_flw_secret_hash',
        ])->postJson('/webhooks/flutterwave', [
            'event' => 'charge.completed',
            'data' => [
                'id' => 998877,
                'status' => 'successful',
                'tx_ref' => 'PAY-TEST001',
            ],
        ]);

        $duplicateResponse->assertStatus(200);
        $this->assertEquals(1, Payment::where('reference', 'PAY-TEST001')->count());
    }

    public function test_webhook_with_invalid_signature_is_rejected()
    {
        $response = $this->withHeaders([
            'verif-hash' => 'invalid_hash_value',
        ])->postJson('/webhooks/flutterwave', [
            'event' => 'charge.completed',
            'data' => ['id' => 12345],
        ]);

        $response->assertStatus(401);
    }
}
