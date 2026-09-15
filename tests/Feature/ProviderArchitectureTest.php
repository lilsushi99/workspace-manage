<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\Role;
use App\Models\User;
use App\Services\Providers\Exceptions\ProviderAuthenticationException;
use App\Services\Providers\ProviderManager;
use App\Services\Providers\ProviderSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected Provider $provider;
    protected User $admin;
    protected User $reseller;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->reseller = User::factory()->create();
        $this->reseller->roles()->attach($resellerRole);

        $this->provider = Provider::create([
            'name' => 'Really Simple Social',
            'slug' => 'really-simple-social',
            'api_url' => 'https://reallysimplesocial.example.com/api/v2',
            'api_key' => 'dummy_secret_key_123',
            'status' => 'active',
            'currency' => 'USD',
        ]);
    }

    public function test_provider_manager_resolves_adapter_by_slug()
    {
        $manager = app(ProviderManager::class);
        $adapter = $manager->makeBySlug('really-simple-social');

        $this->assertNotNull($adapter);
    }

    public function test_provider_service_sync_fetches_and_persists_services()
    {
        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response([
                [
                    'service' => '101',
                    'name' => 'Instagram Likes',
                    'category' => 'Instagram Likes',
                    'rate' => '0.45',
                    'min' => '100',
                    'max' => '50000',
                ],
                [
                    'service' => '201',
                    'name' => 'TikTok Views',
                    'category' => 'TikTok Views',
                    'rate' => '0.05',
                    'min' => '1000',
                    'max' => '1000000',
                ],
            ], 200),
        ]);

        $syncService = app(ProviderSyncService::class);
        $result = $syncService->sync($this->provider);

        $this->assertEquals(2, $result['total']);
        $this->assertDatabaseHas('provider_services', [
            'provider_id' => $this->provider->id,
            'external_service_id' => '101',
            'name' => 'Instagram Likes',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('services', [
            'provider_id' => $this->provider->id,
            'name' => 'Dowa Instagram Likes',
        ]);
    }

    public function test_missing_provider_services_are_deactivated_not_deleted()
    {
        ProviderService::create([
            'provider_id' => $this->provider->id,
            'external_service_id' => '999', // Disappeared service
            'name' => 'Old Discontinued Service',
            'provider_price' => 1.0000,
            'status' => 'active',
        ]);

        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response([
                [
                    'service' => '101',
                    'name' => 'Instagram Likes',
                    'category' => 'Instagram Likes',
                    'rate' => '0.45',
                    'min' => '100',
                    'max' => '50000',
                ],
            ], 200),
        ]);

        $syncService = app(ProviderSyncService::class);
        $syncService->sync($this->provider);

        $this->assertDatabaseHas('provider_services', [
            'external_service_id' => '999',
            'status' => 'inactive',
        ]);
    }

    public function test_reseller_cannot_access_admin_provider_routes()
    {
        $this->actingAs($this->reseller);

        $response = $this->get('/admin/providers');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_provider_routes()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/providers');
        $response->assertStatus(200);
        $response->assertSee('Really Simple Social');
    }

    public function test_provider_401_throws_provider_authentication_exception()
    {
        Http::fake([
            'reallysimplesocial.example.com/*' => Http::response(['error' => 'Invalid API key'], 401),
        ]);

        $this->expectException(ProviderAuthenticationException::class);

        $manager = app(ProviderManager::class);
        $adapter = $manager->make($this->provider);
        $adapter->getBalance();
    }
}
