<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AircraftControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    private Airline $airline;

    private Aircraft $aircraft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);

        $this->airline = Airline::create([
            'name' => 'Garuda Indonesia',
            'code' => 'GA',
        ]);

        $this->aircraft = Aircraft::create([
            'registration' => 'PK-GIA',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
            'pn_adult' => '111-222',
            'pn_crew' => '333-444',
            'pn_infant' => '555-666',
        ]);
    }

    public function test_show_page_loads_for_authenticated_users()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('aircraft.show', $this->aircraft->registration));
        $response->assertStatus(200);
        $response->assertViewHas('aircraft');
    }

    public function test_show_page_aborts_on_non_existent_aircraft()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('aircraft.show', 'PK-UNKNOWN'));
        $response->assertStatus(404);
    }

    public function test_seat_status_returns_json_statistics()
    {
        $this->actingAs($this->regularUser);

        Seat::create([
            'registration' => 'PK-GIA',
            'seat_id' => '1A',
            'class_type' => 'business',
            'status' => 'active',
            'expiry_date' => '2030-01-01',
        ]);

        $response = $this->get(route('aircraft.seatStatus', [
            'registration' => $this->aircraft->registration,
            'status' => 'active',
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'registration' => 'PK-GIA',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_seats()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('aircraft.updateSeats', $this->aircraft->registration), [
            'seat_ids' => ['1A', '2B', 'inf-1'],
            'expiry_date' => '2030-05-15',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $seat1A = Seat::where('registration', 'PK-GIA')->where('seat_id', '1A')->first();
        $this->assertNotNull($seat1A);
        $this->assertEquals('2030-05-15', $seat1A->expiry_date->format('Y-m-d'));

        $seatInf = Seat::where('registration', 'PK-GIA')->where('seat_id', 'inf-1')->first();
        $this->assertNotNull($seatInf);
        $this->assertEquals('spare-inf', $seatInf->class_type);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'registration' => 'PK-GIA',
            'action' => 'update',
        ]);
    }

    public function test_admin_can_delete_spare_seat()
    {
        $this->actingAs($this->admin);

        Seat::create([
            'registration' => 'PK-GIA',
            'seat_id' => 'pax-1',
            'class_type' => 'spare-pax',
            'expiry_date' => '2030-01-01',
        ]);

        $response = $this->delete(route('aircraft.deleteSeat', $this->aircraft->registration), [
            'seat_id' => 'pax-1',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('seats', [
            'registration' => 'PK-GIA',
            'seat_id' => 'pax-1',
        ]);
    }

    public function test_admin_cannot_delete_regular_seat()
    {
        $this->actingAs($this->admin);

        Seat::create([
            'registration' => 'PK-GIA',
            'seat_id' => '21A',
            'class_type' => 'economy',
            'expiry_date' => '2030-01-01',
        ]);

        $response = $this->delete(route('aircraft.deleteSeat', $this->aircraft->registration), [
            'seat_id' => '21A',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('seats', [
            'registration' => 'PK-GIA',
            'seat_id' => '21A',
        ]);
    }

    public function test_batch_input_process_saves_multiple_seats()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('aircraft.storeBatchInput', $this->aircraft->registration), [
            'section_0_col_A' => "24-Jan-25\n25-Jan-25",
            'pax_dates' => 'Oct-25',
            'inf_dates' => 'Nov-25',
        ]);

        $response->assertRedirect(route('aircraft.show', $this->aircraft->registration));
        $response->assertSessionHas('success');

        // Check economy seats
        $seat21A = Seat::where('registration', 'PK-GIA')->where('seat_id', '21A')->first();
        $this->assertNotNull($seat21A);
        $this->assertEquals('2025-01-24', $seat21A->expiry_date->format('Y-m-d'));

        // Check spare seats
        $seatPax1 = Seat::where('registration', 'PK-GIA')->where('seat_id', 'pax-1')->first();
        $this->assertNotNull($seatPax1);
        $this->assertEquals('2025-10-01', $seatPax1->expiry_date->format('Y-m-d'));

        $seatInf1 = Seat::where('registration', 'PK-GIA')->where('seat_id', 'inf-1')->first();
        $this->assertNotNull($seatInf1);
        $this->assertEquals('2025-11-01', $seatInf1->expiry_date->format('Y-m-d'));
    }
}
