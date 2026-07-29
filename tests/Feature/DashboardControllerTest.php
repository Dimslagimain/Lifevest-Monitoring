<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private User $superadmin;

    private Airline $airline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->airline = Airline::create([
            'name' => 'Test Airline',
            'code' => 'TA',
        ]);
    }

    public function test_dashboard_loads_for_authenticated_user()
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_fleet_data()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSee('PK-ABC');
    }

    public function test_dashboard_shows_activity_log_for_admin()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard', ['view' => 'activity-log']));

        $response->assertStatus(200);
        $response->assertSee('Activity Log');
    }

    public function test_dashboard_shows_activity_log_for_superadmin()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->get(route('dashboard', ['view' => 'activity-log']));

        $response->assertStatus(200);
        $response->assertSee('Activity Log');
    }

    public function test_all_roles_can_access_dashboard()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        foreach ([$this->user, $this->admin, $this->superadmin] as $actor) {
            $this->actingAs($actor)
                ->get(route('dashboard'))
                ->assertStatus(200)
                ->assertSee('PK-ABC');
        }
    }
}
