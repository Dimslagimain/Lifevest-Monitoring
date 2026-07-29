<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelReportControllerTest extends TestCase
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

        Aircraft::create([
            'registration' => 'PK-ABC',
            'airline_id' => $this->airline->id,
            'type' => 'B737-800',
            'layout' => 'b737-e46',
            'status' => 'active',
            'pn_adult' => 'PN-ADULT-001',
            'pn_crew' => 'PN-CREW-001',
            'pn_infant' => 'PN-INF-001',
        ]);
    }

    public function test_export_replacement_plan_success()
    {
        Seat::create([
            'registration' => 'PK-ABC',
            'seat_id' => '1A',
            'class_type' => 'business',
            'expiry_date' => '2026-08-01',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.excel'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_replacement_plan_guest_redirect()
    {
        $this->get(route('reports.excel'))
            ->assertRedirect(route('login'));
    }

    public function test_export_summary_dashboard_success()
    {
        Seat::create([
            'registration' => 'PK-ABC',
            'seat_id' => '1A',
            'class_type' => 'economy',
            'expiry_date' => '2026-09-15',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.summary'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_summary_dashboard_guest_redirect()
    {
        $this->get(route('reports.summary'))
            ->assertRedirect(route('login'));
    }

    public function test_export_activity_log_success()
    {
        ActivityLog::create([
            'user_id' => $this->user->id,
            'registration' => 'PK-ABC',
            'action' => 'update',
            'details' => [
                'pns' => ['PN-ADULT-001'],
                'seat_count' => 2,
                'seats' => ['1A', '2B'],
                'expiry_date' => '2026-10-01',
            ],
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.activityLog'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_activity_log_guest_redirect()
    {
        $this->get(route('reports.activityLog'))
            ->assertRedirect(route('login'));
    }

    public function test_export_single_activity_success()
    {
        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'registration' => 'PK-ABC',
            'action' => 'batch',
            'details' => [
                'pns' => ['PN-ADULT-001'],
                'seat_count' => 1,
                'seats' => ['3C'],
                'expiry_date' => '2026-11-01',
            ],
        ]);

        $this->actingAs($this->user)
            ->get(route('reports.activityLog.single', $log->id))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_single_activity_404()
    {
        $this->actingAs($this->user)
            ->get(route('reports.activityLog.single', 999))
            ->assertStatus(404);
    }

    public function test_export_single_activity_guest_redirect()
    {
        $this->get(route('reports.activityLog.single', 1))
            ->assertRedirect(route('login'));
    }

    public function test_all_roles_can_export_excel_reports()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Seat::create([
            'registration' => 'PK-ABC',
            'seat_id' => '1A',
            'class_type' => 'economy',
            'expiry_date' => '2026-12-01',
            'status' => 'active',
        ]);

        foreach ([$this->user, $admin, $superadmin] as $actor) {
            $this->actingAs($actor)
                ->get(route('reports.excel'))
                ->assertStatus(200);
        }
    }
}
