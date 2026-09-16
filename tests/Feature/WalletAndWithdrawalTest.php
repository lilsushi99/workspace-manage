<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\Finance\WalletLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAndWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $resellerUser;
    protected Tenant $tenant;
    protected Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $resellerRole = Role::firstOrCreate(['slug' => 'reseller'], ['name' => 'Reseller']);

        $this->superAdmin = User::factory()->create(['email' => 'admin@dowa.test']);
        $this->superAdmin->roles()->attach($superAdminRole);

        $this->resellerUser = User::factory()->create([
            'email' => 'reseller@dowa.test',
            'username' => 'testreseller',
        ]);
        $this->resellerUser->roles()->attach($resellerRole);

        $this->tenant = Tenant::create([
            'owner_user_id' => $this->resellerUser->id,
            'name' => 'Test Reseller Store',
            'slug' => 'testreseller',
            'status' => 'active',
            'onboarding_step' => 'completed',
            'onboarding_completed_at' => now(),
        ]);

        $this->wallet = Wallet::create([
            'tenant_id' => $this->tenant->id,
            'currency' => 'USD',
            'balance' => 0.0000,
            'available_balance' => 0.0000,
            'pending_balance' => 0.0000,
        ]);
    }

    public function test_wallet_ledger_records_order_profit_idempotently()
    {
        $service = app(WalletLedgerService::class);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => 1,
            'store_id' => 1,
            'service_id' => 1,
            'provider_id' => 1,
            'order_number' => 'ORD-1001',
            'target' => 'https://instagram.com/p/123',
            'quantity' => 100,
            'provider_unit_cost' => 1.0000,
            'provider_total_cost' => 1.0000,
            'customer_unit_price' => 2.5000,
            'customer_total_price' => 2.5000,
            'profit_amount' => 1.5000,
            'status' => 'pending',
            'provider_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => 1,
            'order_id' => $order->id,
            'reference' => 'PAY-1001',
            'gateway' => 'flutterwave',
            'amount' => 2.5000,
            'currency' => 'USD',
            'status' => 'successful',
        ]);

        // First call
        $tx1 = $service->recordOrderProfit($order, $payment);
        $this->assertNotNull($tx1);
        $this->assertEquals('sale', $tx1->type);
        $this->assertEquals(1.5000, $tx1->amount);

        $this->wallet->refresh();
        $this->assertEquals(1.5000, (float) $this->wallet->balance);
        $this->assertEquals(1.5000, (float) $this->wallet->available_balance);

        // Second call (idempotent duplicate test)
        $tx2 = $service->recordOrderProfit($order, $payment);
        $this->assertEquals($tx1->id, $tx2->id);

        $this->wallet->refresh();
        $this->assertEquals(1.5000, (float) $this->wallet->balance);
        $this->assertEquals(1, WalletTransaction::where('tenant_id', $this->tenant->id)->count());
    }

    public function test_reseller_can_add_bank_account()
    {
        $response = $this->actingAs($this->resellerUser)->post(route('reseller.banks.store'), [
            'bank_code' => '044',
            'bank_name' => 'Access Bank',
            'account_number' => '0123456789',
            'account_name' => 'Test Store Ltd',
            'is_default' => 1,
        ]);

        $response->assertRedirect(route('reseller.banks.index'));
        $this->assertDatabaseHas('bank_accounts', [
            'tenant_id' => $this->tenant->id,
            'account_number' => '0123456789',
            'is_default' => 1,
        ]);
    }

    public function test_reseller_can_request_withdrawal_when_balance_sufficient()
    {
        // Fund wallet
        $this->wallet->update([
            'balance' => 100.0000,
            'available_balance' => 100.0000,
        ]);

        $bank = BankAccount::create([
            'tenant_id' => $this->tenant->id,
            'bank_code' => '044',
            'bank_name' => 'Access Bank',
            'account_number' => '0123456789',
            'account_name' => 'Test Store Ltd',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->resellerUser)->post(route('reseller.wallet.withdraw.store'), [
            'bank_account_id' => $bank->id,
            'amount' => 50.00,
        ]);

        $response->assertRedirect(route('reseller.wallet.index'));

        $this->wallet->refresh();
        $this->assertEquals(100.0000, (float) $this->wallet->balance);
        $this->assertEquals(50.0000, (float) $this->wallet->available_balance);
        $this->assertEquals(50.0000, (float) $this->wallet->pending_balance);

        $withdrawal = Withdrawal::where('tenant_id', $this->tenant->id)->first();
        $this->assertNotNull($withdrawal);
        $this->assertEquals('pending', $withdrawal->status);
        $this->assertEquals(50.0000, (float) $withdrawal->amount);
    }

    public function test_reseller_cannot_request_withdrawal_if_balance_insufficient()
    {
        $this->wallet->update([
            'balance' => 10.0000,
            'available_balance' => 10.0000,
        ]);

        $bank = BankAccount::create([
            'tenant_id' => $this->tenant->id,
            'bank_code' => '044',
            'bank_name' => 'Access Bank',
            'account_number' => '0123456789',
            'account_name' => 'Test Store Ltd',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->resellerUser)->post(route('reseller.wallet.withdraw.store'), [
            'bank_account_id' => $bank->id,
            'amount' => 50.00,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_admin_can_approve_withdrawal()
    {
        $this->wallet->update([
            'balance' => 100.0000,
            'available_balance' => 50.0000,
            'pending_balance' => 50.0000,
        ]);

        $withdrawal = Withdrawal::create([
            'tenant_id' => $this->tenant->id,
            'wallet_id' => $this->wallet->id,
            'reference' => 'WTH-TEST-1',
            'amount' => 50.0000,
            'fee' => 0.0000,
            'net_amount' => 50.0000,
            'account_number' => '0123456789',
            'account_name' => 'Test Store Ltd',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.withdrawals.approve', $withdrawal), [
            'notes' => 'Payout completed via bank portal',
        ]);

        $response->assertRedirect(route('admin.withdrawals.index'));

        $withdrawal->refresh();
        $this->assertEquals('paid', $withdrawal->status);
        $this->assertNotNull($withdrawal->processed_at);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->superAdmin->id,
            'entity_type' => 'Withdrawal',
            'entity_id' => $withdrawal->id,
            'action' => 'withdrawal_approved',
        ]);
    }

    public function test_admin_can_reject_withdrawal_and_release_funds()
    {
        $this->wallet->update([
            'balance' => 100.0000,
            'available_balance' => 50.0000,
            'pending_balance' => 50.0000,
        ]);

        $withdrawal = Withdrawal::create([
            'tenant_id' => $this->tenant->id,
            'wallet_id' => $this->wallet->id,
            'reference' => 'WTH-TEST-2',
            'amount' => 50.0000,
            'fee' => 0.0000,
            'net_amount' => 50.0000,
            'account_number' => '0123456789',
            'account_name' => 'Test Store Ltd',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.withdrawals.reject', $withdrawal), [
            'rejection_reason' => 'Account name mismatch',
        ]);

        $response->assertRedirect(route('admin.withdrawals.index'));

        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        $this->assertEquals('Account name mismatch', $withdrawal->rejection_reason);

        // Verify reserved funds released back to available balance
        $this->wallet->refresh();
        $this->assertEquals(100.0000, (float) $this->wallet->balance);
        $this->assertEquals(100.0000, (float) $this->wallet->available_balance);
        $this->assertEquals(0.0000, (float) $this->wallet->pending_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'tenant_id' => $this->tenant->id,
            'withdrawal_id' => $withdrawal->id,
            'type' => 'withdrawal_reversal',
            'amount' => 50.0000,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->superAdmin->id,
            'entity_type' => 'Withdrawal',
            'entity_id' => $withdrawal->id,
            'action' => 'withdrawal_rejected',
        ]);
    }
}
