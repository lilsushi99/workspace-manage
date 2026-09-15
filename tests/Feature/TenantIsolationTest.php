<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Role;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected Tenant $tenantA;
    protected User $userB;
    protected Tenant $tenantB;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);

        // Tenant A setup
        $this->userA = User::factory()->create();
        $this->userA->roles()->attach($resellerRole);
        $this->tenantA = Tenant::create([
            'owner_user_id' => $this->userA->id,
            'name' => 'Tenant A Business',
            'slug' => 'tenant-a',
            'status' => 'active',
        ]);

        // Tenant B setup
        $this->userB = User::factory()->create();
        $this->userB->roles()->attach($resellerRole);
        $this->tenantB = Tenant::create([
            'owner_user_id' => $this->userB->id,
            'name' => 'Tenant B Business',
            'slug' => 'tenant-b',
            'status' => 'active',
        ]);

        // Admin User setup
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);
    }

    public function test_tenant_a_cannot_view_or_update_tenant_b_store()
    {
        $storeB = Store::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Store B',
            'slug' => 'store-b',
            'status' => 'active',
        ]);

        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $storeB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('update', $storeB));

        // Admin should be able to view/update
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('view', $storeB));
    }

    public function test_tenant_a_cannot_access_tenant_b_customer()
    {
        $customerB = Customer::create([
            'tenant_id' => $this->tenantB->id,
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@tenantb.com',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $customerB));
        $this->assertTrue(Gate::forUser($this->userB)->allows('view', $customerB));
    }

    public function test_tenant_a_cannot_access_tenant_b_order()
    {
        $orderB = Order::create([
            'tenant_id' => $this->tenantB->id,
            'order_number' => 'ORD-B-100',
            'target' => 'https://instagram.com/p/123',
            'quantity' => 1000,
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $orderB));
        $this->assertTrue(Gate::forUser($this->userB)->allows('view', $orderB));
    }

    public function test_tenant_a_cannot_access_tenant_b_wallet()
    {
        $walletB = Wallet::create([
            'tenant_id' => $this->tenantB->id,
            'currency' => 'USD',
            'balance' => 500,
            'available_balance' => 500,
        ]);

        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $walletB));
        $this->assertTrue(Gate::forUser($this->userB)->allows('view', $walletB));
    }

    public function test_tenant_a_cannot_access_tenant_b_withdrawal()
    {
        $walletB = Wallet::create([
            'tenant_id' => $this->tenantB->id,
            'currency' => 'USD',
            'balance' => 500,
        ]);

        $withdrawalB = Withdrawal::create([
            'tenant_id' => $this->tenantB->id,
            'wallet_id' => $walletB->id,
            'reference' => 'WTH-B-1',
            'amount' => 100,
            'fee' => 0,
            'net_amount' => 100,
            'account_number' => '1234567890',
            'account_name' => 'Tenant B Account',
            'status' => 'pending',
        ]);

        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $withdrawalB));
        $this->assertTrue(Gate::forUser($this->userB)->allows('view', $withdrawalB));
    }
}
