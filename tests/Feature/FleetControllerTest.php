<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $admin;

    private User $regularUser;

    private Airline $airline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        $this->airline = Airline::create([
            'name' => 'Garuda Indonesia',
            'code' => 'GA',
        ]);
    }

    public function test_guest_cannot_access_fleet()
    {
        $this->get(route('fleet.index'))->assertRedirect('/login');
    }

    public function test_regular_user_can_view_fleet_but_cannot_modify()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('fleet.index'));
        $response->assertStatus(200);

        // Cannot create aircraft (POST store)
        $this->post(route('fleet.store'), [
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
        ])->assertStatus(403);
    }

    public function test_superadmin_can_view_fleet_edit_page()
    {
        $this->actingAs($this->superadmin);

        $aircraft = Aircraft::create([
            'registration' => 'PK-EDIT',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $this->get(route('fleet.edit', $aircraft->id))
            ->assertStatus(200)
            ->assertSee('PK-EDIT');
    }

    public function test_admin_cannot_view_fleet_edit_page()
    {
        $this->actingAs($this->admin);

        $aircraft = Aircraft::create([
            'registration' => 'PK-EDIT',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $this->get(route('fleet.edit', $aircraft->id))
            ->assertStatus(403);
    }

    public function test_superadmin_can_create_aircraft()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('fleet.store'), [
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777-300',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fleet.index'));
        $this->assertDatabaseHas('aircraft', [
            'registration' => 'PK-GIA',
            'type' => 'B777-300',
        ]);
    }

    public function test_superadmin_cannot_create_aircraft_with_duplicate_registration()
    {
        $this->actingAs($this->superadmin);

        Aircraft::create([
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        $response = $this->from(route('fleet.create'))->post(route('fleet.store'), [
            'registration' => 'PK-GIA', // Duplicate
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fleet.create'));
        $response->assertSessionHasErrors('registration');
    }

    public function test_superadmin_can_update_aircraft_including_pn_numbers()
    {
        $this->actingAs($this->superadmin);

        $aircraft = Aircraft::create([
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
            'pn_adult' => '111',
            'pn_crew' => '222',
            'pn_infant' => '333',
        ]);

        $response = $this->put(route('fleet.update', $aircraft->id), [
            'airline_id' => $this->airline->id,
            'type' => 'B777-300ER',
            'status' => 'prolong',
            'pn_adult' => '444',
            'pn_crew' => '222',
            'pn_infant' => '333',
        ]);

        $response->assertRedirect(route('fleet.index'));
        $this->assertDatabaseHas('aircraft', [
            'registration' => 'PK-GIA',
            'type' => 'B777-300ER',
            'status' => 'prolong',
            'pn_adult' => '444',
        ]);

        // Check that activity log was registered
        $this->assertDatabaseHas('activity_logs', [
            'registration' => 'PK-GIA',
            'action' => 'pn_update',
        ]);
    }

    public function test_superadmin_can_delete_aircraft_and_cascades_delete_seats()
    {
        $this->actingAs($this->superadmin);

        $aircraft = Aircraft::create([
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        Seat::create([
            'registration' => 'PK-GIA',
            'seat_id' => '1A',
            'expiry_date' => '2030-01-01',
            'status' => 'active',
        ]);

        $response = $this->delete(route('fleet.destroy', $aircraft->id));

        $response->assertRedirect(route('fleet.index'));
        $this->assertDatabaseMissing('aircraft', ['registration' => 'PK-GIA']);
        // Verify cascade delete
        $this->assertDatabaseMissing('seats', ['registration' => 'PK-GIA']);
    }

    public function test_superadmin_can_create_and_delete_airlines()
    {
        $this->actingAs($this->superadmin);

        // Store Airline
        $response = $this->post(route('airlines.store'), [
            'name' => 'Citilink',
            'code' => 'QG',
        ]);

        $response->assertRedirect(route('fleet.index', ['tab' => 'airlines']));
        $this->assertDatabaseHas('airlines', ['name' => 'Citilink', 'code' => 'QG']);

        $airline = Airline::where('name', 'Citilink')->first();

        // Delete Airline
        $response = $this->delete(route('airlines.destroy', $airline->id));
        $response->assertRedirect(route('fleet.index', ['tab' => 'airlines']));
        $this->assertDatabaseMissing('airlines', ['name' => 'Citilink']);
    }

    public function test_superadmin_cannot_delete_airline_with_assigned_aircraft()
    {
        $this->actingAs($this->superadmin);

        Aircraft::create([
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B777',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        $response = $this->delete(route('airlines.destroy', $this->airline->id));
        $response->assertRedirect(route('fleet.index', ['tab' => 'airlines']));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('airlines', ['id' => $this->airline->id]);
    }
}
