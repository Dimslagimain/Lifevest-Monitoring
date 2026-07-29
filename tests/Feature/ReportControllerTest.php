<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
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

    public function test_export_pdf_success()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
            'pn_adult' => 'PN-001',
            'pn_crew' => 'PN-002',
            'pn_infant' => 'PN-003',
        ]);

        Seat::create([
            'registration' => 'PK-ABC',
            'seat_id' => '1A',
            'class_type' => 'business',
            'expiry_date' => '2027-01-01',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.pdf', 'PK-ABC'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_pdf_404()
    {
        $this->actingAs($this->user)
            ->get(route('reports.pdf', 'PK-UNKNOWN'))
            ->assertStatus(404);
    }

    public function test_export_pdf_guest_redirect()
    {
        $this->get(route('reports.pdf', 'PK-ABC'))
            ->assertRedirect(route('login'));
    }

    public function test_export_blank_form_success()
    {
        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.blank', 'PK-ABC'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_blank_form_404()
    {
        $this->actingAs($this->user)
            ->get(route('reports.blank', 'PK-UNKNOWN'))
            ->assertStatus(404);
    }

    public function test_export_blank_form_guest_redirect()
    {
        $this->get(route('reports.blank', 'PK-ABC'))
            ->assertRedirect(route('login'));
    }

    public function test_all_roles_can_export_pdf()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Aircraft::create([
            'registration' => 'PK-XYZ',
            'airline_id' => $this->airline->id,
            'type' => 'B777-300',
            'layout' => 'b777-e26',
            'status' => 'active',
        ]);

        foreach ([$this->user, $admin, $superadmin] as $actor) {
            $this->actingAs($actor)
                ->get(route('reports.pdf', 'PK-XYZ'))
                ->assertStatus(200);
        }
    }
}
