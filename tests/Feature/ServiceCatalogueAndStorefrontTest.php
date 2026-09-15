<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\TenantService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogueAndStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $resellerA;
    protected Tenant $tenantA;
    protected Store $storeA;
    protected User $resellerB;
    protected Tenant $tenantB;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->resellerA = User::factory()->create(['username' => 'reseller_a']);
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
            'slug' => 'reseller_a',
            'status' => 'active',
        ]);

        $this->resellerB = User::factory()->create(['username' => 'reseller_b']);
        $this->resellerB->roles()->attach($resellerRole);
        $this->tenantB = Tenant::create([
            'owner_user_id' => $this->resellerB->id,
            'name' => 'Tenant B Store',
            'slug' => 'tenant-b-store',
            'status' => 'active',
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ]);

        $this->service = Service::create([
            'name' => 'Instagram Followers',
            'slug' => 'instagram-followers',
            'platform' => 'Instagram',
            'category' => 'Followers',
            'base_price' => 2.0000,
            'selling_price' => 3.0000,
            'markup_value' => 50,
            'min_quantity' => 100,
            'max_quantity' => 50000,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_and_manage_platform_services()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/services');
        $response->assertStatus(200);

        $response = $this->post('/admin/services', [
            'name' => 'TikTok Views Pro',
            'platform' => 'TikTok',
            'category' => 'Views',
            'base_price' => 0.1000,
            'markup_value' => 100,
            'min_quantity' => 1000,
            'max_quantity' => 1000000,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/services');
        $this->assertDatabaseHas('services', ['name' => 'TikTok Views Pro']);
    }

    public function test_reseller_can_enable_service_and_configure_markup()
    {
        $this->actingAs($this->resellerA);

        $response = $this->post("/reseller/services/{$this->service->id}/toggle");
        $response->assertRedirect();

        $this->assertDatabaseHas('tenant_services', [
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'status' => 'active',
        ]);

        $ts = TenantService::first();
        $response = $this->put("/reseller/services/{$ts->id}", [
            'markup_type' => 'percentage',
            'markup_value' => 100, // 100% markup on $2 base = $4.00
        ]);

        $response->assertRedirect('/reseller/services');
        $this->assertEquals(4.0000, $ts->fresh()->selling_price);
    }

    public function test_public_storefront_displays_only_tenant_enabled_services()
    {
        // Enable service for Tenant A
        TenantService::create([
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'selling_price' => 4.0000,
            'status' => 'active',
        ]);

        $response = $this->get('/store/reseller_a');
        $response->assertStatus(200);
        $response->assertSee('Reseller A Store');
        $response->assertSee('Instagram Followers');

        // Tenant B storefront should not show Tenant A's services
        $responseB = $this->get('/store/reseller_b');
        $responseB->assertStatus(404); // Reseller B has no store record yet
    }

    public function test_storefront_summary_recalculates_price_server_side()
    {
        $ts = TenantService::create([
            'tenant_id' => $this->tenantA->id,
            'service_id' => $this->service->id,
            'selling_price' => 5.0000, // $5 per 1k
            'status' => 'active',
        ]);

        $response = $this->postJson('/store/reseller_a/summary', [
            'tenant_service_id' => $ts->id,
            'quantity' => 2000, // 2k * $5/1k = $10 total
            'target' => 'https://instagram.com/myaccount',
            'fake_client_price' => 0.01, // Client attempting price manipulation!
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'quantity' => 2000,
            'unit_price_per_k' => 5.0000,
            'total_price' => 10.0000, // Correct server-calculated price!
        ]);
    }

    public function test_reseller_a_cannot_edit_reseller_b_tenant_service()
    {
        $tsB = TenantService::create([
            'tenant_id' => $this->tenantB->id,
            'service_id' => $this->service->id,
            'selling_price' => 10.0000,
            'status' => 'active',
        ]);

        $this->actingAs($this->resellerA);

        $response = $this->put("/reseller/services/{$tsB->id}", [
            'markup_type' => 'fixed_price',
            'markup_value' => 0.01,
        ]);

        $response->assertStatus(403);
        $this->assertEquals(10.0000, $tsB->fresh()->selling_price);
    }
}
