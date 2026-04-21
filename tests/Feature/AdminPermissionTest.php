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
        
        // Mock aircraft and seat if necessary, but at least check middleware
        $response = $this->actingAs($user)
            ->post("/aircraft/{$registration}/update-seats", [
                'seat_ids' => ['1A'],
                'expiry_date' => '2030-01-01'
            ]);
            
        // If it's 302/403, it means middleware blocked it. 
        // If it's something else (like 404 because PK-TNP doesn't exist), it means it passed middleware.
        $this->assertNotEquals(403, $response->status());
    }
}
