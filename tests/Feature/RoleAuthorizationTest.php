<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_can_be_assigned_to_user()
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user->roles()->attach($adminRole);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_super_admin_gate_bypasses()
    {
        $user = User::factory()->create();
        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        $user->roles()->attach($superAdminRole);

        $this->assertTrue(Gate::forUser($user)->allows('access-admin'));
        $this->assertTrue(Gate::forUser($user)->allows('access-support'));
        $this->assertTrue(Gate::forUser($user)->allows('access-finance'));
    }

    public function test_reseller_cannot_access_admin_gate()
    {
        $user = User::factory()->create();
        $resellerRole = Role::create(['name' => 'Reseller', 'slug' => 'reseller']);
        $user->roles()->attach($resellerRole);

        $this->assertTrue(Gate::forUser($user)->allows('access-reseller'));
        $this->assertFalse(Gate::forUser($user)->allows('access-admin'));
        $this->assertFalse(Gate::forUser($user)->allows('access-finance'));
    }
}
