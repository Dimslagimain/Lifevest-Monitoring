<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_has_admin_privileges()
    {
        $user = User::factory()->create(['role' => 'superadmin']);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_admin_has_admin_privileges_but_not_superadmin()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_regular_user_has_no_admin_privileges()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_admin_can_access_update_seats_route()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $registration = 'PK-TNP';

        $response = $this->actingAs($user)
            ->post("/aircraft/{$registration}/update-seats", [
                'seat_ids' => ['1A'],
                'expiry_date' => '2030-01-01',
            ]);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_regular_user_cannot_update_seats()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->post('/aircraft/PK-TNP/update-seats', [
                'seat_ids' => ['1A'],
                'expiry_date' => '2030-01-01',
            ]);

        $this->assertEquals(403, $response->status());
    }

    public function test_regular_user_cannot_access_fleet_create()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('fleet.create'))
            ->assertStatus(403);
    }

    public function test_regular_user_cannot_access_bulk_import()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.bulk-import'))
            ->assertStatus(403);
    }

    public function test_admin_cannot_access_superadmin_only_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('superadmin.users'))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('fleet.create'))
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.bulk-import'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('admin.pdf-scan'))
            ->assertStatus(200);
    }

    public function test_superadmin_can_access_all_routes()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin)
            ->get(route('fleet.create'))
            ->assertStatus(200);

        $this->actingAs($superadmin)
            ->get(route('superadmin.users'))
            ->assertStatus(200);

        $this->actingAs($superadmin)
            ->get(route('admin.bulk-import'))
            ->assertStatus(200);

        $this->actingAs($superadmin)
            ->get(route('admin.pdf-scan'))
            ->assertStatus(200);
    }
}
