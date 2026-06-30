<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Airline $airline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
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
}
