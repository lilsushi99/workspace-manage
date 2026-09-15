<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\ContentItem;
use App\Models\ContentSection;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\TenantService;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Full platform access and root administrative privileges',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Platform administrator access',
        ]);

        $supportRole = Role::create([
            'name' => 'Support',
            'slug' => 'support',
            'description' => 'Support staff access to manage customer tickets',
        ]);

        $financeRole = Role::create([
            'name' => 'Finance',
            'slug' => 'finance',
            'description' => 'Financial administrator for ledger and payout reviews',
        ]);

        $resellerRole = Role::create([
            'name' => 'Reseller',
            'slug' => 'reseller',
            'description' => 'SMM reseller account owning stores and client services',
        ]);

        // 2. Platform Staff Users
        $superAdminUser = User::create([
            'first_name' => 'Root',
            'last_name' => 'SuperAdmin',
            'username' => 'superadmin',
            'email' => 'superadmin@dowa.io',
            'phone' => '+12345678900',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
        $superAdminUser->roles()->attach($superAdminRole);

        $adminUser = User::create([
            'first_name' => 'Alex',
            'last_name' => 'Manager',
            'username' => 'admin_alex',
            'email' => 'admin@dowa.io',
            'phone' => '+12345678901',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
        $adminUser->roles()->attach($adminRole);

        $supportUser = User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Helper',
            'username' => 'support_sarah',
            'email' => 'support@dowa.io',
            'phone' => '+12345678902',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
        $supportUser->roles()->attach($supportRole);

        $financeUser = User::create([
            'first_name' => 'Fiona',
            'last_name' => 'Banker',
            'username' => 'finance_fiona',
            'email' => 'finance@dowa.io',
            'phone' => '+12345678903',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
        $financeUser->roles()->attach($financeRole);

        // 3. Resellers & Tenants
        $resellersData = [
            [
                'first_name' => 'David',
                'last_name' => 'Boost',
                'username' => 'david_boost',
                'email' => 'david@boostmedia.com',
                'phone' => '+12345678910',
                'tenant_name' => 'BoostMedia Empire',
                'store_name' => 'BoostMedia Store',
                'store_slug' => 'boostmedia',
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Viral',
                'username' => 'elena_viral',
                'email' => 'elena@viralsocial.io',
                'phone' => '+12345678911',
                'tenant_name' => 'ViralSocial Agency',
                'store_name' => 'ViralSocial Hub',
                'store_slug' => 'viralsocial',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Growth',
                'username' => 'michael_growth',
                'email' => 'michael@growthsm.com',
                'phone' => '+12345678912',
                'tenant_name' => 'GrowthSM Solutions',
                'store_name' => 'GrowthSM Market',
                'store_slug' => 'growthsm',
            ],
        ];

        $tenants = [];
        $stores = [];
        $wallets = [];

        foreach ($resellersData as $rData) {
            $rUser = User::create([
                'first_name' => $rData['first_name'],
                'last_name' => $rData['last_name'],
                'username' => $rData['username'],
                'email' => $rData['email'],
                'phone' => $rData['phone'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
            ]);
            $rUser->roles()->attach($resellerRole);

            $tenant = Tenant::create([
                'owner_user_id' => $rUser->id,
                'name' => $rData['tenant_name'],
                'slug' => Str::slug($rData['tenant_name']),
                'status' => 'active',
                'currency' => 'USD',
                'timezone' => 'UTC',
            ]);
            $tenants[] = $tenant;

            $store = Store::create([
                'tenant_id' => $tenant->id,
                'name' => $rData['store_name'],
                'slug' => $rData['store_slug'],
                'description' => 'Premium Social Media Growth Services',
                'logo' => 'stores/logo-' . $tenant->id . '.png',
                'favicon' => 'stores/favicon-' . $tenant->id . '.ico',
                'primary_color' => '#4F46E5',
                'secondary_color' => '#10B981',
                'status' => 'active',
            ]);
            $stores[] = $store;

            $wallet = Wallet::create([
                'tenant_id' => $tenant->id,
                'currency' => 'USD',
                'balance' => 1250.50,
                'available_balance' => 1100.00,
                'pending_balance' => 150.50,
            ]);
            $wallets[] = $wallet;
        }

        // 4. Provider & Provider Services
        $provider = Provider::create([
            'name' => 'Really Simple Social',
            'slug' => 'really-simple-social',
            'api_url' => 'https://reallysimplesocial.example.com/api/v2',
            'api_key' => 'rss_dummy_api_key_placeholder_1234567890',
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $pServicesData = [
            [
                'external_id' => '101',
                'name' => 'Instagram Real Likes [Instant] [Max 50k]',
                'category' => 'Instagram Likes',
                'platform' => 'Instagram',
                'min' => 100,
                'max' => 50000,
                'price' => 0.4500,
            ],
            [
                'external_id' => '102',
                'name' => 'Instagram Organic Followers [30 Days Refill]',
                'category' => 'Instagram Followers',
                'platform' => 'Instagram',
                'min' => 50,
                'max' => 20000,
                'price' => 1.8000,
            ],
            [
                'external_id' => '201',
                'name' => 'TikTok High Quality Views [Fast Speed]',
                'category' => 'TikTok Views',
                'platform' => 'TikTok',
                'min' => 1000,
                'max' => 1000000,
                'price' => 0.0500,
            ],
            [
                'external_id' => '301',
                'name' => 'YouTube High Retention Views [Non Drop]',
                'category' => 'YouTube Views',
                'platform' => 'YouTube',
                'min' => 500,
                'max' => 100000,
                'price' => 2.5000,
            ],
        ];

        $providerServices = [];
        $internalServices = [];

        foreach ($pServicesData as $ps) {
            $pService = ProviderService::create([
                'provider_id' => $provider->id,
                'external_service_id' => $ps['external_id'],
                'name' => $ps['name'],
                'category' => $ps['category'],
                'platform' => $ps['platform'],
                'description' => 'Provider description for ' . $ps['name'],
                'min_quantity' => $ps['min'],
                'max_quantity' => $ps['max'],
                'provider_price' => $ps['price'],
                'provider_currency' => 'USD',
                'supports_refill' => true,
                'supports_cancel' => true,
                'supports_drip_feed' => false,
                'status' => 'active',
            ]);
            $providerServices[] = $pService;

            $service = Service::create([
                'provider_id' => $provider->id,
                'provider_service_id' => $pService->id,
                'name' => 'Dowa ' . $ps['name'],
                'slug' => Str::slug('Dowa ' . $ps['name']),
                'platform' => $ps['platform'],
                'category' => $ps['category'],
                'description' => 'Premium Dowa catalog service: ' . $ps['name'],
                'base_price' => $ps['price'],
                'selling_price' => $ps['price'] * 1.5,
                'markup_type' => 'percentage',
                'markup_value' => 50.0000,
                'min_quantity' => $ps['min'],
                'max_quantity' => $ps['max'],
                'status' => 'active',
                'sort_order' => 1,
            ]);
            $internalServices[] = $service;
        }

        // 5. Tenant Services
        foreach ($tenants as $tenant) {
            foreach ($internalServices as $service) {
                TenantService::create([
                    'tenant_id' => $tenant->id,
                    'service_id' => $service->id,
                    'selling_price' => $service->selling_price * 1.2,
                    'markup_type' => 'percentage',
                    'markup_value' => 20.0000,
                    'status' => 'active',
                ]);
            }
        }

        // 6. Customers, Orders, Payments & Financial Records for each Tenant
        foreach ($tenants as $idx => $tenant) {
            $store = $stores[$idx];
            $wallet = $wallets[$idx];

            // Bank Account
            $bank = BankAccount::create([
                'tenant_id' => $tenant->id,
                'bank_code' => '058',
                'bank_name' => 'GTBank (Development Dummy)',
                'account_number' => '012345678' . $idx,
                'account_name' => $tenant->name . ' Treasury Account',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_default' => true,
            ]);

            // Customers
            for ($c = 1; $c <= 3; $c++) {
                $customer = Customer::create([
                    'tenant_id' => $tenant->id,
                    'first_name' => 'Customer' . $c,
                    'last_name' => 'Client',
                    'email' => 'client' . $c . '_t' . $tenant->id . '@example.com',
                    'phone' => '+1800555010' . $c,
                    'password' => Hash::make('customer123'),
                    'status' => 'active',
                ]);

                // Orders
                $service = $internalServices[$c % count($internalServices)];
                $orderNum = 'ORD-' . strtoupper(Str::random(8));

                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'store_id' => $store->id,
                    'service_id' => $service->id,
                    'provider_id' => $provider->id,
                    'order_number' => $orderNum,
                    'provider_order_id' => 'RSS-PROV-' . rand(10000, 99999),
                    'target' => 'https://instagram.com/dowa_user_' . rand(100, 999),
                    'quantity' => 1000,
                    'provider_unit_cost' => $service->base_price,
                    'provider_total_cost' => $service->base_price,
                    'customer_unit_price' => $service->selling_price * 1.2,
                    'customer_total_price' => $service->selling_price * 1.2,
                    'profit_amount' => ($service->selling_price * 1.2) - $service->base_price,
                    'status' => 'completed',
                    'provider_status' => 'Completed',
                    'payment_status' => 'paid',
                    'started_at' => now()->subDays(2),
                    'completed_at' => now()->subDay(),
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => 'pending',
                    'new_status' => 'completed',
                    'source' => 'provider_webhook',
                    'message' => 'Order successfully fulfilled by provider',
                ]);

                $payment = Payment::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'reference' => 'PAY-' . strtoupper(Str::random(10)),
                    'provider_reference' => 'FLW-TX-' . rand(1000000, 9999999),
                    'gateway' => 'flutterwave',
                    'amount' => $order->customer_total_price,
                    'currency' => 'USD',
                    'status' => 'successful',
                    'payment_method' => 'card',
                    'paid_at' => now()->subDays(2),
                    'metadata' => ['card_type' => 'visa', 'last4' => '4242'],
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'tenant_id' => $tenant->id,
                    'reference' => 'TXN-' . strtoupper(Str::random(10)),
                    'type' => 'sale',
                    'amount' => $order->profit_amount,
                    'balance_before' => $wallet->balance - $order->profit_amount,
                    'balance_after' => $wallet->balance,
                    'direction' => 'credit',
                    'status' => 'completed',
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'description' => 'Profit credited for Order #' . $order->order_number,
                ]);
            }

            // Withdrawal
            $withdrawal = Withdrawal::create([
                'tenant_id' => $tenant->id,
                'wallet_id' => $wallet->id,
                'bank_id' => $bank->id,
                'reference' => 'WTH-' . strtoupper(Str::random(8)),
                'amount' => 100.0000,
                'fee' => 2.0000,
                'net_amount' => 98.0000,
                'account_number' => $bank->account_number,
                'account_name' => $bank->account_name,
                'status' => 'pending',
                'requested_at' => now()->subHours(5),
            ]);
        }

        // 7. CMS Content
        $heroSection = ContentSection::create([
            'key' => 'hero',
            'title' => 'Start Your SMM Reseller Empire Today',
            'subtitle' => 'Turnkey Social Media Reselling SaaS',
            'description' => 'Launch your custom-branded SMM store in minutes. Seamless provider sync, built-in payments, and multi-tenant ledger.',
            'status' => 'active',
        ]);

        ContentItem::create([
            'section_id' => $heroSection->id,
            'title' => 'Instant Storefront Setup',
            'description' => 'Custom domains, tailored branding, and automated order processing.',
            'icon' => 'storefront',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        ContentItem::create([
            'section_id' => $heroSection->id,
            'title' => 'Automated Profit Ledger',
            'description' => 'Track costs, set markup pricing, and withdraw profits smoothly.',
            'icon' => 'wallet',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        // 8. Audit Log record
        AuditLog::create([
            'user_id' => $superAdminUser->id,
            'action' => 'system_seed',
            'entity_type' => 'Database',
            'entity_id' => 1,
            'new_values' => ['message' => 'Database seeded with Phase 1 realistic data'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'CLI/Seeder',
        ]);
    }
}
