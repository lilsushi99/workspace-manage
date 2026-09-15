<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\ContentItem;
use App\Models\ContentSection;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantService;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_tables_and_relationships_work_correctly()
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $tenant = Tenant::create([
            'owner_user_id' => $user->id,
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
        ]);

        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
        ]);

        $provider = Provider::create([
            'name' => 'Test Provider',
            'slug' => 'test-provider',
            'api_url' => 'https://example.com/api',
            'api_key' => 'secret-key-123',
        ]);

        $pService = ProviderService::create([
            'provider_id' => $provider->id,
            'external_service_id' => '99',
            'name' => 'Ext Service',
            'provider_price' => 1.5000,
        ]);

        $service = Service::create([
            'provider_id' => $provider->id,
            'provider_service_id' => $pService->id,
            'name' => 'Internal Service',
            'slug' => 'internal-service',
            'base_price' => 1.5000,
            'selling_price' => 2.5000,
        ]);

        $tService = TenantService::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'selling_price' => 3.0000,
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'service_id' => $service->id,
            'provider_id' => $provider->id,
            'order_number' => 'ORD-12345',
            'target' => 'https://example.com/target',
            'quantity' => 500,
            'customer_total_price' => 3.0000,
        ]);

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'reference' => 'PAY-12345',
            'amount' => 3.0000,
        ]);

        $wallet = Wallet::create([
            'tenant_id' => $tenant->id,
            'balance' => 100.0000,
        ]);

        $bank = BankAccount::create([
            'tenant_id' => $tenant->id,
            'bank_code' => '058',
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
        ]);

        $withdrawal = Withdrawal::create([
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'bank_id' => $bank->id,
            'reference' => 'WTH-12345',
            'amount' => 50.0000,
            'net_amount' => 50.0000,
            'account_number' => $bank->account_number,
            'account_name' => $bank->account_name,
        ]);

        $txn = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'tenant_id' => $tenant->id,
            'reference' => 'TXN-12345',
            'type' => 'withdrawal',
            'amount' => 50.0000,
            'balance_before' => 100.0000,
            'balance_after' => 50.0000,
            'withdrawal_id' => $withdrawal->id,
        ]);

        $section = ContentSection::create([
            'key' => 'test_section',
            'title' => 'Section Title',
        ]);

        $item = ContentItem::create([
            'section_id' => $section->id,
            'title' => 'Item Title',
        ]);

        // Assertions
        $this->assertEquals('test-tenant', $user->tenant->slug);
        $this->assertCount(1, $tenant->stores);
        $this->assertEquals('Test Store', $tenant->stores->first()->name);
        $this->assertEquals('Internal Service', $order->service->name);
        $this->assertEquals(3.0000, $payment->amount);
        $this->assertEquals('Test Account', $withdrawal->bankAccount->account_name);
        $this->assertEquals('TXN-12345', $withdrawal->tenant->walletTransactions->first()->reference);
        $this->assertEquals('Item Title', $section->items->first()->title);
    }
}
