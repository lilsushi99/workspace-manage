<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Role;
use App\Models\Service;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResellerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($resellerRole);

        $this->tenant = Tenant::create([
            'owner_user_id' => $this->user->id,
            'name' => 'New Reseller Store',
            'slug' => 'new-reseller-store',
            'status' => 'active',
            'onboarding_step' => 'account',
        ]);

        Service::create([
            'name' => 'Instagram Likes',
            'slug' => 'instagram-likes',
            'platform' => 'Instagram',
            'category' => 'Likes',
            'base_price' => 1.0000,
            'selling_price' => 1.5000,
            'status' => 'active',
        ]);
    }

    public function test_incomplete_onboarding_redirects_dashboard_access_to_onboarding()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertRedirect('/onboarding');
    }

    public function test_full_onboarding_wizard_flow()
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        // Step 1: Account
        $response = $this->get('/onboarding/account');
        $response->assertStatus(200);

        $response = $this->post('/onboarding/account', [
            'first_name' => 'UpdatedFirst',
            'last_name' => 'UpdatedLast',
            'phone' => '+1234567890',
        ]);
        $response->assertRedirect('/onboarding/services');
        $this->assertEquals('services', $this->tenant->fresh()->onboarding_step);

        // Step 2: Choose Services
        $response = $this->get('/onboarding/services');
        $response->assertStatus(200);
        $service = Service::first();

        $response = $this->post('/onboarding/services', [
            'services' => [$service->id],
        ]);
        $response->assertRedirect('/onboarding/pricing');
        $this->assertEquals('pricing', $this->tenant->fresh()->onboarding_step);
        $this->assertDatabaseHas('tenant_services', [
            'tenant_id' => $this->tenant->id,
            'service_id' => $service->id,
        ]);

        // Step 3: Pricing
        $tenantService = $this->tenant->services()->first();
        $response = $this->post('/onboarding/pricing', [
            'markup' => [$tenantService->id => 50],
        ]);
        $response->assertRedirect('/onboarding/store');
        $this->assertEquals(1.5000, $tenantService->fresh()->selling_price);

        // Step 4: Store Setup (including Reserved Slug Rejection)
        $response = $this->post('/onboarding/store', [
            'name' => 'Admin Store',
            'slug' => 'admin', // Reserved!
        ]);
        $response->assertSessionHasErrors('slug');

        $logo = UploadedFile::fake()->image('logo.png');
        $response = $this->post('/onboarding/store', [
            'name' => 'Valid SMM Store',
            'slug' => 'valid-smm-store',
            'description' => 'Best SMM services',
            'logo' => $logo,
        ]);
        $response->assertRedirect('/onboarding/bank');
        $this->assertDatabaseHas('stores', [
            'tenant_id' => $this->tenant->id,
            'slug' => 'valid-smm-store',
        ]);

        // Step 5: Bank Details
        $response = $this->post('/onboarding/bank', [
            'bank_name' => 'GTBank',
            'account_number' => '0123456789',
            'account_name' => 'Valid Store Account',
        ]);
        $response->assertRedirect('/onboarding/review');
        $this->assertDatabaseHas('bank_accounts', [
            'tenant_id' => $this->tenant->id,
            'account_number' => '0123456789',
        ]);

        // Step 6: Review & Activate
        $response = $this->get('/onboarding/review');
        $response->assertStatus(200);

        $response = $this->post('/onboarding/review');
        $response->assertRedirect('/onboarding/complete');
        $this->assertTrue($this->tenant->fresh()->isOnboardingComplete());

        // Step 7: Complete screen & Dashboard access
        $response = $this->get('/onboarding/complete');
        $response->assertStatus(200);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_tenant_a_cannot_modify_tenant_b_onboarding_data()
    {
        $otherUser = User::factory()->create();
        $otherTenant = Tenant::create([
            'owner_user_id' => $otherUser->id,
            'name' => 'Tenant B Store',
            'slug' => 'tenant-b-store',
        ]);

        $this->actingAs($this->user);

        // Attempting onboarding actions will update only the authenticated tenant
        $this->post('/onboarding/account', [
            'first_name' => 'HackerName',
            'last_name' => 'HackerLast',
        ]);

        $this->assertEquals('HackerName', $this->user->fresh()->first_name);
        $this->assertNotEquals('HackerName', $otherUser->fresh()->first_name);
    }
}
